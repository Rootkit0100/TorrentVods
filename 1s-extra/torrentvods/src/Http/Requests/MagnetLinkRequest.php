<?php

namespace TorrentVodsPlugin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagnetLinkRequest extends FormRequest
{
    public function rules()
    {
        return [
            'link' => 'required|string|max:655',
            'time_to_keep_minutes' => 'required|integer',
            'magnet_link_status_id' => 'sometimes|integer',
            'vod_type_id' => 'sometimes|integer',
        ];
    }
}
