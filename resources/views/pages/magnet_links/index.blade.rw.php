<x-app-layout>

    <x-page.header title="torrent_vods::magnet_links.index" create="torrent_vods::magnet_links.create"></x-page.header>
    <x-main>
        <table id="magnet_links_table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Created</th>
                    <th>Link</th>
                    <th>Status</th>
                    <th>Vod Type</th>
                    <th>Time To Keep (minutes)</th>
                    <th>Files</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
        </table>
        @push('scripts')
        <script type="text/javascript" src="{{ asset('js/scripts/magnet_links/magnet_links_management.js') }}?{{config('app.version')}}"></script>
        @endpush
    </x-main>
</x-app-layout>
