<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Software;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_type' => ['nullable', 'string'],
            'business_size' => ['nullable', 'string'],
            'industry' => ['nullable', 'string'],
            'primary_need' => ['nullable', 'string'],
            'must_have_features' => ['sometimes', 'array'],
            'must_have_features.*' => ['string'],
            'budget' => ['nullable', 'string'],
            'team_size' => ['nullable', 'string'],
            'integrations' => ['sometimes', 'array'],
            'integrations.*' => ['string'],
        ]);

        // Abaikan jawaban kosong untuk proses scoring.
        $answers = array_filter(
            $validated,
            static function ($value) {
                if (is_array($value)) {
                    return count($value) > 0;
                }

                return $value !== null
                    && trim((string) $value) !== '';
            }
        );

        $categoryMap = [
            'Sales & CRM' => 'CRM',
            'Finance & ERP' => 'ERP',
            'HR & People' => 'HR',
            'Operations' => 'Operations',
        ];

        $software = Software::query()
            ->where('is_active', true)
            ->with([
                'features' => fn ($query) => $query
                    ->where('software_features.is_active', true),
                'integrations' => fn ($query) => $query
                    ->where('integrations.is_active', true),
            ])
            ->get();

        $recommendations = $software->map(function ($item) use (
            $answers,
            $categoryMap
        ) {
            $earnedPoints = 0.0;
            $availablePoints = 0;

            /*
             * Primary need: 40 poin.
             */
            if (isset($answers['primary_need'])) {
                $expectedCategory =
                    $categoryMap[$answers['primary_need']] ?? null;

                if ($expectedCategory !== null) {
                    $availablePoints += 40;

                    if ($item->category === $expectedCategory) {
                        $earnedPoints += 40;
                    }
                }
            }

            /*
             * Business size: 30 poin.
             */
            if (isset($answers['business_size'])) {
                $businessSize = $answers['business_size'];

                $sizePoints = match ($businessSize) {
                    '500+' => $item->tag === 'Enterprise' ? 30 : 15,
                    '101–500' => $item->tag === 'Enterprise' ? 25 : 15,
                    '21–100' => in_array(
                        $item->tag,
                        ['SMB', 'Popular'],
                        true
                    ) ? 25 : 15,
                    default => in_array(
                        $item->tag,
                        ['SMB', 'Best Value'],
                        true
                    ) ? 30 : 15,
                };

                $availablePoints += 30;
                $earnedPoints += $sizePoints;
            }

            /*
             * Budget: 30 poin.
             */
            if (isset($answers['budget'])) {
                $budget = $answers['budget'];
                $price = strtolower(trim($item->price ?? ''));

                $budgetPoints = match ($budget) {
                    'Free / low cost' => str_contains($price, 'free')
                        ? 30
                        : ($this->priceFitsBudget($price, 0, 30) ? 20 : 5),

                    '$10–30 per user' => $this->priceFitsBudget(
                        $price, 10, 30
                    ) ? 30 : 10,

                    '$30–75 per user' => $this->priceFitsBudget(
                        $price, 30, 75
                    ) ? 30 : 15,

                    '$75+ per user' => $this->priceFitsBudget(
                        $price, 75, null
                    ) ? 30 : 10,

                    default => null,
                };

                if ($budgetPoints !== null) {
                    $availablePoints += 30;
                    $earnedPoints += $budgetPoints;
                }
            }

            /*
             * Must-have features: 20 poin.
             * Hanya dinilai jika data fitur software tersedia.
             */
            $selectedFeatures = $answers['must_have_features'] ?? [];

            if (
                count($selectedFeatures) > 0
                && $item->features->isNotEmpty()
            ) {
                $availablePoints += 20;

                $softwareFeatures = $item->features
                    ->flatMap(fn ($feature) => [
                        $this->normalizeValue($feature->name),
                        $this->normalizeValue($feature->slug),
                    ])
                    ->filter()
                    ->unique();

                $matchedFeatures = collect($selectedFeatures)
                    ->map(fn ($value) => $this->normalizeValue($value))
                    ->filter(fn ($value) => $value !== '')
                    ->intersect($softwareFeatures)
                    ->unique()
                    ->count();

                $totalSelected = collect($selectedFeatures)
                    ->map(fn ($value) => $this->normalizeValue($value))
                    ->filter(fn ($value) => $value !== '')
                    ->unique()
                    ->count();

                if ($totalSelected > 0) {
                    $earnedPoints += 20
                        * ($matchedFeatures / $totalSelected);
                }
            }

            /*
             * Integrations: 10 poin.
             * Hanya dinilai jika data integrasi software tersedia.
             */
            $selectedIntegrations = $answers['integrations'] ?? [];

            if (
                count($selectedIntegrations) > 0
                && $item->integrations->isNotEmpty()
            ) {
                $availablePoints += 10;

                $softwareIntegrations = $item->integrations
                    ->map(fn ($integration) =>
                        $this->normalizeValue($integration->name)
                    )
                    ->filter()
                    ->unique();

                $normalizedSelections = collect($selectedIntegrations)
                    ->map(fn ($value) => $this->normalizeValue($value))
                    ->filter()
                    ->unique();

                $matchedIntegrations = $normalizedSelections
                    ->intersect($softwareIntegrations)
                    ->count();

                if ($normalizedSelections->isNotEmpty()) {
                    $earnedPoints += 10 * (
                        $matchedIntegrations
                        / $normalizedSelections->count()
                    );
                }
            }

            /*
             * Jangan menghasilkan skor jika tidak ada
             * kriteria yang benar-benar bisa dinilai.
             */
            $matchScore = $availablePoints > 0
                ? (int) round(
                    ($earnedPoints / $availablePoints) * 100
                )
                : null;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'category' => $item->category,
                'rating' => $item->rating,
                'price' => $item->price,
                'fit' => $item->fit,
                'tag' => $item->tag,
                'description' => $item->description,
                'views' => $item->views,
                'match_score' => $matchScore,
                'features' => $item->features
                    ->map(fn ($feature) => [
                        'id' => $feature->id,
                        'name' => $feature->name,
                        'slug' => $feature->slug,
                    ])
                    ->values(),
                'integrations' => $item->integrations
                    ->map(fn ($integration) => [
                        'id' => $integration->id,
                        'name' => $integration->name,
                        'description' => $integration->description,
                        'url' => $integration->url,
                    ])
                    ->values(),
            ];
        });

        $recommendations = $recommendations
            ->sort(function ($a, $b) {
                if ($a['match_score'] === null) {
                    if ($b['match_score'] !== null) {
                        return 1;
                    }
                } elseif ($b['match_score'] === null) {
                    return -1;
                } else {
                    $comparison =
                        $b['match_score'] <=> $a['match_score'];

                    if ($comparison !== 0) {
                        return $comparison;
                    }
                }

                return (float) ($b['rating'] ?? 0)
                    <=> (float) ($a['rating'] ?? 0);
            })
            ->values()
            ->take(3);

        return response()->json([
            'success' => true,
            'message' => 'Software recommendations generated successfully.',
            'data' => [
                'answers' => $validated,
                'recommendations' => $recommendations,
            ],
        ]);
    }

    private function normalizeValue(?string $value): string
    {
        return strtolower(
            preg_replace('/[^a-z0-9]/i', '', trim($value ?? ''))
        );
    }

    private function priceFitsBudget(
        string $price,
        float $minimum,
        ?float $maximum
    ): bool {
        if (
            $price === ''
            || str_contains($price, 'free')
            || !preg_match('/\$\s*(\d+(?:\.\d+)?)/', $price, $matches)
        ) {
            return false;
        }

        $amount = (float) $matches[1];

        return $amount >= $minimum
            && ($maximum === null || $amount <= $maximum);
    }
}
