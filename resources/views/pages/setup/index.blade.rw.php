<x-app-layout>

    <x-page.header title="torrent_vods::setup.index"></x-page.header>
    <x-main>
        <div class="row mb-1 settingsForm">
            <div class="col-md-4">
                <label for="servers">Stream From Servers</label>
                @php($selectedServers=$settings['servers']??[])
                <select name="servers[]" class="select2 form-control" multiple>
                    @foreach($servers as $s)
                        <option value="{{$s->id}}"  {{ in_array($s->id, $selectedServers) ? 'selected' : '' }}> {{$s->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="download_limit">Download Limit (kB/s)</label>
                <input type="text" class="form-control" name="download_limit" value="{{ $settings['download_limit'] ?? 20000 }}">
            </div>
            <div class="col-md-4">
                <label for="upload_limit">Upload Limit (kB/s)</label>
                <input type="text" class="form-control" name="upload_limit" value="{{ $settings['upload_limit'] ?? 3000 }}">
            </div>
        </div>

        <div class="row mb-1 mt-4">
            <div class="col-md-12">
                <div class="alert alert-primary">
                    <div class="row">
                        <div class="col-md-12">
                            <button class="btn btn-warning installTorrentClient">
                                Install Torrent Client
                            </button>
                            <button class="btn btn-success createVods">
                                Create VODs from Magnet Links
                            </button>
                            <button class="btn btn-success linkVods">
                                Link VODs to Magnet Link Files
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered">
                    @foreach ($watchfolders as $w)
                    <tr>
                        <td><a href="{{ route('watch_folders.edit', ['watch_folder' => $w->id]) }}">{{ $w->title }}</a></td>
                        <td>{{ $w->server->name ?? '-' }}</td>
                        <td>{{ $w->path }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>

        @include('components.form_inputs.save-btn-fixed')

        @push('scripts')
        <script type="text/javascript" src="{{ asset('js/scripts/setup/setup.js') }}?{{config('app.version')}}"></script>
        @endpush
    </x-main>
</x-app-layout>