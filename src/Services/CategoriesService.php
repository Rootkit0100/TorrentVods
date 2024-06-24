<?php

namespace TorrentVodsPlugin\Services;

use App\Models\Category;

class CategoriesService
{
    public function createCategories()
    {
        $categories = [
            [
                'name' => '[TORRENT] All',
                'type' => 'Movie',
            ],
            [
                'name' => '[TORRENT] All',
                'type' => 'Series',
            ],
        ];

        $byType = [];

        foreach ($categories as $c) {
            $cc = Category::where('name', '=', $c['name'])
                    ->where('type', '=', $c['type'])
                    ->first();

            if (!$cc) {
                $cc = new Category();
                $cc->fill($c);
                $cc->save();
            }

            $byType[$c['type']][] = $cc->id;
        }

        return $byType;
    }
}
