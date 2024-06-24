<x-app-layout>
    <x-page.header title="{{ __('torrent_vods::magnet_links.edit') . ' #' . $magnet_link->id }}" index="torrent_vods::magnet_links.index"></x-page.header>
    <x-main-form>
        @include('torrent_vods::pages.magnet_links.form', ['formUrl' => route('magnet_links.update', $magnet_link->id), 'formMethod' => 'PUT'])

        @push('scripts')
            <script type="text/javascript" src="{{ asset('js/scripts/magnet_links/magnet_links_management.js') }}?{{config('app.version')}}"></script>
        @endpush
    </x-main-form>
</x-app-layout>
