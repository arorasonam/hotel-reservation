<x-filament-widgets::widget>
    <x-filament::section>

        <div class="space-y-2 max-h-60 overflow-y-auto">
            @if(!empty($activities))
            @foreach($activities as $activity)

            <div class="text-sm text-gray-600">
                {{ $activity->description }}
                <span class="text-xs text-gray-400">
                    {{ $activity->created_at->diffForHumans() }}
                </span>
            </div>

            @endforeach
            @endif
        </div>

    </x-filament::section>
</x-filament-widgets::widget>