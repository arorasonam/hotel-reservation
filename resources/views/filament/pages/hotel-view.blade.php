{{-- Hide Filament sidebar/topbar for this page only --}}
@push('styles')
<style>
    .fi-sidebar,
    aside[class*="fi-sidebar"],
    nav[class*="fi-sidebar"],
    .fi-sidebar-nav {
        display: none !important;
    }

    .fi-topbar,
    header.fi-topbar,
    [class*="fi-topbar"] {
        display: none !important;
    }

    .fi-layout,
    .fi-main-ctn,
    main.fi-main,
    .fi-main {
        padding-left: 0 !important;
        margin-left: 0 !important;
        padding-top: 0 !important;
    }

    .fi-page,
    [class*="fi-page"] {
        padding: 0 !important;
        max-width: 100% !important;
    }
</style>
@endpush

{{--
    ICON MACRO — explicit width/height on every SVG so Tailwind class
    application is irrelevant. Sizes: sm=14px, md=16px, lg=20px
--}}
@php
// size presets in px
$S = 'width="14" height="14"';
$M = 'width="16" height="16"';
$L = 'width="20" height="20"';
$XL= 'width="28" height="28"';

// helper closure — returns full inline SVG string
$svg = function(string $d, string $size = '', string $style = '') {
return '<svg xmlns="http://www.w3.org/2000/svg" ' . $size . ' ' .
               ' viewBox="0 0 24 24" fill="none" stroke="currentColor" ' .
               ' stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" ' .
               ' aria-hidden="true" style="display:inline-block;flex-shrink:0;' . $style . '">' .
    '
    <path d="' . $d . '" />
</svg>';
};

// paths
$pArrow = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';
$pPin = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
$pBuilding = 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21';
$pStar = 'M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z';
$pKey = 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0 1 21.75 8.25Z';
$pCheck = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
$pTick = 'M4.5 12.75l6 6 9-13.5';
$pPhoto = 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z';
$pDoc = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
$pIn = 'M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25';
$pOut = 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9';
$pCard = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z';
$pChat = 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z';
$pMap = 'M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z';
$pShield = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
$pBed = 'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z';
$pUsers = 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z';

// prebuilt icons at different sizes
$gallery_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:inline-block;flex-shrink:0;opacity:.9">
    <path d="' . $pPhoto . '" />
</svg>';
$map_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:inline-block;opacity:.5">
    <path d="' . $pMap . '" />
</svg>';

// Solid star for rating
$solidStar = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="display:inline-block;flex-shrink:0;color:#facc15">
    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
</svg>';
@endphp

<div class="min-h-screen" style="background:#f3f4f6;font-family:system-ui,sans-serif;">

    {{-- ═══ HEADER ═══════════════════════════════════════════════════════ --}}
    <div style="background:#003580;color:#fff;padding:10px 24px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:50;box-shadow:0 2px 8px rgba(0,0,0,.25);">
        <div style="display:flex;align-items:center;gap:10px;">
            <a href="{{ url()->previous() }}" style="display:flex;align-items:center;justify-content:center;padding:6px;border-radius:50%;color:#fff;text-decoration:none;transition:background .2s;" onmouseover="this.style.background='rgba(255,255,255,.12)'" onmouseout="this.style.background='transparent'">
                {!! $svg($pArrow, $M) !!}
            </a>
            <span style="font-weight:900;font-size:18px;letter-spacing:-.5px;">🏨 HotelFolio</span>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <button onclick="window.print()" style="padding:7px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.3);background:transparent;color:#fff;font-size:13px;font-weight:600;cursor:pointer;">🖨 Print</button>
            <button style="padding:7px 16px;border-radius:8px;background:#0071c2;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;">Book Now</button>
        </div>
    </div>

    {{-- ═══ TABS ══════════════════════════════════════════════════════════ --}}
    <div style="background:#fff;border-bottom:1px solid #e5e7eb;position:sticky;top:50px;z-index:40;box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="max-width:1100px;margin:0 auto;padding:0 16px;display:flex;overflow-x:auto;">
            @foreach(['overview'=>'Overview','facilities'=>'Facilities','rooms'=>'Rooms','reviews'=>'Reviews','location'=>'Location'] as $tab=>$label)
            <a href="#section-{{ $tab }}" style="padding:14px 18px;font-size:13px;font-weight:600;color:#4b5563;text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent;transition:color .15s,border-color .15s;" onmouseover="this.style.color='#0071c2';this.style.borderBottomColor='#0071c2';" onmouseout="this.style.color='#4b5563';this.style.borderBottomColor='transparent';">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div style="max-width:1100px;margin:0 auto;padding:28px 16px;">

        {{-- ═══ TITLE ROW ══════════════════════════════════════════════════ --}}
        <div id="section-overview" style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:24px;">
            <div style="flex:1;min-width:260px;">
                {{-- Stars --}}
                <div style="display:flex;align-items:center;gap:3px;margin-bottom:6px;">
                    @for($i=0; $i<($record->stars??4); $i++) {!! $solidStar !!} @endfor
                        <span style="font-size:11px;color:#6b7280;margin-left:4px;text-transform:uppercase;letter-spacing:.05em;">{{ $record->property_type ?? 'Hotel' }}</span>
                </div>

                <h1 style="font-size:26px;font-weight:900;color:#111827;line-height:1.2;margin:0 0 8px;">{{ $record->name }}</h1>

                <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:#6b7280;flex-wrap:wrap;">
                    <span style="display:flex;align-items:center;gap:4px;">
                        {!! $svg($pPin, $M, 'color:#0071c2') !!}
                        {{ collect([$record->address['street']??null,$record->address['city']??null,$record->address['state']??null])->filter()->implode(', ') }}
                    </span>
                    <a href="#section-location" style="color:#0071c2;font-weight:600;font-size:12px;text-decoration:none;">Show on map</a>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">
                    @if($record->is_featured??false) <span style="background:#dbeafe;color:#1e40af;font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;">⭐ Featured</span> @endif
                    @if($record->is_pet_friendly??false) <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;">🐾 Pet-friendly</span> @endif
                    <span style="background:#ffedd5;color:#c2410c;font-size:11px;font-weight:700;padding:3px 8px;border-radius:4px;">🔥 In high demand</span>
                </div>
            </div>

            {{-- Rating badge --}}
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="text-align:right;">
                        <div style="font-size:13px;font-weight:800;color:#111827;">@php $r=$record->rating??8.5; echo $r>=9?'Exceptional':($r>=8?'Excellent':($r>=7?'Very Good':'Good')); @endphp</div>
                        <div style="font-size:11px;color:#6b7280;">{{ $record->reviews_count??'1,284' }} reviews</div>
                    </div>
                    <div style="background:#003580;color:#fff;font-size:18px;font-weight:900;width:46px;height:46px;display:flex;align-items:center;justify-content:center;border-radius:8px 8px 8px 0;flex-shrink:0;">
                        {{ number_format($record->rating??8.5,1) }}
                    </div>
                </div>
                <div style="font-size:11px;color:#9ca3af;">Cleanliness · Location · Value</div>
            </div>
        </div>

        {{-- ═══ GALLERY ════════════════════════════════════════════════════ --}}
        @php
        $media = $record->medias ?? collect([]);
        $ph = ['https://placehold.co/800x600/003580/fff?text=Hotel','https://placehold.co/400x300/1a1a2e/fff?text=Room','https://placehold.co/400x300/16213e/fff?text=Lobby','https://placehold.co/400x300/0f3460/fff?text=Pool','https://placehold.co/400x300/533483/fff?text=Dining'];
        @endphp
        <div style="display:grid;grid-template-columns:repeat(4,1fr);grid-template-rows:repeat(2,200px);gap:6px;border-radius:16px;overflow:hidden;margin-bottom:28px;">
            <div style="grid-column:span 2;grid-row:span 2;overflow:hidden;">
                <img src="{{ $record->featured_image??($media->first()?->url??$ph[0]) }}" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'" alt="{{ $record->name }}">
            </div>
            @for($i=0;$i<3;$i++)
                <div style="overflow:hidden;">
                <img src="{{ $media->skip($i+1)->first()?->url??$ph[$i+1] }}" style="width:100%;height:100%;object-fit:cover;transition:transform .4s;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'" alt="Photo">
        </div>
        @endfor
        <div style="overflow:hidden;position:relative;cursor:pointer;" onclick="">
            <img src="{{ $media->skip(4)->first()?->url??$ph[4] }}" style="width:100%;height:100%;object-fit:cover;filter:brightness(.45);" alt="More photos">
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;pointer-events:none;">
                {!! $gallery_icon !!}
                <span style="font-size:20px;font-weight:900;margin-top:2px;">+{{ max(0,($media->count()?:12)-4) }}</span>
                <span style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;opacity:.85;">Show all</span>
            </div>
        </div>
    </div>

    {{-- ═══ CONTENT + SIDEBAR ══════════════════════════════════════════ --}}
    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

        {{-- ─── LEFT ─────────────────────────────────────────────── --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- ABOUT --}}
            <section style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <h2 style="font-size:15px;font-weight:900;color:#111827;margin:0 0 14px;display:flex;align-items:center;gap:7px;">
                    {!! $svg($pBuilding, $M, 'color:#0071c2') !!} About this property
                </h2>
                @if($record->descriptions && $record->descriptions->count())
                @foreach($record->descriptions as $desc)
                @if($desc->type==='main'||$loop->first)
                <div style="font-size:13px;color:#374151;line-height:1.65;margin-bottom:10px;">{!! nl2br(e($desc->content)) !!}</div>
                @endif
                @endforeach
                @if($record->descriptions->count()>1)
                <div x-data="{open:false}">
                    <div x-show="open" x-collapse style="font-size:13px;color:#374151;line-height:1.65;">
                        @foreach($record->descriptions->skip(1) as $desc)
                        <div style="margin-top:10px;">
                            @if($desc->title??false)<strong>{{ $desc->title }}</strong><br>@endif
                            {!! nl2br(e($desc->content)) !!}
                        </div>
                        @endforeach
                    </div>
                    <button @click="open=!open" style="margin-top:8px;color:#0071c2;font-size:12px;font-weight:600;background:none;border:none;cursor:pointer;padding:0;">
                        <span x-text="open?'Show less ▲':'Read more ▼'"></span>
                    </button>
                </div>
                @endif
                @else
                <p style="font-size:13px;color:#374151;line-height:1.65;margin:0;">{{ $record->description ?? 'Located in '.($record->address['city']??'the city').', '.$record->name.' offers refined comfort with premium amenities and exceptional service.' }}</p>
                @endif
            </section>

            {{-- AMENITIES --}}
            <section id="section-facilities" style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <h2 style="font-size:15px;font-weight:900;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:7px;">
                    {!! $svg($pStar, $M, 'color:#0071c2') !!} Amenities &amp; Facilities
                </h2>
                @php
                $ams = $record->hotelAmenities ?? $record->amenities ?? collect([]);
                $grp = $ams->groupBy('category');
                $defaults = ['Free WiFi','Parking','Swimming Pool','Fitness Centre','Restaurant','Bar','Room Service','Spa','Air Conditioning','Flat-screen TV','Safe','Concierge'];
                @endphp
                @if($grp->isNotEmpty())
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
                    @foreach($grp as $cat=>$items)
                    <div>
                        <div style="font-size:11px;font-weight:800;color:#374151;text-transform:uppercase;letter-spacing:.07em;border-bottom:1px solid #e5e7eb;padding-bottom:6px;margin-bottom:8px;">{{ $cat?:'General' }}</div>
                        @foreach($items as $am)
                        <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;padding:3px 0;">
                            {!! $svg($pCheck, $M, 'color:#16a34a') !!} {{ $am->name }}
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @else
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                    @forelse($ams as $am)
                    <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;">{!! $svg($pCheck, $M, 'color:#16a34a') !!} {{ $am->name }}</div>
                    @empty
                    @foreach($defaults as $d)
                    <div style="display:flex;align-items:center;gap:6px;font-size:13px;color:#374151;">{!! $svg($pCheck, $M, 'color:#16a34a') !!} {{ $d }}</div>
                    @endforeach
                    @endforelse
                </div>
                @endif
                <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #e5e7eb;font-size:11px;color:#9ca3af;">* Charges may apply for some facilities.</div>
            </section>

            {{-- ROOMS --}}
            <section id="section-rooms" style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <h2 style="font-size:15px;font-weight:900;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:7px;">
                    {!! $svg($pKey, $M, 'color:#0071c2') !!} Select your room
                </h2>
                @php
                $roomList = $record->rooms ?? collect([]);
                $demoRooms = [['Standard Room',22,2,'Twin beds',24500,null],['Deluxe Suite',38,3,'King bed',48000,56000]];
                @endphp
                @forelse($roomList as $room)
                <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:12px;display:flex;">
                    <img src="{{ $room->image??'https://placehold.co/180x130/f3f4f6/9ca3af?text=Room' }}" style="width:160px;min-height:120px;object-fit:cover;flex-shrink:0;" alt="{{ $room->name }}">
                    <div style="flex:1;padding:14px;display:flex;justify-content:space-between;gap:12px;">
                        <div>
                            <div style="font-size:14px;font-weight:800;color:#111827;margin-bottom:6px;">{{ $room->name }}</div>
                            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:6px;">
                                @if($room->size??false)<span style="background:#f3f4f6;font-size:11px;padding:2px 7px;border-radius:4px;display:flex;align-items:center;gap:3px;">{!! $svg($pBed,$S) !!} {{ $room->size }}m²</span>@endif
                                @if($room->max_guests??false)<span style="background:#f3f4f6;font-size:11px;padding:2px 7px;border-radius:4px;display:flex;align-items:center;gap:3px;">{!! $svg($pUsers,$S) !!} {{ $room->max_guests }} guests</span>@endif
                                @if($room->bed_type??false)<span style="background:#f3f4f6;font-size:11px;padding:2px 7px;border-radius:4px;">🛏 {{ $room->bed_type }}</span>@endif
                            </div>
                            @foreach($room->amenities??[] as $ra)
                            <div style="font-size:11px;color:#15803d;display:flex;align-items:center;gap:4px;">{!! $svg($pTick,$S,'color:#15803d') !!} {{ $ra }}</div>
                            @endforeach
                        </div>
                        <div style="text-align:right;display:flex;flex-direction:column;justify-content:space-between;align-items:flex-end;flex-shrink:0;">
                            <div>
                                @if($room->original_price??false)<div style="font-size:11px;color:#9ca3af;text-decoration:line-through;">₹{{ number_format($room->original_price) }}</div>@endif
                                <div style="font-size:22px;font-weight:900;color:#111827;">₹{{ number_format($room->price??$record->min_price??24500) }}</div>
                                <div style="font-size:11px;color:#6b7280;">/ night</div>
                            </div>
                            <button style="background:#0071c2;color:#fff;font-size:12px;font-weight:700;padding:7px 14px;border-radius:8px;border:none;cursor:pointer;">I'll reserve</button>
                        </div>
                    </div>
                </div>
                @empty
                @foreach($demoRooms as [$rn,$sz,$gst,$bed,$price,$orig])
                <div style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:12px;display:flex;">
                    <img src="https://placehold.co/180x130/f8fafc/cbd5e1?text={{ urlencode($rn) }}" style="width:160px;min-height:120px;object-fit:cover;flex-shrink:0;" alt="{{ $rn }}">
                    <div style="flex:1;padding:14px;display:flex;justify-content:space-between;gap:12px;">
                        <div>
                            <div style="font-size:14px;font-weight:800;color:#111827;margin-bottom:6px;">{{ $rn }}</div>
                            <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:6px;">
                                <span style="background:#f3f4f6;font-size:11px;padding:2px 7px;border-radius:4px;">{{ $sz }}m²</span>
                                <span style="background:#f3f4f6;font-size:11px;padding:2px 7px;border-radius:4px;">{{ $gst }} guests</span>
                                <span style="background:#f3f4f6;font-size:11px;padding:2px 7px;border-radius:4px;">🛏 {{ $bed }}</span>
                            </div>
                            <div style="font-size:11px;color:#15803d;display:flex;align-items:center;gap:4px;">{!! $svg($pTick,$S,'color:#15803d') !!} Free cancellation before check-in</div>
                        </div>
                        <div style="text-align:right;display:flex;flex-direction:column;justify-content:space-between;align-items:flex-end;flex-shrink:0;">
                            <div>
                                @if($orig)<div style="font-size:11px;color:#9ca3af;text-decoration:line-through;">₹{{ number_format($orig) }}</div>@endif
                                <div style="font-size:22px;font-weight:900;color:#111827;">₹{{ number_format($price) }}</div>
                                <div style="font-size:11px;color:#6b7280;">/ night</div>
                            </div>
                            <button style="background:#0071c2;color:#fff;font-size:12px;font-weight:700;padding:7px 14px;border-radius:8px;border:none;cursor:pointer;">I'll reserve</button>
                        </div>
                    </div>
                </div>
                @endforeach
                @endforelse
            </section>

            {{-- POLICIES --}}
            <section style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <h2 style="font-size:15px;font-weight:900;color:#111827;margin:0 0 16px;display:flex;align-items:center;gap:7px;">
                    {!! $svg($pDoc, $M, 'color:#0071c2') !!} Property Policies
                </h2>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                    <div>
                        <div style="font-size:12px;font-weight:800;color:#374151;margin-bottom:5px;display:flex;align-items:center;gap:5px;">{!! $svg($pIn,$M) !!} Check-in</div>
                        <div style="font-size:13px;color:#374151;">From <strong>{{ \Carbon\Carbon::parse($record->check_in_time??'14:00')->format('g:i A') }}</strong></div>
                        <div style="font-size:11px;color:#6b7280;margin-top:3px;">Photo ID required</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:800;color:#374151;margin-bottom:5px;display:flex;align-items:center;gap:5px;">{!! $svg($pOut,$M) !!} Check-out</div>
                        <div style="font-size:13px;color:#374151;">Until <strong>{{ \Carbon\Carbon::parse($record->check_out_time??'11:00')->format('g:i A') }}</strong></div>
                        <div style="font-size:11px;color:#6b7280;margin-top:3px;">Late checkout on request</div>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:800;color:#374151;margin-bottom:5px;display:flex;align-items:center;gap:5px;">{!! $svg($pCard,$M) !!} Payment</div>
                        <div style="font-size:13px;color:#374151;">Credit card · Cash</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:3px;">Prepayment may apply</div>
                    </div>
                </div>
                @if($record->cancellation_policy??false)
                <div style="margin-top:14px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;color:#15803d;">
                    <strong>Cancellation Policy:</strong> {{ $record->cancellation_policy }}
                </div>
                @endif
            </section>

            {{-- REVIEWS --}}
            <section id="section-reviews" style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <h2 style="font-size:15px;font-weight:900;color:#111827;margin:0;display:flex;align-items:center;gap:7px;">
                        {!! $svg($pChat,$M,'color:#0071c2') !!} Guest Reviews
                    </h2>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="text-align:right;">
                            <div style="font-size:13px;font-weight:800;color:#111827;">@php $r=$record->rating??8.5; echo $r>=9?'Exceptional':($r>=8?'Excellent':'Very Good'); @endphp</div>
                            <div style="font-size:11px;color:#6b7280;">{{ $record->reviews_count??'1,284' }} reviews</div>
                        </div>
                        <div style="background:#003580;color:#fff;font-size:20px;font-weight:900;width:48px;height:48px;display:flex;align-items:center;justify-content:center;border-radius:8px 8px 8px 0;flex-shrink:0;">
                            {{ number_format($record->rating??8.5,1) }}
                        </div>
                    </div>
                </div>

                {{-- Score bars --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:20px;">
                    @foreach(['Staff'=>9.2,'Facilities'=>8.8,'Cleanliness'=>9.0,'Comfort'=>8.7,'Value for money'=>8.3,'Location'=>9.1] as $cat=>$sc)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:11px;color:#6b7280;width:100px;flex-shrink:0;">{{ $cat }}</span>
                        <div style="flex:1;background:#e5e7eb;border-radius:99px;height:5px;">
                            <div style="background:#003580;height:5px;border-radius:99px;width:{{ ($sc/10)*100 }}%;"></div>
                        </div>
                        <span style="font-size:11px;font-weight:700;color:#374151;width:22px;text-align:right;">{{ $sc }}</span>
                    </div>
                    @endforeach
                </div>

                {{-- Review cards --}}
                @forelse($record->reviews??[] as $rev)
                <div style="border-top:1px solid #f3f4f6;padding-top:16px;margin-top:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#003580;color:#fff;font-size:13px;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ strtoupper(substr($rev->reviewer_name??'G',0,1)) }}</div>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#111827;">{{ $rev->reviewer_name??'Guest' }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $rev->reviewer_country??'' }}</div>
                            </div>
                        </div>
                        <div style="background:#003580;color:#fff;font-size:12px;font-weight:900;padding:4px 9px;border-radius:6px;">{{ number_format($rev->rating??9,1) }}</div>
                    </div>
                    @if($rev->title??false)<div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:4px;">{{ $rev->title }}</div>@endif
                    <div style="font-size:13px;color:#374151;line-height:1.55;">{{ $rev->content }}</div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:5px;">{{ \Carbon\Carbon::parse($rev->created_at)->format('F Y') }}</div>
                </div>
                @empty
                @foreach([['Priya S.','India','Fantastic Stay!',9.6,'The staff was incredibly warm and helpful. Room was spotless and comfortable.'],['Marco B.','Italy','Excellent value',8.4,'Great facilities and wonderful breakfast. The location is perfect for exploring the city.'],['Sarah L.','UK','Wonderful experience',9.0,'Everything was top notch. The pool and spa were outstanding. Check-in was smooth.']] as [$rn,$rc,$rt,$rs,$rx])
                <div style="border-top:1px solid #f3f4f6;padding-top:16px;margin-top:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:#003580;color:#fff;font-size:13px;font-weight:900;display:flex;align-items:center;justify-content:center;flex-shrink:0;">{{ strtoupper(substr($rn,0,1)) }}</div>
                            <div>
                                <div style="font-size:13px;font-weight:700;color:#111827;">{{ $rn }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $rc }}</div>
                            </div>
                        </div>
                        <div style="background:#003580;color:#fff;font-size:12px;font-weight:900;padding:4px 9px;border-radius:6px;">{{ $rs }}</div>
                    </div>
                    <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:4px;">{{ $rt }}</div>
                    <div style="font-size:13px;color:#374151;line-height:1.55;">{{ $rx }}</div>
                </div>
                @endforeach
                @endforelse
                <div style="text-align:center;margin-top:16px;">
                    <button style="color:#0071c2;font-size:13px;font-weight:700;background:none;border:none;cursor:pointer;text-decoration:underline;">Read all {{ $record->reviews_count??'1,284' }} reviews →</button>
                </div>
            </section>

            {{-- LOCATION --}}
            <section id="section-location" style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.05);">
                <h2 style="font-size:15px;font-weight:900;color:#111827;margin:0 0 10px;display:flex;align-items:center;gap:7px;">
                    {!! $svg($pPin,$M,'color:#0071c2') !!} Location
                </h2>
                <div style="font-size:13px;color:#6b7280;margin-bottom:12px;">
                    {{ collect([$record->address['street']??null,$record->address['city']??null,$record->address['state']??null,$record->address['pincode']??null])->filter()->implode(', ') }}
                </div>
                <div style="border-radius:12px;overflow:hidden;height:240px;background:#f3f4f6;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                    @if($record->latitude??false)
                    <iframe src="https://maps.google.com/maps?q={{ $record->latitude }},{{ $record->longitude }}&z=15&output=embed" style="width:100%;height:100%;border:none;" loading="lazy" title="Map"></iframe>
                    @else
                    <div style="text-align:center;color:#9ca3af;">
                        {!! $map_icon !!}
                        <div style="font-size:13px;font-weight:600;margin-top:6px;">Map not available</div>
                        <div style="font-size:11px;">Coordinates not set</div>
                    </div>
                    @endif
                </div>
                <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:12px;">
                    @foreach(['Airport'=>'18 km','Train Station'=>'2.1 km','City Centre'=>'3.5 km'] as $pl=>$dist)
                    <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:#374151;">
                        {!! $svg($pPin,$S,'color:#0071c2') !!} <span><strong>{{ $pl }}</strong> · {{ $dist }}</span>
                    </div>
                    @endforeach
                </div>
            </section>

        </div>{{-- end left --}}

        {{-- ─── SIDEBAR ──────────────────────────────────────────── --}}
        <aside style="position:sticky;top:110px;">
            <div style="background:#fff;border:2px solid #e5e7eb;border-radius:16px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.08);">
                <div style="background:#003580;color:#fff;padding:18px 20px;">
                    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;opacity:.7;margin-bottom:2px;">Starting from</div>
                    <div style="font-size:28px;font-weight:900;">₹{{ number_format($record->min_price??24500) }}<span style="font-size:12px;font-weight:500;opacity:.7;margin-left:4px;">/ night</span></div>
                    <div style="font-size:11px;opacity:.55;margin-top:2px;">Taxes &amp; fees may apply</div>
                </div>
                <div style="padding:16px 20px;display:flex;flex-direction:column;gap:14px;">
                    <div style="border:1px solid #d1d5db;border-radius:10px;overflow:hidden;">
                        <div style="display:flex;border-bottom:1px solid #e5e7eb;">
                            <div style="flex:1;padding:10px 12px;border-right:1px solid #e5e7eb;">
                                <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Check-in</div>
                                <div style="font-size:13px;font-weight:900;color:#111827;">{{ \Carbon\Carbon::parse($record->check_in_time??'14:00')->format('h:i A') }}</div>
                            </div>
                            <div style="flex:1;padding:10px 12px;">
                                <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Check-out</div>
                                <div style="font-size:13px;font-weight:900;color:#111827;">{{ \Carbon\Carbon::parse($record->check_out_time??'11:00')->format('h:i A') }}</div>
                            </div>
                        </div>
                        <div style="padding:10px 12px;">
                            <div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;">Guests</div>
                            <div style="font-size:13px;font-weight:900;color:#111827;">2 adults · 0 children · 1 room</div>
                        </div>
                    </div>

                    <button style="width:100%;background:#0071c2;color:#fff;padding:12px;border-radius:10px;font-size:13px;font-weight:800;border:none;cursor:pointer;letter-spacing:.02em;">Check Availability</button>
                    <div style="text-align:center;font-size:11px;color:#9ca3af;">No reservation fees · Confirm instantly</div>

                    <div style="border-top:1px solid #f3f4f6;padding-top:14px;">
                        <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#9ca3af;margin-bottom:10px;">Property Highlights</div>
                        @foreach(['Top location — rated 9.1 by guests','Breakfast included in some rates','Free cancellation available','Pool & Spa on-site'] as $hl)
                        <div style="display:flex;align-items:flex-start;gap:7px;font-size:12px;color:#374151;margin-bottom:7px;">
                            {!! $svg($pShield,$M,'color:#16a34a') !!} {{ $hl }}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>

    </div>{{-- end grid --}}
</div>{{-- end container --}}

{{-- FOOTER --}}
<div style="background:#111827;color:#fff;margin-top:48px;padding:20px 32px;">
    <div style="max-width:1100px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <span style="font-size:16px;font-weight:900;">🏨 HotelFolio</span>
        <span style="font-size:12px;color:#6b7280;">© {{ date('Y') }} · All rights reserved</span>
        <div style="display:flex;gap:16px;">
            <a href="#" style="font-size:12px;color:#6b7280;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#6b7280'">Privacy</a>
            <a href="#" style="font-size:12px;color:#6b7280;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#6b7280'">Terms</a>
            <a href="#" style="font-size:12px;color:#6b7280;text-decoration:none;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#6b7280'">Help</a>
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