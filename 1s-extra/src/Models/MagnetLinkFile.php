<?php

namespace TorrentVodsPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class MagnetLinkFile extends Model
{
    use Uuid;

    protected $table = 'magnet_link_files';

    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function magnet()
    {
        return $this->belongsTo(MagnetLink::class, 'magnet_link_id', 'id');
    }
}
