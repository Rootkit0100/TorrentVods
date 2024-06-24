<?php

namespace TorrentVodsPlugin\Http\Controllers;

use TorrentVodsPlugin\Http\Requests\MagnetLinkRequest;
use TorrentVodsPlugin\Models\MagnetLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use TorrentVodsPlugin\Http\Serializers\MagnetLinkSerializer;
use App\Http\Controllers\Api\ApiController;
use TorrentVodsPlugin\Jobs\ProcessMagnetLinkJob;

class MagnetLinkController extends ApiController
{
    public function __construct(MagnetLinkSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function index()
    {
        return view('torrent_vods::pages.magnet_links.index');
    }

    public function data(Request $request)
    {
        $links = MagnetLink::query()
            ->orderBy('created_at', 'desc');

        $allCount = $links->count();

        if ($search = $request->get('search')) {
            $title = $search['value'] ?? '';
            $links->where(function ($q) use ($title) {
                $q->where('link', 'ilike', '%' . $title . '%');
            });
        }
        
        $filteredCount = $links->count();

        $links->withCount('files');

        if ($request->has(['start', 'length'])) {
            $start = intval($request->get('start'));
            $length = intval($request->get('length'));

            $links->skip($start)->take($length);
        }

        $links = $links->get();

        $serialized = $this->serializer->collection($links);

        $response = [
            'draw' => intval($request->get('draw', 0)),
            'recordsTotal' => $allCount,
            'recordsFiltered' => $filteredCount,
            'data' => $serialized,
        ];

        return $this->respond($response);
    }

    public function create()
    {
        return view('torrent_vods::pages.magnet_links.create');
    }

    public function store(MagnetLinkRequest $request)
    {
        $validated = $request->validated();

        $magnet_link = MagnetLink::create($validated);

        return Redirect::route('magnet_links.edit', ['magnet_link' => $magnet_link])
            ->with(['message' => __('message.create')]);
    }

    public function edit(MagnetLink $magnet_link)
    {
        $this->authorize('update', $magnet_link);

        return view('torrent_vods::pages.magnet_links.edit', ['magnet_link' => $magnet_link]);
    }

    public function update(MagnetLinkRequest $request, MagnetLink $magnet_link)
    {
        $this->authorize('update', $magnet_link);

        $validated = $request->validated();

        $magnet_link->update($validated);

        return Redirect::route('magnet_links.edit', ['magnet_link' => $magnet_link])
            ->with(['message' => __('message.update')]);
    }

    public function destroy(MagnetLink $magnet_link)
    {
        $this->authorize('update', $magnet_link);

        $magnet_link->delete();

        return response()->json(['status' => 'success', 'message' => __('message.delete')]);
    }

    public function process(MagnetLink $magnet_link)
    {
        $this->authorize('update', $magnet_link);

        \TorrentVodsPlugin\Jobs\ProcessMagnetLinkJob::dispatch($magnet_link->id)
                ->onConnection('redis')
                ->onQueue('plugin');

        return response()->json(['status' => 'success', 'message' => __('message.success')]);
    }
}
