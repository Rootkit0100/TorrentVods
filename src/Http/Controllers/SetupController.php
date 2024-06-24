<?php

namespace TorrentVodsPlugin\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Repositories\SettingsRepository;
use App\Models\StreamingServer;
use TorrentVodsPlugin\Services\WatchFolderService;

class SetupController extends ApiController
{
    public function index(WatchFolderService $service, SettingsRepository $repository)
    {
        $currentServer = config('app.server_id');

        $service->createWatchFolder($currentServer, 'movies');
        $service->createWatchFolder($currentServer, 'series');

        $watchfolders = $service->getWatchFolders();

        $settings = $repository->find('torrentvods')->value ?? [];

        $servers = StreamingServer::all();
        
        return view('torrent_vods::pages.setup.index', ['watchfolders' => $watchfolders, 'settings' => $settings, 'servers' => $servers]);
    }
    
    public function storeSettings(SettingsRepository $repository)
    {
        $request = request();

        $values = [
            'download_limit' => $request->get('download_limit'),
            'upload_limit' => $request->get('upload_limit'),
            'servers' => $request->get('servers'),
        ];

        $repository->store('torrentvods', $values, true);

        return $this->respondSuccess();
    }

    public function createVods(WatchFolderService $service)
    {
        $jobs = [
            new \TorrentVodsPlugin\Jobs\CreateBlankFilesJob(),
        ];

        $watchfolders = $service->getWatchFolders();

        foreach ($watchfolders as $wf) {
            $jobs[] = new \App\Jobs\WatchFolderJob($wf);
        }

        $jobs[] = new \TorrentVodsPlugin\Jobs\LinkStreamsToMagnetFilesJob();

        \Illuminate\Support\Facades\Bus::chain($jobs)
                ->onConnection('redis')
                ->onQueue('plugin')
                ->dispatch();

        return $this->respondSuccess();
    }

    public function linkVods()
    {
        \TorrentVodsPlugin\Jobs\LinkStreamsToMagnetFilesJob::dispatch()
                ->onConnection('redis')
                ->onQueue('plugin')
                ->dispatch();

        return $this->respondSuccess();
    }

    public function installTorrentClient(SettingsRepository $repository)
    {
        $settings = $repository->find('torrentvods')->value ?? [];
        $servers = $settings['servers'] ?? [];

        $servers = array_unique(array_merge($servers, [config('app.server_id')]));

        $script = <<<'INSTALL'
apt-get install -y curl
curl -s https://deb.nodesource.com/setup_18.x | sudo bash
apt-get install -y nodejs
npm install webtorrent-cli@5.1.0 -g
ln -s /usr/lib/node_modules/webtorrent-cli/bin/cmd.js /usr/bin/webffmpeg
sed -i '/process.title/d' /usr/lib/node_modules/webtorrent-cli/bin/cmd.js
sed -i '/server.listen/,/server.once/d' /usr/lib/node_modules/webtorrent-cli/bin/cmd.js
INSTALL;

        $connectionFactory = app(\App\Http\Services\ServerConnectionFactory::class);

        foreach ($servers as $serverId) {
            $server = StreamingServer::find($serverId);

            if (!$server) {
                continue;
            }
            
            $host = $server->ip;
            $port = $server->ssh_port ?? 22;
            $password = $server->ssh_password;
            $user = 'root';

            $connection = $connectionFactory->getSshConnection($host, $port, $user, $password);

            if (!$connection) {
                continue;
            }

            $connection->exec('echo ' . escapeshellarg($script) . ' > /webffmpeg_install.sh' );

            $connection->exec('nohup bash /webffmpeg_install.sh > /webffmpeg_install.log 2>&1 &');
        }

        return $this->respondSuccess();
    }
}
