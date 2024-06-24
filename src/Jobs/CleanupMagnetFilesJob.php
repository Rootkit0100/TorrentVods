<?php

namespace TorrentVodsPlugin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CleanupMagnetFilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;

    public $tries = 1;

    public function handle()
    {
        $sql = "
            SELECT mf.id, mf.filename, mf.folder
            FROM servers_streams s
            JOIN magnet_link_files mf ON mf.stream_id = s.stream_id
            WHERE status = 'ondemand_waiting'
                    AND s.updated_at < NOW() - CONCAT(mf.time_to_keep_minutes, ' minute')::interval
                    AND s.updated_at > NOW() - CONCAT(mf.time_to_keep_minutes * 60, ' minute')::interval
                    AND s.server_id = ?
                    AND mf.time_to_keep_minutes != 0
                    AND mf.id > ?
            ORDER BY mf.id ASC
            LIMIT ?
            ";

        $serverId = config('app.server_id');
        
        $limit = 1000;

        $minId = '00000000-0000-0000-0000-000000000000';

        $bind = [$serverId, $minId, $limit];

        do {
            $results = DB::select($sql, $bind);

            Log::info(sprintf('Processing %1$s records', count($results)));

            foreach ($results as $r) {

                $filePath = $r->folder . '/' . $r->filename;

                if (file_exists($filePath)) {
                    @unlink($filePath);
                    Log::notice('Deleted ' . $filePath);
                }

                $minId = $r->id;
            }

            $bind[1] = $minId;

            Log::info(sprintf('Processed %1$s records', count($results)));
        } while (count($results) >= $limit);
    }
}
