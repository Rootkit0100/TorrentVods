<?php

namespace TorrentVodsPlugin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use TorrentVodsPlugin\Services\WatchFolderService;

class LinkStreamsToMagnetFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;

    public $tries = 1;

    public function handle(WatchFolderService $watchFolderService)
    {
        $wfs = [
            1 => $watchFolderService->createWatchFolder(config('app.server_id'), 'movies'),
            2 => $watchFolderService->createWatchFolder(config('app.server_id'), 'series'),
        ];

        // foreach watch folder stream, check for a magnet file
        $sql = 'SELECT mg.*, m.vod_type_id FROM magnet_link_files mg
            JOIN magnet_links m ON m.id = mg.magnet_link_id
            WHERE mg.stream_id IS NULL AND mg.id >= ?
            ORDER BY mg.id ASC
            LIMIT ?';

        $limit = 1000;

        $minId = '00000000-0000-0000-0000-000000000000';

        $bind = [$minId, $limit];

        $updateSql = 'UPDATE magnet_link_files SET stream_id=?, folder=? WHERE id=?';

        do {
            $results = DB::select($sql, $bind);

            Log::info(sprintf('Processing %1$s records', count($results)));

            foreach ($results as $r) {
                $wf = $wfs[$r->vod_type_id];

                $streamId = $this->getLinkByPath($r->filename, $wf);
                
                DB::update($updateSql, [$streamId, $streamId ? $wf->path : null, $r->id]);

                $minId = $r->id;
            }

            $bind[0] = $minId;

            Log::info(sprintf('Processed %1$s records', count($results)));
        } while (count($results) >= $limit);
    }

    protected function getLinkByPath($filename, $wf)
    {
        $path = rtrim($wf->path, '/') . '/' . ltrim($filename, '/');

        $sql = 'SELECT stream_id FROM watch_folders_streams wf WHERE wf.watch_folder_id IN (?) AND path IN (?) LIMIT 1';

        $results = DB::select($sql, [$wf->id, $path]);

        if (!empty($results[0])) {
            return $results[0]->stream_id;
        }

        return null;
    }
}
