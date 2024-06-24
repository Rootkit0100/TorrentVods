<?php

namespace TorrentVodsPlugin\Http\Controllers;

use TorrentVodsPlugin\Models\MagnetLinkFile;
use Illuminate\Http\Request;
use TorrentVodsPlugin\Http\Serializers\MagnetLinkFileSerializer;
use App\Http\Controllers\Api\ApiController;

class MagnetLinkFileController extends ApiController
{
    public function __construct(MagnetLinkFileSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function index()
    {
        return view('torrent_vods::pages.magnet_link_files.index');
    }

    public function data(Request $request)
    {
        $files = MagnetLinkFile::query()
            ->orderBy('created_at', 'desc');

        $allCount = $files->count();

        if ($search = $request->get('search')) {
            $title = $search['value'] ?? '';
            $files->where(function ($q) use ($title) {
                $q->where('filename', 'ilike', '%' . $title . '%');
            });
        }

        $magnetLinkId = $request->get('magnet_link_id');

        if ($magnetLinkId) {
            $files->where('magnet_link_id', '=', $magnetLinkId);
        }
        
        $filteredCount = $files->count();

        if ($request->has(['start', 'length'])) {
            $start = intval($request->get('start'));
            $length = intval($request->get('length'));

            $files->skip($start)->take($length);
        }

        $files = $files->get();

        $serialized = $this->serializer->collection($files);

        $response = [
            'draw' => intval($request->get('draw', 0)),
            'recordsTotal' => $allCount,
            'recordsFiltered' => $filteredCount,
            'data' => $serialized,
        ];

        return $this->respond($response);
    }
}
