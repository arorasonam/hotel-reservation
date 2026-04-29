{{-- ═══════════════════════════════════════════
     HIDE FILAMENT SIDEBAR + TOPBAR FOR THIS PAGE ONLY
═══════════════════════════════════════════ --}}
@push('styles')
<style>
    /* Hide Filament sidebar */
    .fi-sidebar,
    aside[class*="fi-sidebar"],
    nav[class*="fi-sidebar"],
    .fi-sidebar-nav,
    .fi-layout-sidebar,
    .filament-sidebar {
        display: none !important;
    }

    /* Hide Filament topbar */
    .fi-topbar,
    header.fi-topbar,
    [class*="fi-topbar"] {
        display: none !important;
    }

    /* Remove sidebar gap from main content */
    .fi-layout,
    .fi-main-ctn,
    main.fi-main,
    .fi-main {
        padding-left: 0 !important;
        margin-left: 0 !important;
        padding-top: 0 !important;
    }

    /* Remove page wrapper padding */
    .fi-page,
    [class*="fi-page"] {
        padding: 0 !important;
        max-width: 100% !important;
    }
</style>
@endpush

@php
/**
* Inline SVG icon helper — avoids Heroicon blade components
* which render at full (browser-default) size unless the class
* attribute is applied correctly in the current Filament version.
*
* Usage: {!! icon($path, 'w-5 h-5 text-blue-600') !!}
*/
function icon(string $path, string $classes = 'w-4 h-4'): string {
// We wrap the SVG in a span to ensure it doesn't fight with flex-basis
return '<span class="inline-flex shrink-0 ' . e($classes) . '">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
        stroke-width="2" stroke="currentColor" class="w-full h-full">
        <path stroke-linecap="round" stroke-linejoin="round" d="' . $path . '" />
    </svg>
</span>';
}

// ── Icon path constants ─────────────────────────────────────────────
$iArrowLeft = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';
$iMapPin = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
$iBuilding = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
$iSparkles = 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z';
$iKey = 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z';
$iCheckCirc = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
$iCheckLine = 'M4.5 12.75l6 6 9-13.5';
$iPhoto = 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z';
$iDocText = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
$iArrowIn = 'M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25';
$iArrowOut = 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9';
$iCard = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z';
$iChat = 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z';
$iMap = 'M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z';
$iSquares = 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z';
$iUsers = 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z';
$iShield = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
@endphp

<div class="min-h-screen bg-gray-50">

    {{-- ═══ TOP HEADER ═══════════════════════════════════════════════════ --}}
    <div class="bg-[#003580] text-white py-3 px-6 flex justify-between items-center shadow-md sticky top-0 z-50">
        <div class="flex items-center gap-3">
            <a href="{{ url()->previous() }}"
                class="p-2 hover:bg-white/10 rounded-full transition flex items-center justify-center">
                {!! icon($iArrowLeft, 'w-5 h-5') !!}
            </a>
            <span class="font-black tracking-tight text-xl">🏨 HotelFolio</span>
        </div>
        <div class="flex gap-3 items-center">
            <button onclick="window.print()"
                class="px-4 py-2 rounded-lg border border-white/30 text-white text-sm font-semibold hover:bg-white/10 transition">
                🖨 Print Folio
            </button>
            <button class="px-4 py-2 rounded-lg bg-[#0071c2] text-white text-sm font-bold hover:bg-[#005fa3] transition">
                Book Now
            </button>
        </div>
    </div>

    {{-- ═══ STICKY TABS ══════════════════════════════════════════════════ --}}
    <div class="bg-white border-b border-gray-200 sticky top-[52px] z-40 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 flex overflow-x-auto">
            @foreach(['overview' => 'Overview', 'facilities' => 'Facilities', 'rooms' => 'Rooms', 'reviews' => 'Reviews', 'location' => 'Location'] as $tab => $label)
            <a href="#section-{{ $tab }}"
                class="px-5 py-4 text-sm font-semibold text-gray-600 hover:text-[#0071c2] hover:border-b-2 hover:border-[#0071c2] transition border-b-2 border-transparent whitespace-nowrap">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-10">

        {{-- ═══ HOTEL TITLE ══════════════════════════════════════════════ --}}
        <div id="section-overview" class="flex flex-col md:flex-row md:items-start justify-between gap-6">
            <div class="space-y-2">
                {{-- Stars --}}
                <div class="flex items-center gap-0.5">
                    @for($i = 0; $i < ($record->stars ?? 4); $i++)
                        <span class="inline-block w-3 h-3 text-yellow-400 shrink-0">
                            <svg viewBox="0 0 20 20" fill="currentColor" class="w-full h-full">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </span>
                        @endfor
                </div>

                <h1 class="text-3xl font-black text-gray-900 leading-tight">{{ $record->name }}</h1>

                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600">
                    <span class="flex items-center gap-1">
                        {!! icon($iMapPin, 'w-4 h-4 text-[#0071c2] shrink-0') !!}
                        {{ collect([$record->address['street'] ?? null, $record->address['city'] ?? null, $record->address['state'] ?? null])->filter()->implode(', ') }}
                    </span>
                    <a href="#section-location" class="text-[#0071c2] hover:underline font-medium text-xs">Show on map</a>
                </div>

                <div class="flex flex-wrap gap-2 pt-1">
                    @if($record->is_featured ?? false)
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-1 rounded">⭐ Featured</span>
                    @endif
                    @if($record->is_pet_friendly ?? false)
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2.5 py-1 rounded">🐾 Pet-friendly</span>
                    @endif
                    <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2.5 py-1 rounded">🔥 In high demand</span>
                </div>
            </div>

            {{-- Rating box --}}
            <div class="flex flex-col items-end gap-1 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-sm font-bold text-gray-800">
                            @php $r = $record->rating ?? 8.5; echo $r >= 9 ? 'Exceptional' : ($r >= 8 ? 'Excellent' : ($r >= 7 ? 'Very Good' : 'Good')); @endphp
                        </p>
                        <p class="text-xs text-gray-500">{{ $record->reviews_count ?? '1,284' }} reviews</p>
                    </div>
                    <div class="bg-[#003580] text-white font-black text-xl w-12 h-12 flex items-center justify-center rounded-tl-xl rounded-tr-xl rounded-br-xl shrink-0">
                        {{ number_format($record->rating ?? 8.5, 1) }}
                    </div>
                </div>
                <p class="text-xs text-gray-400">Cleanliness · Location · Value</p>
            </div>
        </div>

        {{-- ═══ IMAGE GALLERY ════════════════════════════════════════════ --}}
        @php
        $galleryImages = $record->medias ?? collect([]);
        $ph = [
        'https://placehold.co/800x600/003580/white?text=Hotel',
        'https://placehold.co/400x300/1a1a2e/white?text=Room',
        'https://placehold.co/400x300/16213e/white?text=Lobby',
        'https://placehold.co/400x300/0f3460/white?text=Pool',
        'https://placehold.co/400x300/533483/white?text=Dining',
        ];
        @endphp
        <div class="grid grid-cols-4 grid-rows-2 gap-2 h-[420px] rounded-2xl overflow-hidden">
            {{-- Main --}}
            <div class="col-span-2 row-span-2 overflow-hidden">
                <img src="{{ $record->featured_image ?? ($galleryImages->first()?->url ?? $ph[0]) }}"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-500 cursor-pointer"
                    alt="{{ $record->name }}">
            </div>
            {{-- Thumbs 1–3 --}}
            @for($i = 0; $i < 3; $i++)
                <div class="col-span-1 row-span-1 overflow-hidden">
                <img src="{{ $galleryImages->skip($i + 1)->first()?->url ?? $ph[$i + 1] }}"
                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-500 cursor-pointer"
                    alt="Photo {{ $i + 2 }}">
        </div>
        @endfor
        {{-- Show all --}}
        <div class="col-span-1 row-span-1 overflow-hidden relative group cursor-pointer">
            <img src="{{ $galleryImages->skip(4)->first()?->url ?? $ph[4] }}"
                class="w-full h-full object-cover brightness-50 group-hover:scale-105 transition-transform duration-500"
                alt="More photos">
            <div class="absolute inset-0 flex flex-col items-center justify-center text-white pointer-events-none">
                {!! icon($iPhoto, 'w-7 h-7 mb-1 opacity-90') !!}
                <span class="text-xl font-black">+{{ max(0, ($galleryImages->count() ?: 12) - 4) }}</span>
                <span class="text-xs font-semibold uppercase tracking-widest opacity-80">Show all</span>
            </div>
        </div>
    </div>

    {{-- ═══ MAIN CONTENT + SIDEBAR ══════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ─── LEFT COLUMN ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- ABOUT --}}
            <section class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-900 mb-4 flex items-center gap-2">
                    {!! icon($iBuilding, 'w-5 h-5 text-[#0071c2] shrink-0') !!}
                    About this property
                </h2>

                @if($record->descriptions && $record->descriptions->count())
                @foreach($record->descriptions as $desc)
                @if($desc->type === 'main' || $loop->first)
                <div class="text-gray-700 leading-relaxed text-sm mb-4">
                    {!! nl2br(e($desc->content)) !!}
                </div>
                @endif
                @endforeach

                @if($record->descriptions->count() > 1)
                <div x-data="{ open: false }">
                    <div x-show="open" x-collapse class="space-y-4 text-sm text-gray-700">
                        @foreach($record->descriptions->skip(1) as $desc)
                        <div>
                            @if($desc->title ?? false)
                            <h4 class="font-bold text-gray-800 mb-1">{{ $desc->title }}</h4>
                            @endif
                            <p class="leading-relaxed">{!! nl2br(e($desc->content)) !!}</p>
                        </div>
                        @endforeach
                    </div>
                    <button @click="open = !open" class="mt-3 text-[#0071c2] text-sm font-semibold hover:underline">
                        <span x-text="open ? 'Show less ▲' : 'Read more ▼'"></span>
                    </button>
                </div>
                @endif
                @else
                <p class="text-gray-700 leading-relaxed text-sm">
                    {{ $record->description ?? 'Located in ' . ($record->address['city'] ?? 'the city') . ', ' . $record->name . ' offers refined comfort with premium amenities and exceptional service.' }}
                </p>
                @endif
            </section>

            {{-- FACILITIES --}}
            <section id="section-facilities" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    {!! icon($iSparkles, 'w-5 h-5 text-[#0071c2] shrink-0') !!}
                    Amenities and Facilities
                </h2>

                @php
                $amenities = $record->hotelAmenities ?? $record->amenities ?? collect([]);
                $grouped = $amenities->groupBy('category');
                $defaults = ['Free WiFi','Parking','Swimming Pool','Fitness Centre','Restaurant','Bar','Room Service','Spa','Air Conditioning','Flat-screen TV','Safe','Concierge'];
                @endphp

                @if($grouped->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($grouped as $category => $items)
                    <div>
                        <h4 class="font-black text-gray-700 text-xs uppercase tracking-widest mb-3 border-b pb-2">
                            {{ $category ?: 'General' }}
                        </h4>
                        <ul class="space-y-1.5">
                            @foreach($items as $amenity)
                            <li class="flex items-center gap-2 text-sm text-gray-700">
                                {!! icon($iCheckCirc, 'w-4 h-4 text-green-500') !!}
                                <span>{{ $amenity->name }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @forelse($amenities as $amenity)
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        {!! icon($iCheckCirc, 'w-4 h-4 text-green-500') !!}
                        <span>{{ $amenity->name }}</span>
                    </div>
                    @empty
                    @foreach($defaults as $a)
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                        {!! icon($iCheckCirc, 'w-4 h-4 text-green-500') !!}
                        <span>{{ $a }}</span>
                    </div>
                    @endforeach
                    @endforelse
                </div>
                @endif

                <p class="mt-5 pt-4 border-t border-dashed border-gray-200 text-xs text-gray-400">
                    * Charges may apply for some facilities. Please confirm at check-in.
                </p>
            </section>

            {{-- ROOMS --}}
            <section id="section-rooms" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-900 mb-6 flex items-center gap-2">
                    {!! icon($iKey, 'w-5 h-5 text-[#0071c2] shrink-0') !!}
                    Select your room
                </h2>

                @php
                $roomList = $record->rooms ?? collect([]);
                $demoRooms = [
                ['Standard Room', 22, 2, 'Twin beds', 24500, null],
                ['Deluxe Suite', 38, 3, 'King bed', 48000, 56000],
                ];
                @endphp

                @forelse($roomList as $room)
                <div class="border border-gray-200 rounded-xl overflow-hidden mb-4 hover:border-[#0071c2] hover:shadow-md transition-all">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-48 h-36 md:h-auto shrink-0 overflow-hidden">
                            <img src="{{ $room->image ?? 'https://placehold.co/200x150/f3f4f6/9ca3af?text=Room' }}"
                                class="w-full h-full object-cover" alt="{{ $room->name }}">
                        </div>
                        <div class="flex-1 p-4 flex flex-col md:flex-row gap-4">
                            <div class="flex-1 space-y-2">
                                <h3 class="font-black text-gray-900">{{ $room->name }}</h3>
                                <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                    @if($room->size ?? false)
                                    <span class="flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded">
                                        {!! icon($iSquares, 'w-3 h-3') !!} {{ $room->size }} m²
                                    </span>
                                    @endif
                                    @if($room->max_guests ?? false)
                                    <span class="flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded">
                                        {!! icon($iUsers, 'w-3 h-3') !!} {{ $room->max_guests }} guests
                                    </span>
                                    @endif
                                    @if($room->bed_type ?? false)
                                    <span class="bg-gray-100 px-2 py-0.5 rounded">🛏 {{ $room->bed_type }}</span>
                                    @endif
                                </div>
                                @foreach($room->amenities ?? [] as $ra)
                                <p class="text-xs text-green-700 flex items-center gap-1">
                                    {!! icon($iCheckLine, 'w-3 h-3 shrink-0') !!} {{ $ra }}
                                </p>
                                @endforeach
                            </div>
                            <div class="md:text-right flex md:flex-col justify-between items-end gap-3 shrink-0">
                                <div>
                                    @if($room->original_price ?? false)
                                    <p class="text-xs text-gray-400 line-through">₹{{ number_format($room->original_price) }}</p>
                                    @endif
                                    <p class="text-2xl font-black text-gray-900">₹{{ number_format($room->price ?? $record->min_price ?? 24500) }}</p>
                                    <p class="text-xs text-gray-500">/ night · excl. taxes</p>
                                </div>
                                <button class="bg-[#0071c2] text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#005fa3] transition">
                                    I'll reserve
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                @foreach($demoRooms as [$rn, $sz, $gst, $bed, $price, $orig])
                <div class="border border-gray-200 rounded-xl overflow-hidden mb-4 hover:border-[#0071c2] hover:shadow-md transition-all">
                    <div class="flex flex-col md:flex-row">
                        <div class="md:w-48 h-36 shrink-0 overflow-hidden">
                            <img src="https://placehold.co/200x150/f8fafc/cbd5e1?text={{ urlencode($rn) }}"
                                class="w-full h-full object-cover" alt="{{ $rn }}">
                        </div>
                        <div class="flex-1 p-4 flex flex-col md:flex-row gap-4">
                            <div class="flex-1 space-y-2">
                                <h3 class="font-black text-gray-900">{{ $rn }}</h3>
                                <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                                    <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $sz }} m²</span>
                                    <span class="bg-gray-100 px-2 py-0.5 rounded">{{ $gst }} guests</span>
                                    <span class="bg-gray-100 px-2 py-0.5 rounded">🛏 {{ $bed }}</span>
                                </div>
                                <p class="text-xs text-green-700 flex items-center gap-1">
                                    {!! icon($iCheckLine, 'w-3 h-3 shrink-0') !!} Free cancellation before check-in
                                </p>
                            </div>
                            <div class="md:text-right flex md:flex-col justify-between items-end gap-3 shrink-0">
                                <div>
                                    @if($orig)
                                    <p class="text-xs text-gray-400 line-through">₹{{ number_format($orig) }}</p>
                                    @endif
                                    <p class="text-2xl font-black text-gray-900">₹{{ number_format($price) }}</p>
                                    <p class="text-xs text-gray-500">/ night</p>
                                </div>
                                <button class="bg-[#0071c2] text-white text-sm font-bold px-4 py-2 rounded-lg hover:bg-[#005fa3] transition">
                                    I'll reserve
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @endforelse
            </section>

            {{-- POLICIES --}}
            <section class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-900 mb-5 flex items-center gap-2">
                    {!! icon($iDocText, 'w-5 h-5 text-[#0071c2] shrink-0') !!}
                    Property Policies
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
                    <div>
                        <h4 class="font-black text-gray-700 mb-2 flex items-center gap-1">
                            {!! icon($iArrowIn, 'w-4 h-4 shrink-0') !!} Check-in
                        </h4>
                        <p class="text-gray-600">From <strong>{{ \Carbon\Carbon::parse($record->check_in_time ?? '14:00')->format('g:i A') }}</strong></p>
                        <p class="text-gray-500 text-xs mt-1">Photo ID required</p>
                    </div>
                    <div>
                        <h4 class="font-black text-gray-700 mb-2 flex items-center gap-1">
                            {!! icon($iArrowOut, 'w-4 h-4 shrink-0') !!} Check-out
                        </h4>
                        <p class="text-gray-600">Until <strong>{{ \Carbon\Carbon::parse($record->check_out_time ?? '11:00')->format('g:i A') }}</strong></p>
                        <p class="text-gray-500 text-xs mt-1">Late checkout on request</p>
                    </div>
                    <div>
                        <h4 class="font-black text-gray-700 mb-2 flex items-center gap-1">
                            {!! icon($iCard, 'w-4 h-4 shrink-0') !!} Payment
                        </h4>
                        <p class="text-gray-600">Credit card · Cash</p>
                        <p class="text-gray-500 text-xs mt-1">Prepayment may apply</p>
                    </div>
                </div>
                @if($record->cancellation_policy ?? false)
                <div class="mt-5 p-4 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
                    <strong>Cancellation Policy:</strong> {{ $record->cancellation_policy }}
                </div>
                @endif
            </section>

            {{-- REVIEWS --}}
            <section id="section-reviews" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-black text-gray-900 flex items-center gap-2">
                        {!! icon($iChat, 'w-5 h-5 text-[#0071c2] shrink-0') !!}
                        Guest Reviews
                    </h2>
                    <div class="flex items-center gap-3">
                        <div class="bg-[#003580] text-white font-black text-2xl w-14 h-14 flex items-center justify-center rounded-tl-xl rounded-tr-xl rounded-br-xl shrink-0">
                            {{ number_format($record->rating ?? 8.5, 1) }}
                        </div>
                        <div>
                            <p class="font-black text-gray-800">
                                @php $r = $record->rating ?? 8.5; echo $r >= 9 ? 'Exceptional' : ($r >= 8 ? 'Excellent' : 'Very Good'); @endphp
                            </p>
                            <p class="text-sm text-gray-500">{{ $record->reviews_count ?? '1,284' }} reviews</p>
                        </div>
                    </div>
                </div>

                {{-- Score bars --}}
                <div class="grid grid-cols-2 gap-3 mb-8">
                    @foreach(['Staff' => 9.2, 'Facilities' => 8.8, 'Cleanliness' => 9.0, 'Comfort' => 8.7, 'Value for money' => 8.3, 'Location' => 9.1] as $cat => $sc)
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-600 w-28 shrink-0">{{ $cat }}</span>
                        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
                            <div class="bg-[#003580] h-1.5 rounded-full" style="width:{{ ($sc / 10) * 100 }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-700 w-6">{{ $sc }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Review cards --}}
                @forelse($record->reviews ?? [] as $review)
                <div class="border-t border-gray-100 pt-5 mt-5">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-[#003580] text-white font-black flex items-center justify-center text-sm shrink-0">
                            {{ strtoupper(substr($review->reviewer_name ?? 'G', 0, 1)) }}
                        </div>
                        <div class="flex-1 flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ $review->reviewer_name ?? 'Guest' }}</p>
                                <p class="text-xs text-gray-400">{{ $review->reviewer_country ?? '' }}</p>
                            </div>
                            <div class="bg-[#003580] text-white text-sm font-black px-2 py-1 rounded-lg shrink-0">
                                {{ number_format($review->rating ?? 9.0, 1) }}
                            </div>
                        </div>
                    </div>
                    @if($review->title ?? false)
                    <p class="font-bold text-gray-800 text-sm mb-1">{{ $review->title }}</p>
                    @endif
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $review->content }}</p>
                    <p class="text-xs text-gray-400 mt-2">{{ \Carbon\Carbon::parse($review->created_at)->format('F Y') }}</p>
                </div>
                @empty
                @foreach([
                ['Priya S.','India','Fantastic Stay!',9.6,'The staff was incredibly warm and helpful. Room was spotless and comfortable. Would definitely return on my next visit!'],
                ['Marco B.','Italy','Excellent value',8.4,'Great facilities and wonderful breakfast. The location is perfect for exploring the city. Highly recommended.'],
                ['Sarah L.','UK','Wonderful experience',9.0,'Everything was top notch. The pool and spa were outstanding. Check-in was smooth and the room exceeded expectations.'],
                ] as [$rname, $rcountry, $rtitle, $rscore, $rcontent])
                <div class="border-t border-gray-100 pt-5 mt-5">
                    <div class="flex items-start gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-[#003580] text-white font-black flex items-center justify-center text-sm shrink-0">
                            {{ strtoupper(substr($rname, 0, 1)) }}
                        </div>
                        <div class="flex-1 flex items-center justify-between">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ $rname }}</p>
                                <p class="text-xs text-gray-400">{{ $rcountry }}</p>
                            </div>
                            <div class="bg-[#003580] text-white text-sm font-black px-2 py-1 rounded-lg shrink-0">{{ $rscore }}</div>
                        </div>
                    </div>
                    <p class="font-bold text-gray-800 text-sm mb-1">{{ $rtitle }}</p>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $rcontent }}</p>
                </div>
                @endforeach
                @endforelse

                <div class="mt-6 text-center">
                    <button class="text-[#0071c2] font-bold text-sm hover:underline">
                        Read all {{ $record->reviews_count ?? '1,284' }} reviews →
                    </button>
                </div>
            </section>

            {{-- LOCATION --}}
            <section id="section-location" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-900 mb-4 flex items-center gap-2">
                    {!! icon($iMapPin, 'w-5 h-5 text-[#0071c2] shrink-0') !!}
                    Location
                </h2>
                <p class="text-sm text-gray-600 mb-4">
                    {{ collect([$record->address['street'] ?? null, $record->address['city'] ?? null, $record->address['state'] ?? null, $record->address['pincode'] ?? null])->filter()->implode(', ') }}
                </p>

                <div class="rounded-xl overflow-hidden h-64 bg-gray-100 border relative">
                    @if($record->latitude ?? false)
                    <iframe src="https://maps.google.com/maps?q={{ $record->latitude }},{{ $record->longitude }}&z=15&output=embed"
                        class="w-full h-full border-0" loading="lazy" title="Map"></iframe>
                    @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                        {!! icon($iMap, 'w-12 h-12 mb-2') !!}
                        <p class="text-sm font-medium">Map not available</p>
                        <p class="text-xs">Coordinates not set for this property</p>
                    </div>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach(['Airport' => '18 km', 'Train Station' => '2.1 km', 'City Centre' => '3.5 km'] as $place => $dist)
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        {!! icon($iMapPin, 'w-4 h-4 text-[#0071c2] shrink-0') !!}
                        <span><span class="font-bold">{{ $place }}</span> · {{ $dist }}</span>
                    </div>
                    @endforeach
                </div>
            </section>

        </div>{{-- end left --}}

        {{-- ─── STICKY SIDEBAR ──────────────────────────────────── --}}
        <aside class="lg:col-span-1">
            <div class="bg-white border-2 border-gray-200 rounded-2xl shadow-xl sticky top-[110px] overflow-hidden">

                <div class="bg-[#003580] text-white p-5">
                    <p class="text-xs font-bold uppercase tracking-widest opacity-70 mb-1">Starting from</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-3xl font-black">₹{{ number_format($record->min_price ?? 24500) }}</span>
                        <span class="text-xs opacity-70 font-semibold">/ night</span>
                    </div>
                    <p class="text-xs opacity-60 mt-1">Taxes &amp; fees may apply</p>
                </div>

                <div class="p-5 space-y-5">
                    <div class="border border-gray-300 rounded-xl overflow-hidden divide-y divide-gray-200">
                        <div class="flex divide-x divide-gray-200">
                            <div class="flex-1 p-3">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Check-in</p>
                                <p class="font-black text-gray-900 text-sm">{{ \Carbon\Carbon::parse($record->check_in_time ?? '14:00')->format('h:i A') }}</p>
                            </div>
                            <div class="flex-1 p-3">
                                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Check-out</p>
                                <p class="font-black text-gray-900 text-sm">{{ \Carbon\Carbon::parse($record->check_out_time ?? '11:00')->format('h:i A') }}</p>
                            </div>
                        </div>
                        <div class="p-3">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Guests</p>
                            <p class="font-black text-gray-900 text-sm">2 adults · 0 children · 1 room</p>
                        </div>
                    </div>

                    <button class="w-full bg-[#0071c2] text-white py-3.5 rounded-xl font-black text-sm hover:bg-[#005fa3] transition">
                        Check Availability
                    </button>

                    <p class="text-center text-xs text-gray-400">No reservation fees · Confirm instantly</p>

                    <div class="border-t border-gray-100 pt-4 space-y-2">
                        <p class="text-xs font-black uppercase tracking-widest text-gray-400 mb-3">Property Highlights</p>
                        @foreach(['Top location — rated 9.1 by guests', 'Breakfast included in some rates', 'Free cancellation available', 'Pool &amp; Spa on-site'] as $highlight)
                        <div class="flex items-start gap-2.5 text-sm text-gray-700">
                            <div class="mt-0.5"> {{-- Manual offset for alignment --}}
                                {!! icon($iShield, 'w-3.5 h-3.5 text-green-600') !!}
                            </div>
                            <span>{!! $highlight !!}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

    </div>{{-- end grid --}}
</div>{{-- end container --}}

{{-- FOOTER --}}
<div class="bg-gray-900 text-white mt-16 py-6 px-8">
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
        <span class="font-black text-lg">🏨 HotelFolio</span>
        <p class="text-gray-400 text-sm">© {{ date('Y') }} · All rights reserved</p>
        <div class="flex gap-4 text-sm text-gray-400">
            <a href="#" class="hover:text-white transition">Privacy</a>
            <a href="#" class="hover:text-white transition">Terms</a>
            <a href="#" class="hover:text-white transition">Help</a>
        </div>
    </div>
</div>

</div>

@push('scripts')
<script>
    document.querySelectorAll('a[href^="#section-"]').forEach(a => {
        a.addEventListener('click', e => {
            e.preventDefault();
            const el = document.querySelector(a.getAttribute('href'));
            if (el) window.scrollTo({
                top: el.getBoundingClientRect().top + window.pageYOffset - 110,
                behavior: 'smooth'
            });
        });
    });
</script>
@endpush