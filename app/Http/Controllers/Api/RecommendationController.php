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
            'business_size' => ['required', 'string'],
            'primary_need' => ['required', 'string'],
            'budget' => ['required', 'string'],
        ]);

        $software = Software::query()
            ->where('is_active', true)
            ->get();

        $recommendations = $software->map(function ($item) use ($validated) {
            $score = 0;

            /*
             * PRIMARY NEED
             */
            $categoryMap = [
                'Sales & CRM' => 'CRM',
                'Finance & ERP' => 'ERP',
                'HR & People' => 'HR',
                'Operations' => 'Operations',
            ];

            if (
                isset($categoryMap[$validated['primary_need']]) &&
                $item->category === $categoryMap[$validated['primary_need']]
            ) {
                $score += 40;
            }

            /*
             * BUSINESS SIZE
             *
             * Enterprise-oriented software mendapat score
             * lebih tinggi untuk perusahaan besar.
             */
            $businessSize = $validated['business_size'];

            if ($businessSize === '500+') {
                if ($item->tag === 'Enterprise') {
                    $score += 30;
                } else {
                    $score += 15;
                }
            } elseif ($businessSize === '101–500') {
                if ($item->tag === 'Enterprise') {
                    $score += 25;
                } else {
                    $score += 15;
                }
            } elseif ($businessSize === '21–100') {
                if ($item->tag === 'SMB' || $item->tag === 'Popular') {
                    $score += 25;
                } else {
                    $score += 15;
                }
            } else {
                if ($item->tag === 'SMB' || $item->tag === 'Best Value') {
                    $score += 30;
                } else {
                    $score += 15;
                }
            }

            /*
             * BUDGET
             */
            $budget = $validated['budget'];

            $price = strtolower($item->price ?? '');

            if ($budget === 'Free / low cost') {
                if (str_contains($price, 'free')) {
                    $score += 30;
                } elseif (
                    str_contains($price, '$8') ||
                    str_contains($price, '$10') ||
                    str_contains($price, '$20')
                ) {
                    $score += 20;
                } else {
                    $score += 5;
                }
            } elseif ($budget === '$10–30 per user') {
                if (
                    str_contains($price, '$10') ||
                    str_contains($price, '$20') ||
                    str_contains($price, '$24') ||
                    str_contains($price, '$25')
                ) {
                    $score += 30;
                } else {
                    $score += 10;
                }
            } elseif ($budget === '$30–75 per user') {
                if (
                    str_contains($price, '$65') ||
                    str_contains($price, '$75')
                ) {
                    $score += 30;
                } else {
                    $score += 15;
                }
            } elseif ($budget === '$75+ per user') {
                if (
                    str_contains($price, '$75') ||
                    str_contains($price, '$100') ||
                    str_contains($price, '$150')
                ) {
                    $score += 30;
                } else {
                    $score += 10;
                }
            }

            /*
             * BASE RATING
             *
             * Rating memberikan sedikit tambahan score
             * supaya software dengan rating tinggi
             * punya posisi lebih baik ketika score utama sama.
             */
            $score += ((float) $item->rating) * 2;

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
                'match_score' => min(100, $score),
            ];
        });

        $recommendations = $recommendations
            ->sortByDesc('match_score')
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
}