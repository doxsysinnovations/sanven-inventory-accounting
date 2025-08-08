@props([
    'label' => null,
    'name' => null,
])

@php
// We only want to show the name attribute on the checkbox if it has been set
// manually, but not if it has been set from the wire:model attribute...
$showName = isset($name);

if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

$classes = Flux::classes()
    ->add('flex size-[1.125rem] rounded-[.3rem] mt-px outline-offset-2')
    ;
@endphp

<flux:with-inline-field :$attributes>
    <div class="inline-flex items-center space-x-2">
        <ui-checkbox {{ $attributes->class($classes) }} name="{{ $name }}" data-flux-control data-flux-checkbox>
            <flux:checkbox.indicator />
        </ui-checkbox>

        @if ($label)
            <label for="{{ $name }}" class="mt-0.5 text-sm font-medium text-gray-800 dark:text-white">
                {{ $label }}
            </label>
        @endif
    </div>
</flux:with-inline-field>
