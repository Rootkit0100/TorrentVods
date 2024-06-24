<?php

namespace TorrentVodsPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Uuid;

class MagnetLink extends Model
{
    use Uuid;

    protected $table = 'magnet_links';

    protected $keyType = 'string';

    protected $guarded = ['id'];

    public function files()
    {
        return $this->hasMany(MagnetLinkFile::class)
                ->orderByRaw('fileindex ASC');
    }
}
