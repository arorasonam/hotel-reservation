<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach($stats as $stat)
    <x-filament::section class="text-center">
        <div class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ $stat['label'] }}</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stat['value'] }}</div>
    </x-filament::section>
    @endforeach
</div>