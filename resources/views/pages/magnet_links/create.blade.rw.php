<x-app-layout>
    <x-page.header title="torrent_vods::magnet_links.create" index="torrent_vods::magnet_links.index"></x-page.header>
    <x-main-form>
        @include('torrent_vods::pages.magnet_links.form', ['formUrl' => route('magnet_links.store'), 'formMethod' => 'POST'])

        @push('scripts')
            <script type="text/javascript" src="{{ asset('js/scripts/magnet_links/magnet_links_management.js') }}?{{config('app.version')}}"></script>
        @endpush
    </x-main-form>
</x-app-layout>
