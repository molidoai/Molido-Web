<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Controllers\Controller;
use App\Models\KnowledgeArticle;
use App\Services\Knowledge\KnowledgeSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = KnowledgeArticle::where(function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id)
                  ->orWhereNull('organization_id');
            })
            ->latest();

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->boolean('published_only', true)) {
            $query->where('is_published', true);
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'type' => 'nullable|in:article,faq,document,instruction',
            'summary' => 'nullable|string|max:1000',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $article = KnowledgeArticle::create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']) . '-' . Str::random(4),
            'category' => $validated['category'] ?? 'general',
            'type' => $validated['type'] ?? 'article',
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'tags' => $validated['tags'] ?? null,
            'is_published' => $validated['is_published'] ?? true,
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return response()->json([
            'message' => 'مقاله دانش ایجاد شد',
            'article' => $article,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $article = KnowledgeArticle::where(function ($q) use ($user) {
                $q->where('organization_id', $user->organization_id)
                  ->orWhereNull('organization_id');
            })
            ->findOrFail($id);

        $article->increment('view_count');

        return response()->json($article);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $article = KnowledgeArticle::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:50',
            'type' => 'nullable|in:article,faq,document,instruction',
            'summary' => 'nullable|string|max:1000',
            'content' => 'sometimes|required|string',
            'tags' => 'nullable|array',
            'is_published' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $article->update($validated);

        return response()->json([
            'message' => 'مقاله به‌روزرسانی شد',
            'article' => $article->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $article = KnowledgeArticle::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $article->delete();

        return response()->json(['message' => 'مقاله حذف شد']);
    }

    /**
     * Search (Lightweight RAG entry point)
     */
    public function search(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'q' => 'required|string|min:2|max:500',
            'category' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $search = app(KnowledgeSearch::class);
        $results = $search->search(
            $validated['q'],
            $user->organization_id,
            [
                'category' => $validated['category'] ?? null,
                'limit' => $validated['limit'] ?? 10,
            ]
        );

        return response()->json([
            'query' => $validated['q'],
            'count' => $results->count(),
            'results' => $results,
        ]);
    }

    /**
     * Retrieve context for AI (RAG)
     */
    public function retrieve(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'q' => 'required|string|min:2|max:500',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $search = app(KnowledgeSearch::class);
        $context = $search->retrieveForAI(
            $validated['q'],
            $user->organization_id,
            $validated['limit'] ?? 5
        );

        return response()->json([
            'query' => $validated['q'],
            'context' => $context,
        ]);
    }
}
