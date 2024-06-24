<?php

$panelsPermissions = [
    'indexMagnetLinks',
    'createMagnetLinks',
    'editMagnetLinks',
];

return [
    [
        'entry' => [
            "Torrent Vods" => array(
                "can" => $panelsPermissions,
                "icon" => "fa fa-magnet",
                "subcategories" => array(
                    0 => array(
                        "title" => "Setup",
                        "can" => $panelsPermissions,
                        "items" => array(
                            0 => array(
                                "title" => "Setup",
                                "can" => "indexMagnetLinks",
                                "route" => "torrentvods.setup.index",
                            ),
                        ),
                    ),
                    1 => array(
                        "title" => "Magnet Links",
                        "can" => $panelsPermissions,
                        "items" => array(
                            0 => array(
                                "title" => "Add Magnet Link",
                                "can" => "createMagnetLinks",
                                "route" => "magnet_links.create",
                            ),
                            1 => array(
                                "title" => "Manage Magnet Link Files",
                                "can" => "indexMagnetLinks",
                                "route" => "magnet_link_files.index",
                            ),
                            2 => array(
                                "title" => "Manage Magnet Links",
                                "can" => "indexMagnetLinks",
                                "route" => "magnet_links.index",
                                "matching_routes" => ["magnet_links.edit"]
                            ),
                        ),
                    ),
                )
            ),
        ],
    ],
];