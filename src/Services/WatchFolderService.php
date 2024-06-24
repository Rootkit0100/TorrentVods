<?php

namespace TorrentVodsPlugin\Services;

use App\Models\WatchFolder;

class WatchFolderService
{
    public function createWatchFolder($serverId, $type)
    {
        $settingsRepository = app(\App\Http\Repositories\SettingsRepository::class);
        $settings = $settingsRepository->find('torrentvods')->value ?? [];

        $folderType = $type == 'movies' ? 'Movie' : 'Series';

        $data = [
            'title' => 'TorrentVods Plugin Folder #' . $serverId . ' (' . $folderType . ')',
            'description' => 'Auto Created',
            'server_id' => $serverId,
            'path' => $this->getPath($type),
            'type' => $folderType,
            'is_enabled' => true,
            'status' => 'not processed',
        ];

        $wf = WatchFolder::where('title', '=', $data['title'])
                ->where('server_id', '=', $data['server_id'])
                ->where('type', '=', $data['type'])
                ->first();

        if (!$wf) {
            $wf = new WatchFolder();
            $wf->fill($data);
            $wf->save();
        }

        $categoriesService = app(CategoriesService::class);
        $byType = $categoriesService->createCategories();
        
        $settings = [
            'tmdb_search' => true,
            'no_match_search' => true,
            'create_symlink' => true,
            'title_format' => $data['type'] == 'Movies' ? 'title_with_year' : 'title_with_year/series_abbr_episode',
            'tmdb_language' => 'en-US',
            'tmdb_matching_percents' => '80',
            'categories' => $byType[$data['type']] ?? [],
            'bouquets' => [],
            'vod_location' => 0,
            'vod_location_servers' => ($settings['servers'] ?? []),
            'is_ondemand' => true,
            'status' => 'started',
        ];

        foreach ($settings as $key => $val) {
            $record = $wf->watch_folder_settings()->where('key', $key)->first();
            if (!$record) {
                $wf->watch_folder_settings()->create([
                    'key' => $key,
                    'value' => json_encode($val)
                ]);
            } else {
                $record->update([
                    'value' => json_encode($val)
                ]);
            }
        }

        return $wf;
    }

    public function getWatchFolders()
    {
        $wf = WatchFolder::where('title', 'like', 'TorrentVods Plugin Folder #%')
                ->get();

        return $wf;
    }

    public function getPath($type)
    {
        return base_path() . '/storage/app/torrent_downloads/' . $type;
    }
}
