<?php

namespace TorrentVodsPlugin\Http\Serializers;

use App\Http\Serializers\Serializer;

class MagnetLinkSerializer extends Serializer
{
    public function transform($data)
    {
        $details = [
            'id' => $data->id,
            'link' => $data->link,
            'status' => $this->getStatusString($data->magnet_link_status_id),
            'time_to_keep_minutes' => $data->time_to_keep_minutes,
            'files' => $data->files_count,
            'vod_type' => $this->getVodTypeString($data->vod_type_id),
            'created_at' => $data->created_at,
            'updated_at' => $data->updated_at,
        ];

        return $details;
    }

    protected function getStatusString($status)
    {
        if (!$status) {
            return 'Not Processed';
        }

        $map = [
            1 => 'Processing',
            2 => 'Processed',
            3 => 'Failed',
        ];

        return $map[$status] ?? 'Error';
    }

    protected function getVodTypeString($vodTypeId)
    {
        if (!$vodTypeId) {
            return '-';
        }

        $map = [
            1 => 'Movies',
            2 => 'Series',
        ];

        return $map[$vodTypeId] ?? 'Error';
    }
}
