<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FrontendFaqService
{
    public function forPage(?Page $page, array $fallback): array
    {
        $faqs = $this->assignedFaqs($page);
        $source = 'assigned';

        if ($faqs->isEmpty()) {
            $faqs = $this->globalFaqs();
            $source = $faqs->isNotEmpty() ? 'global' : 'fallback';
        }

        return [
            'source' => $source,
            'groups' => $faqs->isNotEmpty()
                ? $this->groups($faqs)
                : $this->fallbackGroups($fallback),
        ];
    }

    private function assignedFaqs(?Page $page): Collection
    {
        if (! $page) {
            return collect();
        }

        return $page->faqs()
            ->active()
            ->where(fn (Builder $query) => $this->activeCategoryFilter($query))
            ->with('category')
            ->orderBy('faq_page.sort_order')
            ->orderBy('faqs.sort_order')
            ->orderBy('faqs.id')
            ->get();
    }

    private function globalFaqs(): Collection
    {
        return Faq::active()
            ->where(fn (Builder $query) => $this->activeCategoryFilter($query))
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(50)
            ->get();
    }

    private function activeCategoryFilter(Builder $query): Builder
    {
        return $query
            ->whereNull('faq_category_id')
            ->orWhereHas('category', fn (Builder $category) => $category->active());
    }

    private function groups(Collection $faqs): Collection
    {
        return $faqs
            ->groupBy(fn (Faq $faq) => $faq->category?->id ?: 'general')
            ->map(function (Collection $items, string|int $key) {
                $category = $items->first()->category;

                return [
                    'id' => $category ? 'faq-category-'.$category->id : 'faq-category-general',
                    'name' => $category?->name ?: 'General',
                    'sort_order' => $category?->sort_order ?? $items->min(fn (Faq $faq) => $faq->pivot?->sort_order ?? $faq->sort_order),
                    'items' => $items->map(fn (Faq $faq) => [
                        'id' => $faq->id,
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                        'sort_order' => $faq->pivot?->sort_order ?? $faq->sort_order,
                    ])->values(),
                ];
            })
            ->sortBy([
                ['sort_order', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function fallbackGroups(array $fallback): Collection
    {
        return collect([[
            'id' => 'faq-category-general',
            'name' => 'General',
            'sort_order' => 9999,
            'items' => collect($fallback['questions'] ?? [])->map(fn (array $item, int $index) => [
                'id' => 'fallback-'.$index,
                'question' => $item['question'] ?? '',
                'answer' => $item['answer'] ?? '',
                'sort_order' => $index,
            ])->filter(fn (array $item) => $item['question'] !== '')->values(),
        ]]);
    }
}
