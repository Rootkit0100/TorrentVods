<?php

namespace TorrentVodsPlugin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use TorrentVodsPlugin\Models\MagnetLinkFile;
use TorrentVodsPlugin\Services\WatchFolderService;

class CreateBlankFilesJob implements ShouldQueue
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
        
        MagnetLinkFile::query()
            ->with('magnet')
            ->chunk(500, function ($files) use ($wfs) {
                Log::info(date('c') . ' ' . count($files) . ' files');
                foreach ($files as $file) {
                    if (empty($file->magnet->vod_type_id)) {
                        continue;
                    }

                    $path = $wfs[$file->magnet->vod_type_id]->path . '/' . $file->filename;

                    if (!file_exists($path)) {
                        Log::info(sprintf('Creating file %1$s', $path));

                        $dirname = dirname($path);

                        if (!file_exists($dirname)) {
                            mkdir($dirname, 0777, true);
                        }

                        file_put_contents($path, '');
                    } else {
                        Log::info(sprintf('File exists %1$s', $path));
                    }
                }
            });
    }
}
