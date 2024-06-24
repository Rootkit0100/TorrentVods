$(function () {
    initForm();

    $(document).on('click', '.installTorrentClient', function () {
        installTorrentClient();
    });

    $(document).on('click', '.createVods', function () {
        createVods();
    });

    $(document).on('click', '.linkVods', function () {
        linkVods();
    });
});

var initForm = function () {
    $('#submitBtn').on('click', function () {
        let settings = $('.settingsForm').find('input, select').serialize();
        
        saveSettings(settings);
        
        return false;
    })
}

var installTorrentClient = function () {
    $.ajax({
        url: Router.route('torrentvods.setup.install_torrent_client'),
        method: 'POST',
        success: function () {
            toastr['success']('Install started')
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Failed to start install')
        }
    });
}

var createVods = function () {
    $.ajax({
        url: Router.route('torrentvods.setup.create_vods'),
        method: 'POST',
        success: function () {
            toastr['success']('VODs are creating')
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Failed to start')
        }
    });
}

var linkVods = function () {
    $.ajax({
        url: Router.route('torrentvods.setup.link_vods'),
        method: 'POST',
        success: function () {
            toastr['success']('VODs are linking')
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Failed to start')
        }
    });
}

var saveSettings = function (settings) {
    $.ajax({
        url: Router.route('torrentvods.setup.store_settings'),
        method: 'POST',
        data: settings,
        success: function () {
            toastr['success']('Settings are saved')
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Failed to save')
        }
    });
}