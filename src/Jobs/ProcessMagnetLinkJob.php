<?php

namespace TorrentVodsPlugin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use TorrentVodsPlugin\Models\MagnetLink;
use TorrentVodsPlugin\Models\MagnetLinkFile;

class ProcessMagnetLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;

    public $tries = 1;

    public $magnetLinkId;

    const ALLOWED_EXTENSIONS = ['mkv', 'avi', 'flv', 'mov', 'mp4'];

    public function __construct($magnetLinkId)
    {
        $this->magnetLinkId = $magnetLinkId;
    }

    public function handle()
    {
        // command to show list of files is torrent
        // webtorrent downloadmeta https://webtorrent.io/torrents/sintel.torrent -o pathtodir
        // webtorrent info pathtodir/hash.torrent
        $ml = MagnetLink::find($this->magnetLinkId);

        // processing
        $ml->magnet_link_status_id = 1;
        $ml->save();

        $dir = Storage::path('/torrent_meta');

        $webtorrentBin = 'timeout 30s webffmpeg';

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $commandFetchMeta = sprintf($webtorrentBin . ' downloadmeta %1$s -o %2$s', escapeshellarg($ml->link), $dir);
        
        Log::info($dir);

        $outputFetchMeta = shell_exec($commandFetchMeta);
        // " fetching torrent metadata from 0 peers saving the .torrent file data to /var/www/storage/app//torrent_meta/08ada5a7a6183aae1e09d831df6748d566095a10.torrent ... webtorrent is exiting... " 

        Log::info($outputFetchMeta);

        $matches = [];

        if (!preg_match('/file data to\s+(.*)\s+\.\.\./', $outputFetchMeta, $matches)) {
            // failed
            $ml->magnet_link_status_id = 3;
            $ml->save();
            return;
        }
        
        $file = $matches[1];

        $commandInfo = sprintf($webtorrentBin . ' info %1$s', $file);

        $outputInfo = shell_exec($commandInfo);

        $outputJson = @json_decode($outputInfo, true);

        $files = $outputJson['files'] ?? [];

        Log::info(sprintf('Magnet Link %1$s fetched %2$s files', $ml->id, count($files)));
        
        $allowedExt = ['mkv', 'avi', 'flv', 'mov', 'mp4'];

        $currentIds = [];

        foreach ($files as $index => $f) {
            $ext = mb_strtolower(pathinfo($f['path'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
                Log::info(sprintf('Magnet Link %1$s skipping file#%2$s - %3$s, extension not allowed', $ml->id, $index, $f['path']));
                continue;
            }
            
            $magnetFile = MagnetLinkFile::where('magnet_link_id', '=', $ml->id)
                    ->where('fileindex', '=', $index)
                    ->where('filename', '=', $f['path'])
                    ->first();

            if ($magnetFile) {
                $currentIds[] = $magnetFile->id;
                $magnetFile->time_to_keep_minutes = $ml->time_to_keep_minutes;
                $magnetFile->save();
                continue;
            }
            
            $magnetFile = MagnetLinkFile::create([
                'magnet_link_id' => $ml->id,
                'filename' => $f['path'],
                'fileindex' => $index,
                'time_to_keep_minutes' => $ml->time_to_keep_minutes,
            ]);

            $currentIds[] = $magnetFile->id;
        }

        // delete old records
        MagnetLinkFile::where('magnet_link_id', '=', $ml->id)
                ->whereNotIn('id', $currentIds)
                ->delete();

        // processed
        $ml->magnet_link_status_id = 2;
        $ml->save();
    }
}
