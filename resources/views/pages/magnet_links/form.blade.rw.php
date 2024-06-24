<form action="{{ $formUrl }}" method="POST" id="reseller_panel_form" autocomplete="off" enctype="multipart/form-data">
    @method($formMethod)
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="card card-default shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Details</h3>
                </div>
                <div class="card-body">
                    @include('components.page.form-errors-box')

                    <div class="row form-group">
                        <div class="col-md-12">
                            <label for="link">Link</label>
                            <textarea class="form-control" id="link" name="link" required >{{ !empty($magnet_link->link) ? $magnet_link->link : old('link', '') }}</textarea>
                        </div>
                    </div>

                    <div class="row form-group">
                        <div class="col-md-4">
                            <label for="filename">Status</label>
                            @php($status=$magnet_link->magnet_link_status_id ?? 0)
                            <select class="select2" name="magnet_link_status_id">
                                <option value="0" {{ $status == 0 ? 'selected' : '' }}>Not Processed</option>
                                <option value="1" {{ $status == 1 ? 'selected' : '' }}>Processing</option>
                                <option value="2" {{ $status == 2 ? 'selected' : '' }}>Processed</option>
                                <option value="3" {{ $status == 3 ? 'selected' : '' }}>Failed</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="vod_type_id">Vod Type</label>
                            @php($vodType=$magnet_link->vod_type_id ?? 0)
                            <select class="select2" name="vod_type_id">
                                <option value="1" {{ $vodType == 1 ? 'selected' : '' }}>Movies</option>
                                <option value="2" {{ $vodType == 2 ? 'selected' : '' }}>Series</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="time_to_keep_minutes">Time To Keep (Minutes)</label>
                            <input class="form-control" id="time_to_keep_minutes" name="time_to_keep_minutes" type="number"
                                   value="{{ !empty($magnet_link->time_to_keep_minutes) ? $magnet_link->time_to_keep_minutes : old('time_to_keep_minutes', '0') }}">
                        </div>
                    </div>

                </div>
            </div>
    </div>

    @include('components.form_inputs.save-btn-fixed',
    ['obj' => $server ?? null]
    )
    </div>


</form>
