<flux:button
    variant="primary"
    color="blue"
    wire:click="runSeeder('{{ $seeder['name'] }}')"
    onclick="if (!confirm('Are you sure you want to run this seeder?')) event.stopImmediatePropagation();"
    :disabled="$seeder['status'] === 'completed'">
    {{ __('Run Seeder') }}
</flux:button>