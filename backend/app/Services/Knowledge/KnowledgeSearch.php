<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeArticle;
use Illuminate\Support\Collection;

/**
 * Lightweight RAG foundation (Version 1)
 * MySQL-based search — no Vector DB required.
 * Interface ready for future vector search.
 */
class KnowledgeSearch
{
    /**
     * Search knowledge base for an organization.
     */
    public function search(string $query, ?int $organizationId = null, array $options = []): Collection
    {
        $limit = $options['limit'] ?? 10;
        $category = $options['category'] ?? null;
        $onlyPublished = $options['published'] ?? true;

        $builder = KnowledgeArticle::query()
            ->where(function ($q) use ($organizationId) {
                $q->where('organization_id', $organizationId)
                  ->orWhereNull('organization_id'); // system knowledge
            });

        if ($onlyPublished) {
            $builder->where('is_published', true);
        }

        if ($category) {
            $builder->where('category', $category);
        }

        // Simple relevance: title match > summary > content
        $terms = array_filter(explode(' ', mb_strtolower(trim($query))));

        if (empty($terms)) {
            return $builder->latest()->limit($limit)->get();
        }

        $builder->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $like = '%' . $term . '%';
                $q->orWhere('title', 'like', $like)
                  ->orWhere('summary', 'like', $like)
                  ->orWhere('content', 'like', $like);
            }
        });

        // Order by simple score (title hits first)
        $results = $builder->limit($limit * 3)->get();

        $scored = $results->map(function ($article) use ($terms) {
            $score = 0;
            $title = mb_strtolower($article->title ?? '');
            $summary = mb_strtolower($article->summary ?? '');
            $content = mb_strtolower($article->content ?? '');

            foreach ($terms as $term) {
                if (str_contains($title, $term)) $score += 10;
                if (str_contains($summary, $term)) $score += 5;
                if (str_contains($content, $term)) $score += 1;
            }

            $article->relevance_score = $score;
            return $article;
        })
        ->filter(fn ($a) => $a->relevance_score > 0)
        ->sortByDesc('relevance_score')
        ->take($limit)
        ->values();

        return $scored;
    }

    /**
     * Retrieve structured context for AI (RAG-style).
     */
    public function retrieveForAI(string $query, ?int $organizationId = null, int $limit = 5): array
    {
        $articles = $this->search($query, $organizationId, ['limit' => $limit]);

        return $articles->map(function ($a) {
            return [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'summary' => $a->summary,
                'excerpt' => mb_substr(strip_tags($a->content), 0, 500),
                'score' => $a->relevance_score ?? 0,
            ];
        })->toArray();
    }
}
