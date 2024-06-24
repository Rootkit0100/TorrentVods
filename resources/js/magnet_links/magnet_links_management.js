$(function () {
    initListing();
    initFileListing();
});


var deleteWithConfirmation = function (id, callback) {
    customConfirm({
        title: '<i class="fa fa-trash text-danger"></i>' + ' Magnet Link?',
        message: 'Are you sure you want to delete?',
        callback: function (result) {
            if (!result) {
                return;
            }

            $.ajax({
                url: Router.route('magnet_links.destroy', {'magnet_link': id}),
                method: 'DELETE',
                success: function () {
                    toastr['success']('Magnet Link has been deleted.')
                    callback();
                },
                error: function (response) {
                    var r = response.responseJSON;
                    toastr['error'](r.message ? r.message : 'Magnet Link failed to delete')
                    callback();
                }
            });
        }
    });
}

var processMagnetLink = function (id) {
    $.ajax({
        url: Router.route('magnet_links.process', {'magnet_link': id}),
        method: 'POST',
        success: function () {
            toastr['success']('Magnet Link is Processing')
        },
        error: function (response) {
            var r = response.responseJSON;
            toastr['error'](r.message ? r.message : 'Magnet Link failed to start processing')
        }
    });
}

var initListing = function () {
    var table = $('#magnet_links_table');
    if (!table.length) {
        return;
    }

   table.DataTable({
        ajax: {
            url: Router.route('magnet_links.data'),
            method: 'GET'
        },
        serverSide: true,
        lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
        iDisplayLength: 100,
        order: [[1, 'desc']],
        columns: [
            {
                data: 'id',
                width: "22%",
                render:function (data, type, row){
                    return view.renderString('<span>{{ id }}</span>', row);
                }
            },
            {
                data: 'created_at',
                visible: false,
            },
            {
                data: 'link',
                render:function (data, type, row) {
                    return view.renderString('<span style="word-wrap:anywhere;">{{ link }}</span>', row);
                }
            },
            {
                data: 'status',
                render:function (data, type, row) {
                    let colors = {
                        'Not Processed': 'text-secondary',
                        'Processed': 'text-success',
                        'Processing': 'text-primary',
                        'Failed': 'text-danger',
                    }

                    row.status_color = colors[data] ?? '';

                    return view.renderString('<span class="{{ status_color }}">{{ status }}</span>', row);
                }
            },
            {
                data: 'vod_type',
                render:function (data, type, row) {
                    return view.renderString('<span>{{ vod_type }}</span>', row);
                }
            },
            {
                data: 'time_to_keep_minutes',
                render:function (data, type, row) {
                    return view.renderString('<span>{{ time_to_keep_minutes }}</span>', row);
                }
            },
            {
                data: 'files',
                render:function (data, type, row) {
                    row.files_index_link = Router.route('magnet_link_files.index', {'magnet_link_id': row.id});
                    
                    return view.renderString('<a href="{{files_index_link}}">{{ files }}</a>', row);
                }
            },
            {
                width: '20%',
                className: 'actions',
                orderable: false,
                render: function (data, type, row) {
                    var processButton = view.renderString(
                        '<button type="button" class="btn btn-sm btn-outline-warning process" data-id="{{ id }}" title="Process"><i class="fa fa-magnet"></i></button>',
                        row
                    );

                    var editButton = view.renderString(
                        '<a href="{{ url }}" class="btn btn-sm btn-outline-primary mr-1" title="Edit"><i class="fa fa-pencil"></i></a>',
                        {'url': Router.route('magnet_links.edit', {'magnet_link': row.id})}
                    );

                    var deleteButton = view.renderString(
                        '<button type="button" class="btn btn-sm btn-outline-danger delete" data-id="{{ id }}" title="Delete"><i class="fas fa-trash-alt"></i></button>',
                        row
                    );

                    return action_buttons.render(row, [processButton, editButton, deleteButton]);
                }
            }
        ]
    });

    $(document).on('click', '.process', function () {
        processMagnetLink($(this).data('id'));
    });

    $(document).on('click', '.delete', function () {
        deleteWithConfirmation($(this).data('id'), function () {
            table.DataTable().ajax.reload(null, false);
        });
    });
};

var initFileListing = function () {
    var table = $('#magnet_link_files_table');
    if (!table.length) {
        return;
    }

   table.DataTable({
        ajax: {
            url: Router.route('magnet_link_files.data'),
            method: 'GET',
            data: function (d) {
                return $.extend({}, d, {
                    'magnet_link_id': $('.tableFilter [name="magnet_link_id"]').val()
                });
            }
        },
        serverSide: true,
        lengthMenu: [[25, 50, 100, 200, -1], [25, 50, 100, 200, "All"]],
        iDisplayLength: 100,
        order: [[1, 'desc']],
        columns: [
            {
                data: 'id',
                width: "22%",
                render:function (data, type, row){
                    return view.renderString('<span>{{ id }}</span>', row);
                }
            },
            {
                data: 'created_at',
                visible: false,
            },
            {
                data: 'magnet',
                render:function (data, type, row) {
                    row.edit_url = Router.route('magnet_links.edit', {'magnet_link': row.magnet_link_id});
                    
                    return view.renderString('<a href="{{ edit_url }}">Magnet</a>', row);
                }
            },
            {
                data: 'filename',
                render:function (data, type, row) {
                    return view.renderString('<span>{{ filename }}</span>', row);
                }
            },
            {
                data: 'fileindex',
                render:function (data, type, row) {
                    return view.renderString('<span>{{ fileindex }}</span>', row);
                }
            },
            {
                data: 'time_to_keep_minutes',
                render:function (data, type, row) {
                    return view.renderString('<span>{{ time_to_keep_minutes }}</span>', row);
                }
            },
            {
                data: 'stream_id',
                render:function (data, type, row) {
                    if (!data) {
                        return '-';
                    }
                    
                    row.edit_url = Router.route('movies.edit', {'movie': data});
                    
                    return view.renderString('<a href="{{ edit_url }}">VOD</a>', row);
                }
            },
            {
                data: 'full_path',
                render:function (data, type, row) {
                    if (!row.folder) {
                        return '-';
                    }
                    
                    return view.renderString('<span>{{ folder }}/{{ filename }}</span>', row);
                }
            },
            {
                width: '5%',
                className: 'actions',
                orderable: false,
                render: function (data, type, row) {
                    return '';
//                    return action_buttons.render(row, []);
                }
            }
        ]
    });
};