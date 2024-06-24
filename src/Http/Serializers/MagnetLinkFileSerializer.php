<?php

namespace TorrentVodsPlugin\Http\Serializers;

use App\Http\Serializers\Serializer;

class MagnetLinkFileSerializer extends Serializer
{
    public function transform($data)
    {
        $details = [
            'id' => $data->id,
            'magnet_link_id' => $data->magnet_link_id,
            'filename' => $data->filename,
            'fileindex' => $data->fileindex,
            'time_to_keep_minutes' => $data->time_to_keep_minutes,
            'stream_id' => $data->stream_id,
            'folder' => $data->folder,
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
        ];

        return $details;
    }
}
