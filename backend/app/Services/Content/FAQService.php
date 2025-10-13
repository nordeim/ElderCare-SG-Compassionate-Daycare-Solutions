<?php

namespace App\Services\Content;

use App\Models\FAQ;
use Illuminate\Database\Eloquent\Collection;

class FAQService
{
    public function getPublishedByCategory(?string $category = null): Collection
    {
        $query = FAQ::where('status', 'published')
            ->orderBy('display_order')
            ->orderBy('created_at');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->get();
    }

    public function getAllGroupedByCategory(bool $publishedOnly = true): array
    {
        $query = FAQ::query()->orderBy('display_order');

        if ($publishedOnly) {
            $query->where('status', 'published');
        }

        return $query->get()->groupBy('category')->toArray();
    }

    public function search(string $searchTerm): Collection
    {
        return FAQ::where('status', 'published')
            ->where(function ($query) use ($searchTerm) {
                $query->where('question', 'like', "%{$searchTerm}%")
                      ->orWhere('answer', 'like', "%{$searchTerm}%");
            })
            ->orderBy('display_order')
            ->get();
    }
}
