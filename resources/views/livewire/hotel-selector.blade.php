<div class="flex items-center gap-2 px-2">
    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Hotel:</span>
    <select
        wire:model.live="selectedHotel"
        class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-1.5 focus:ring-2 focus:ring-primary-500">

        @foreach ($hotels as $hotel)
            <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
        @endforeach
    </select>
</div>