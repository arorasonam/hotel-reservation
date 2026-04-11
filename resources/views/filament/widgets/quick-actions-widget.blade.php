<x-filament-widgets::widget>
    <x-filament::section>

        <div class="flex gap-3">

        <x-filament::button
                tag="a"
                href="{{ route('filament.admin.resources.reservations.create') }}"
            >
            New Reservation
            </x-filament::button>

        </div>

    </x-filament::section>
</x-filament-widgets::widget>
