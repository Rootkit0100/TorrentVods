<?php

namespace TorrentVodsPlugin\Listeners;

use App\Events\StreamStatEvent;
use App\Models\StreamStatType;
use App\Models\Enum\StreamType;
use App\Http\Repositories\StreamRepository;
use TorrentVodsPlugin\Jobs\DownloadMagnetFileJob;
use TorrentVodsPlugin\Models\MagnetLinkFile;

class StreamStatsSubscriber
{
    public function subscribe($events)
    {
        if (!app()->runningInConsole()) {
            return;
        }
        
        $events->listen(StreamStatEvent::class, [$this, 'handleStreamStatEvent']);
    }

    public function handleStreamStatEvent($event)
    {
        if ($event->type != StreamStatType::TYPE_STARTING) {
            return;
        }

        $repository = app(StreamRepository::class);

        $stream = $repository->find($event->stat['stream_id']);

        if (!in_array($stream->type, [StreamType::MOVIE, StreamType::EPISODE])) {
            return;
        }

        $mf = MagnetLinkFile::query()
                ->where('stream_id', '=', $stream->id)
                ->first();

        if (!$mf) {
            return;
        }

        // check if need to start download job
        DownloadMagnetFileJob::dispatchSync($mf);
    }
}