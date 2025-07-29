@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <span class="font-extrabold text-xl md:text-2xl">{{ $title }}</span>
    <flux:subheading>{{ $description }}</flux:subheading>
</div>
