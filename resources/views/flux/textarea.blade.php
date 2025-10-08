@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'resize' => 'vertical',
    'invalid' => null,
    'rows' => 4,
    'readonly' => false,
])

@php
$invalid ??= ($name && $errors->has($name));

$classes = Flux::classes()
    ->add('block p-3 w-full')
    ->add('shadow-xs disabled:shadow-none border rounded-sm')
    ->add(
        $attributes->has('readonly') || $readonly
            ? 'bg-gray-100 dark:bg-[#353F4D]'
            : 'bg-white dark:bg-[#353F4D] dark:disabled:bg-[#353F4D]'
    )
    ->add($resize ? 'resize-y' : 'resize-none')
    ->add('text-base sm:text-sm text-zinc-700 disabled:text-zinc-500 placeholder-zinc-400 disabled:placeholder-zinc-400/70 dark:text-zinc-300 dark:disabled:text-zinc-400 dark:placeholder-zinc-400 dark:disabled:placeholder-zinc-500')
    ->add($invalid ? 'border-(--color-accent-2)' : 'border-zinc-200 border-b-zinc-300/80 dark:border-gray-600');
    
$resizeStyle = match ($resize) {
    'none' => 'resize: none',
    'both' => 'resize: both',
    'horizontal' => 'resize: horizontal',
    'vertical' => 'resize: vertical',
};
@endphp

<flux:with-field :$attributes>
    <textarea
        {{ $attributes->class($classes) }}
        rows="{{ $rows }}"
        style="{{ $resizeStyle }}; {{ $rows === 'auto' ? 'field-sizing: content' : '' }}"
        @isset($name) name="{{ $name }}" @endisset
        @if($invalid) aria-invalid="true" data-invalid @endif
        @if($readonly || $attributes->has('readonly')) readonly @endif 
        data-flux-control
        data-flux-textarea
    >{{ $slot }}</textarea>
</flux:with-field>