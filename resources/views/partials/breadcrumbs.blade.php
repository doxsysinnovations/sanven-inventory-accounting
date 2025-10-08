<flux:breadcrumbs>
    <flux:breadcrumbs.item href="{{ route('dashboard') }}" icon="home" />

    @foreach ($breadcrumbs as $breadcrumb)
        @if (!is_null($breadcrumb->url) && !$loop->last)
            <flux:breadcrumbs.item 
                href="{{ $breadcrumb->url }}" 
            >
                {{ $breadcrumb->title }}
            </flux:breadcrumbs.item>
        @else
            <flux:breadcrumbs.item>
                {{ $breadcrumb->title }}
            </flux:breadcrumbs.item>
        @endif
    @endforeach
</flux:breadcrumbs>
