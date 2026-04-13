<x-filament-widgets::widget>
    <x-filament::section>
        <h2 class="text-xl font-bold">
            Good {{ now()->format('A') == 'AM' ? 'Morning' : 'Evening' }},
            {{ auth()->user()->name }}!
        </h2>

        <p class="text-sm text-gray-500">
            {{ now()->format('l, d F Y') }}
        </p>
    </x-filament::section>
</x-filament-widgets::widget>
