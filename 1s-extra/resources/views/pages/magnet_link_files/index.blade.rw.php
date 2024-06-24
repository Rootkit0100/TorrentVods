<x-app-layout>

    <x-page.header title="torrent_vods::magnet_link_files.index"></x-page.header>
    <x-main>
        <div class="row form-group tableFilter">
            @if (!empty($_GET['magnet_link_id']))
            <div class="alert alert-primary">Results for Magnet Link {{ $_GET['magnet_link_id'] }}</div>
            <input type="hidden" value="{{ $_GET['magnet_link_id'] }}" name="magnet_link_id">
            @endif
        </div>
        <table id="magnet_link_files_table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Created</th>
                    <th>Magnet</th>
                    <th>Filename</th>
                    <th>Fileindex</th>
                    <th>Time To Keep (minutes)</th>
                    <th>VOD</th>
                    <th>Full Path</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
        </table>
        @push('scripts')
        <script type="text/javascript" src="{{ asset('js/scripts/magnet_links/magnet_links_management.js') }}?{{config('app.version')}}"></script>
        @endpush
    </x-main>
</x-app-layout>
