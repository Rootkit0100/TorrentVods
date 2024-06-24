<?php

global $loader;
global $plugins;

$loader->addPsr4('TorrentVodsPlugin\\', __DIR__ . '/src/');

$plugins[] = [
    TorrentVodsPlugin\Plugin::NAME,
    '\TorrentVodsPlugin\TorrentVodsPluginServiceProvider',
];