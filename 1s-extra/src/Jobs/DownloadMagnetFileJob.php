<?php

namespace TorrentVodsPlugin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Utils\System;
use App\Utils\Path;
use App\Http\Services\Streams\StreamLifecycleService;

class DownloadMagnetFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;

    public $tries = 1;

    public $magnetLinkFile;

    public function __construct($magnetLinkFile)
    {
        $this->magnetLinkFile = $magnetLinkFile;
    }

    public function handle(StreamLifecycleService $lifecycleService)
    {
        $serverId = config('app.server_id');
        $mf = $this->magnetLinkFile;
        $ml = $mf->magnet;
        $streamId = $mf->stream_id;
        
        $webtorrentBin = 'webffmpeg';

        $settingsRepository = app(\App\Http\Repositories\SettingsRepository::class);
        $settings = $settingsRepository->find('torrentvods')->value ?? [];

        // kB/s
        $downloadLimit = $settings['download_limit'] ?? '20000';
        $uploadLimit = $settings['upload_limit'] ?? '3000';
        if (!file_exists($mf->folder)) {
            mkdir($mf->folder, 0777, true);
        }

        $commandDownload = sprintf('nohup ' . $webtorrentBin . ' download %1$s -o %2$s -d %3$s -u %4$s --select %5$s 2>&1 &', escapeshellarg($ml->link), escapeshellarg($mf->folder),
                $downloadLimit, $uploadLimit, $mf->fileindex);

        $pidfile = Path::pidfile($mf->id . '_download');

        $logger = \Illuminate\Support\Facades\Log::channel('cli');

        $logger->info(sprintf('Starting download stream#%1$s, magnetfile#%2$s', $streamId, $mf->id));

        $logger->debug($commandDownload);

        System::startWithPidFile($commandDownload, $pidfile);

        $pid = System::readPidFile($pidfile);

        $sys = $lifecycleService->getStreamSys($streamId, $serverId);
        
        $sys->pid = $pid;

        $logger->info(sprintf('Started download stream#%1$s, magnetfile#%2$s with pid %3$s', $streamId, $mf->id, $pid));

        $lifecycleService->updateSys($sys);

        $filePath = $mf->folder . '/' . $mf->filename;
        
        $i = 0;
        while((!file_exists($filePath) || filesize($filePath) < 5 * 1024 * 1024) && $i < 10) {
            clearstatcache($filePath);
            $logger->notice('Sleep 1s');
            sleep(1);
            $i++;
        }
    }
}
