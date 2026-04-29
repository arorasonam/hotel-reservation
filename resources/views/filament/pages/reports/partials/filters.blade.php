{{-- Shared filter form partial used by all report views --}}
<div class="mb-6">
    <x-filament::section>
        <div class="flex items-end gap-4 flex-wrap">
            {{ $this->form }}
            <div class="flex gap-2 pb-1">
                <x-filament::button wire:click="$refresh" color="primary" icon="heroicon-o-funnel">
                    Apply
                </x-filament::button>
                <x-filament::button wire:click="export" color="success" icon="heroicon-o-arrow-down-tray">
                    Export Excel
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</div>