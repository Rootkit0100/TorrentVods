<?php

use TorrentVodsPlugin\Http\Controllers\MagnetLinkController;
use TorrentVodsPlugin\Http\Controllers\MagnetLinkFileController;
use TorrentVodsPlugin\Http\Controllers\SetupController;

Route::middleware('web')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::middleware('check.permission')->prefix('torrentvods')->group(function (){

            Route::resource('magnet_links', MagnetLinkController::class)
                    ->except(['show']);

            Route::get('/magnet_link_files', [MagnetLinkFileController::class, 'index'])->name('magnet_link_files.index');

            Route::post('/magnet_links/{magnet_link}/process', [MagnetLinkController::class, 'process'])->name('magnet_links.process');

            Route::prefix('api')->group(function () {
                Route::get('/magnet_links/index', [MagnetLinkController::class, 'data'])->name('magnet_links.data');
                Route::get('/magnet_link_files/index', [MagnetLinkFileController::class, 'data'])->name('magnet_link_files.data');
            });

            Route::get('/', [SetupController::class, 'index'])->name('torrentvods.setup.index');
            Route::post('/setup/create-vods', [SetupController::class, 'createVods'])->name('torrentvods.setup.create_vods');
            Route::post('/setup/link-vods', [SetupController::class, 'linkVods'])->name('torrentvods.setup.link_vods');
            Route::post('/setup/settings', [SetupController::class, 'storeSettings'])->name('torrentvods.setup.store_settings');
            Route::post('/setup/install-torrent-client', [SetupController::class, 'installTorrentClient'])->name('torrentvods.setup.install_torrent_client');
        });
    });
});