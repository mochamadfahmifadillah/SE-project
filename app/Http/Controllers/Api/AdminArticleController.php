<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminArticleController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/admin/articles
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): JsonResponse
    {
        $query = Article::query()
            ->with('author')
            ->latest('created_at');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('excerpt', 'ilike', "%{$search}%")
                    ->orWhere('content', 'ilike', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            max((int) $request->input('per_page', 15), 1),
            100
        );

        $articles = $query->paginate($perPage);

        return response()->json([
            'data' => $articles->items(),

            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /api/v1/admin/articles
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:articles,slug',
            ],

            'excerpt' => [
                'nullable',
                'string',
            ],

            'content' => [
                'required',
                'string',
            ],

            'featured_image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'in:draft,published,archived',
            ],

            'published_at' => [
                'nullable',
                'date',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Slug
        |--------------------------------------------------------------------------
        */

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['title']
        );

        /*
        |--------------------------------------------------------------------------
        | Default Status
        |--------------------------------------------------------------------------
        */

        $validated['status'] = $validated['status'] ?? 'draft';

        /*
        |--------------------------------------------------------------------------
        | Published Date
        |--------------------------------------------------------------------------
        */

        if (
            $validated['status'] === 'published' &&
            empty($validated['published_at'])
        ) {
            $validated['published_at'] = now();
        }

        /*
        |--------------------------------------------------------------------------
        | Author
        |--------------------------------------------------------------------------
        */

        $validated['author_id'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $article = Article::create($validated);

        $article->load('author');

        return response()->json([
            'message' => 'Article created successfully.',
            'data' => $article,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/admin/articles/{article}
    |--------------------------------------------------------------------------
    */

    public function show(Article $article): JsonResponse
    {
        $article->load('author');

        return response()->json([
            'data' => $article,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PUT /api/v1/admin/articles/{article}
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Article $article
    ): JsonResponse {
        $validated = $request->validate([
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'unique:articles,slug,' . $article->id,
            ],

            'excerpt' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'content' => [
                'sometimes',
                'required',
                'string',
            ],

            'featured_image' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'sometimes',
                'nullable',
                'in:draft,published,archived',
            ],

            'published_at' => [
                'sometimes',
                'nullable',
                'date',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Slug
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists('slug', $validated) &&
            empty($validated['slug'])
        ) {
            $validated['slug'] = $validated['title']
                ?? $article->title;
        }

        if (
            array_key_exists('slug', $validated)
        ) {
            $validated['slug'] = $this->generateUniqueSlug(
                $validated['slug'],
                $article->id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Publishing Logic
        |--------------------------------------------------------------------------
        */

        if (
            ($validated['status'] ?? $article->status) === 'published' &&
            empty($validated['published_at'] ?? $article->published_at)
        ) {
            $validated['published_at'] = now();
        }

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $article->update($validated);

        $article->load('author');

        return response()->json([
            'message' => 'Article updated successfully.',
            'data' => $article,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE /api/v1/admin/articles/{article}
    |--------------------------------------------------------------------------
    */

    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Unique Slug
    |--------------------------------------------------------------------------
    */

    private function generateUniqueSlug(
        string $value,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'article';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Article::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId,
                    fn ($query) => $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $counter++;

            $slug = "{$baseSlug}-{$counter}";
        }

        return $slug;
    }
}