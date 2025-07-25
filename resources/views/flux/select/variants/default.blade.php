@props([
    'name' => $attributes->get('name') ?? $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
])

@php
$invalid ??= ($name && $errors->has($name));

$classes = Flux::classes()
    ->add('appearance-none w-full ps-3 pe-10 block')
    ->add(match ($size) {
        default => 'h-10 py-2 text-sm leading-[1.375rem] rounded',
        'sm' => 'h-8 py-1.5 text-sm leading-[1.125rem] rounded',
        'xs' => 'h-6 text-xs leading-[1.125rem] rounded',
    })
    ->add('bg-white dark:bg-gray-800 dark:disabled:bg-white/[7%]')
    ->add('text-zinc-700 dark:text-zinc-300 disabled:text-zinc-500 dark:disabled:text-zinc-400')
    ->add('has-[option.placeholder:checked]:text-zinc-400 dark:has-[option.placeholder:checked]:text-zinc-400')
    ->add('dark:[&>option]:bg-zinc-700 dark:[&>option]:text-white')
    ->add('disabled:shadow-none')
    ->add('[&::-webkit-appearance]:none [&::-moz-appearance]:none')
    ->add($invalid
        ? 'border border-(--color-accent-2)'
        : 'border border-gray-300 dark:border-gray-600'
    );
@endphp

<div class="relative w-full">
    <select
        {{ $attributes->class($classes) }}
        @if ($invalid) aria-invalid="true" data-invalid @endif
        @isset($name) name="{{ $name }}" @endisset
        @if (is_numeric($size)) size="{{ $size }}" @endif
        data-flux-control
        data-flux-select-native
        data-flux-group-target
    >
        {{ $slot }}
    </select>
</div>