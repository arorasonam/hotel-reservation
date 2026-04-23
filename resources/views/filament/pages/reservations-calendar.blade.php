<x-filament::page>

    {{--
        LIVEWIRE COMPONENT ID — injected into a JS variable here in the blade template
        (before @push scripts) so it is available when the pushed script runs.
        This is the correct, reliable way to call Livewire actions from pushed scripts.
    --}}
    <script>
        window.__livewireCalendarId = '{{ $this->getId() }}';
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        #hotel-calendar-wrap * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        #hotel-calendar-wrap {
            --primary: #45556c;
            --primary-hover: #374155;
            --bg: #f4f6fa;
            --surface: #ffffff;
            --border: #e2e6ef;
            --text: #1a1f36;
            --muted: #6b7a99;
            --today-bg: #45556c;
            --today-fg: #ffffff;
            --occupied: #f87171;
            --mng: #4ade80;
            --advance: #34d399;
            --partial: #60a5fa;
            --vacant-bg: #f0f4ff;
            --vacant: #22c55e;
            --dirty: #ef4444;
            --mnt: #facc15;
            --room-w: 90px;
            --col-w: 110px;
            --row-h: 34px;
            --header-h: 52px;
            font-size: 13px;
            color: var(--text);
            background: var(--bg);
            padding: 16px;
            border-radius: 12px;
        }

        /* ── Toolbar ── */
        .hc-toolbar {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }

        .hc-toolbar select,
        .hc-toolbar input[type="date"] {
            padding: 7px 12px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--surface);
            color: var(--text);
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }

        .hc-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--surface);
            color: var(--text);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s;
        }

        .hc-btn.primary {
            background: #45556c;
            color: #fff;
            border-color: #45556c;
        }

        .hc-btn.primary:hover {
            background: #374155;
        }

        .hc-spacer {
            flex: 1;
        }

        .hc-nav-btn {
            width: 30px;
            height: 30px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--surface);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: var(--muted);
            transition: background .15s;
        }

        .hc-nav-btn:hover {
            background: #f0f4ff;
            color: #45556c;
        }

        /* ── Calendar Grid ── */
        .hc-scroll-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .hc-grid {
            display: grid;
            grid-template-columns: var(--room-w) repeat(var(--hc-days, 9), var(--col-w));
            min-width: max-content;
        }

        /* Header cells */
        .hc-head-cell {
            height: var(--header-h);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-right: 1px solid var(--border);
            border-bottom: 2px solid var(--border);
            background: var(--surface);
            position: sticky;
            top: 0;
            z-index: 10;
            font-weight: 600;
            user-select: none;
        }

        .hc-head-cell.room-label {
            justify-content: flex-start;
            align-items: flex-start;
            padding: 8px 10px;
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            border-right: 2px solid var(--border);
        }

        .hc-head-cell.today {
            background: var(--today-bg);
            color: var(--today-fg);
        }

        .hc-head-cell .day-num {
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
        }

        .hc-head-cell .day-month {
            font-size: 10px;
            font-weight: 500;
            opacity: .75;
        }

        .hc-head-cell .day-name {
            font-size: 10px;
            opacity: .75;
        }

        /* Vacant row */
        .hc-vacant-cell {
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted);
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: var(--vacant-bg);
        }

        .hc-vacant-cell.label {
            justify-content: space-between;
            padding: 0 8px;
            border-right: 2px solid var(--border);
            font-size: 11px;
            text-transform: uppercase;
        }

        /* Room type header */
        .hc-type-cell {
            height: 30px;
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            background: #f8f9fd;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .hc-type-cell.label {
            border-right: 2px solid var(--border);
        }

        .hc-type-cell.count-cell {
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            color: #45556c;
        }

        /* Room label */
        .hc-room-label {
            height: var(--row-h);
            display: flex;
            align-items: center;
            padding: 0 6px 0 8px;
            font-size: 12px;
            font-weight: 600;
            border-right: 2px solid var(--border);
            border-bottom: 1px solid var(--border);
            position: sticky;
            left: 0;
            z-index: 5;
            transition: background .2s, color .2s;
            background: var(--surface);
            color: var(--text);
        }

        .hc-room-label.s-clean {
            background: #22c55e;
            color: #fff;
        }

        .hc-room-label.s-dirty {
            background: #ef4444;
            color: #fff;
        }

        .hc-room-label.s-mnt {
            background: #facc15;
            color: #78350f;
        }

        .hc-room-label.s-ooo {
            background: #6b7280;
            color: #fff;
        }

        .hc-room-label.s-complaint {
            background: #f97316;
            color: #fff;
        }

        .hc-room-label.s-sanitised {
            background: #06b6d4;
            color: #fff;
        }

        .hc-room-label.s-vip {
            background: #8b5cf6;
            color: #fff;
        }

        .hc-room-label.s-inspect {
            background: #f59e0b;
            color: #fff;
        }

        .hc-room-label.s-discrepancy {
            background: #ec4899;
            color: #fff;
        }

        /* Brush icon + status dropdown */
        .hc-brush-wrap {
            position: relative;
            margin-left: auto;
            flex-shrink: 0;
        }

        .hc-brush-btn {
            width: 22px;
            height: 22px;
            border: none;
            border-radius: 4px;
            background: rgba(255, 255, 255, .28);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .15s;
            padding: 0;
            color: inherit;
        }

        .hc-brush-btn:hover {
            background: rgba(255, 255, 255, .5);
        }

        .hc-room-label:not([class*="s-"]) .hc-brush-btn,
        .hc-room-label.s-clean~* .hc-brush-btn {
            /* fallback */
        }

        /* When no status class — default surface room */
        .hc-room-label[class="hc-room-label"] .hc-brush-btn {
            background: rgba(0, 0, 0, .07);
            color: var(--muted);
        }

        .hc-status-dd {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 30px;
            z-index: 9999;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, .18);
            min-width: 220px;
            overflow: hidden;
        }

        .hc-status-dd.open {
            display: block;
        }

        .hc-status-dd-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 14px;
            font-size: 13px;
            color: var(--text);
            cursor: pointer;
            transition: background .1s;
            user-select: none;
        }

        .hc-status-dd-item:hover {
            background: #f0f4ff;
        }

        .hc-status-dd-item.active {
            font-weight: 700;
            background: #f8f9fd;
        }

        .hc-s-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-block;
        }

        .dd-tick {
            margin-left: auto;
            font-size: 13px;
            color: var(--primary);
            font-weight: 700;
        }

        .hc-status-dd-item.saving {
            opacity: .55;
            pointer-events: none;
        }

        /* Day cell */
        .hc-day-cell {
            height: var(--row-h);
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            position: relative;
            cursor: pointer;
            transition: background .1s;
            overflow: visible;
        }

        .hc-day-cell:hover {
            background: #eef3ff;
        }

        .hc-day-cell.today-col {
            background: #f5f8ff;
        }

        /* Booking chip */
        .hc-booking {
            position: absolute;
            top: 4px;
            bottom: 4px;
            left: 2px;
            display: flex;
            align-items: center;
            padding: 0 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            cursor: pointer;
            z-index: 2;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .15);
            transition: filter .15s;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .hc-booking:hover {
            filter: brightness(1.1);
            z-index: 10;
        }

        .hc-booking.occupied {
            background: #f87171;
        }

        .hc-booking.mng {
            background: #4ade80;
        }

        .hc-booking.advance {
            background: #34d399;
        }

        .hc-booking.partial {
            background: #60a5fa;
        }

        .hc-booking .chk {
            width: 14px;
            height: 14px;
            background: rgba(255, 255, 255, .35);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            font-size: 9px;
            flex-shrink: 0;
        }

        /* Legend */
        .hc-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 12px;
            font-size: 11px;
            color: var(--muted);
            align-items: center;
        }

        .hc-legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .hc-legend-dot {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }

        /* ══ BOOKING DETAIL POPOVER ══ */
        .hc-popover {
            display: none;
            position: fixed;
            z-index: 10000;
            width: 340px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, .22);
            overflow: hidden;
            animation: hcPopIn .15s ease;
        }

        .hc-popover.open {
            display: block;
        }

        @keyframes hcPopIn {
            from {
                opacity: 0;
                transform: scale(.95) translateY(6px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .pop-head {
            display: flex;
            background: #111827;
            color: #fff;
            min-height: 54px;
            position: relative;
        }

        .pop-res-bar {
            background: #22c55e;
            color: #fff;
            font-weight: 700;
            font-size: 12px;
            padding: 0 12px;
            display: flex;
            align-items: center;
            min-width: 120px;
            border-radius: 12px 0 0 0;
            letter-spacing: .02em;
        }

        .pop-ref {
            padding: 10px 12px;
            flex: 1;
        }

        .pop-ref-lbl {
            font-size: 10px;
            opacity: .55;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .pop-ref-val {
            font-size: 14px;
            font-weight: 700;
            margin-top: 2px;
        }

        .pop-edit {
            position: absolute;
            top: 10px;
            right: 38px;
            font-size: 11px;
            font-weight: 700;
            color: #f97316;
            cursor: pointer;
            text-transform: uppercase;
        }

        .pop-close {
            position: absolute;
            top: 8px;
            right: 10px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
            font-size: 18px;
            opacity: .65;
            line-height: 1;
        }

        .pop-close:hover {
            opacity: 1;
        }

        .pop-body {
            padding: 14px 16px 16px;
        }

        .pop-dates {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            text-align: center;
            margin-bottom: 12px;
        }

        .pop-date .d {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            color: var(--text);
        }

        .pop-date .m {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
            text-transform: uppercase;
        }

        .pop-nights {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }

        .pop-nights .n {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
        }

        .pop-nights .dots {
            font-size: 10px;
            color: var(--muted);
            letter-spacing: 2px;
        }

        .pop-room {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .pop-divider {
            height: 1px;
            background: var(--border);
            margin: 10px 0;
        }

        .pop-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .pop-email {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .pop-badges {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .pop-badge-status {
            border: 2px solid #22c55e;
            color: #22c55e;
            border-radius: 50%;
            padding: 4px 9px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .pop-badge-source {
            background: #e8f0fe;
            color: #003580;
            border-radius: 5px;
            padding: 3px 9px;
            font-size: 12px;
            font-weight: 700;
        }

        .pop-billing {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .pop-bill-lbl {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--muted);
            margin-bottom: 2px;
        }

        .pop-bill-amt {
            font-size: 18px;
            font-weight: 700;
        }

        .pop-bill-amt.red {
            color: #ef4444;
        }

        .pop-actions {
            display: flex;
            gap: 8px;
            margin-bottom: 10px;
        }

        .pop-action {
            flex: 1;
            padding: 8px 4px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: #f8f9fd;
            cursor: pointer;
            font-size: 11px;
            color: var(--muted);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            transition: background .15s;
        }

        .pop-action:hover {
            background: #e8f0fe;
            color: var(--primary);
        }

        .pop-action svg {
            width: 18px;
            height: 18px;
        }

        .pop-cancel {
            width: 100%;
            padding: 10px;
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: .04em;
            transition: background .15s;
        }

        .pop-cancel:hover {
            background: #dc2626;
        }

        .pop-cancel:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        /* ══ RESERVATION MODAL ══ */
        .hc-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 20, 50, .48);
            z-index: 10001;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .hc-modal-overlay.open {
            display: flex;
        }

        .hc-modal {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .24);
            width: 580px;
            max-width: 96vw;
            max-height: 92vh;
            overflow-y: auto;
            animation: hcModalIn .2s ease;
        }

        @keyframes hcModalIn {
            from {
                opacity: 0;
                transform: scale(.96) translateY(10px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .hc-modal-hd {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            background: #f8f9fd;
            border-radius: 14px 14px 0 0;
        }

        .hc-modal-title {
            font-size: 15px;
            font-weight: 700;
        }

        .hc-modal-sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 2px;
        }

        .hc-modal-x {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--surface);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--muted);
        }

        .hc-modal-x:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        .hc-modal-bd {
            padding: 18px 20px;
        }

        .hc-modal-ft {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            background: #f8f9fd;
            border-radius: 0 0 14px 14px;
        }

        .fi-lbl {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: var(--muted);
            margin: 16px 0 10px;
        }

        .fi-lbl:first-child {
            margin-top: 0;
        }

        .fi-g2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .fi-g3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
        }

        .fi-g4 {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 60px;
            gap: 10px;
        }

        .fi-f {
            display: flex;
            flex-direction: column;
        }

        .fi-fl {
            font-size: 12px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 4px;
        }

        .fi-fl .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .fi-in,
        .fi-sel {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            color: var(--text);
            background: var(--surface);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .fi-in:focus,
        .fi-sel:focus {
            border-color: #45556c;
            box-shadow: 0 0 0 3px rgba(69, 85, 108, .12);
        }

        .fi-div {
            height: 1px;
            background: var(--border);
            margin: 14px 0;
        }

        .btn-save {
            padding: 8px 20px;
            background: #45556c;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-save:hover {
            background: #374155;
        }

        .btn-save:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .btn-cancel {
            padding: 8px 16px;
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-cancel:hover {
            background: #f9fafb;
        }

        /* Toast */
        .hc-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 10002;
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .2);
            display: none;
            animation: hcToastIn .2s ease;
        }

        .hc-toast.show {
            display: block;
        }

        .hc-toast.success {
            background: #065f46;
        }

        .hc-toast.error {
            background: #991b1b;
        }

        @keyframes hcToastIn {
            from {
                opacity: 0;
                transform: translateY(8px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        @media(max-width:640px) {

            .fi-g2,
            .fi-g3,
            .fi-g4 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- ── CALENDAR WRAP ── --}}
    <div id="hotel-calendar-wrap">
        <div class="hc-toolbar">

            <select id="hc-property-select">
                @foreach($hotels as $hotel)
                <option value="{{ $hotel['id'] }}">{{ $hotel['name'] }}</option>
                @endforeach
            </select>
            <select id="hc-type-filter">
                <option value="">All Types</option>
                @foreach($roomTypes as $type)
                <option value="{{ $type['code'] }}">{{ $type['name'] }}</option>
                @endforeach
            </select>
            <button class="hc-btn" id="hc-prev-week">« Week</button>
            <button class="hc-nav-btn" id="hc-prev">&#8249;</button>
            <input type="date" id="hc-date-input" />
            <button class="hc-nav-btn" id="hc-next">&#8250;</button>
            <button class="hc-btn" id="hc-next-week">Week »</button>
            <span class="hc-spacer"></span>
            <a href="{{ route('filament.admin.resources.reservations.create') }}"
                class="hc-btn primary" id="hc-new-res-btn">
                ＋ &nbsp;New Reservation
            </a>
        </div>
        <div class="hc-scroll-wrap">
            <div class="hc-grid" id="hc-grid"></div>
        </div>
        <div class="hc-legend">
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#22c55e"></div> Clean
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#ef4444"></div> Dirty
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#facc15"></div> Maintenance
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#6b7280"></div> OOO
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#f87171"></div> Occupied
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#34d399"></div> Advance Paid
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#60a5fa"></div> Partial
            </div>
        </div>
    </div>

    {{-- ── BOOKING DETAIL POPOVER ── --}}
    <div class="hc-popover" id="hc-popover">
        <div class="pop-head">
            <div class="pop-res-bar" id="pop-res-id">#---</div>
            <div class="pop-ref">
                <div class="pop-ref-lbl">Ref. Id</div>
                <div class="pop-ref-val" id="pop-ref-val">---</div>
            </div>
            <span class="pop-edit" id="pop-edit">EDIT</span>
            <button class="pop-close" id="pop-close">✕</button>
        </div>
        <div class="pop-body">
            <div class="pop-dates">
                <div class="pop-date">
                    <div class="d" id="pop-cin-d">--</div>
                    <div class="m" id="pop-cin-m">---</div>
                </div>
                <div class="pop-nights">
                    <span class="dots">·······</span>
                    <span class="n" id="pop-nights">1</span>
                    <span class="dots">·······</span>
                </div>
                <div class="pop-date">
                    <div class="d" id="pop-cout-d">--</div>
                    <div class="m" id="pop-cout-m">---</div>
                </div>
            </div>
            <div class="pop-room" id="pop-room">Room ---</div>
            <div class="pop-divider"></div>
            <div class="pop-name" id="pop-name">Guest</div>
            <div class="pop-email" id="pop-email"></div>
            <div class="pop-badges">
                <span class="pop-badge-status" id="pop-status">CONFIRMED</span>
                <span class="pop-badge-source" id="pop-source">Direct</span>

            </div>
            <div class="pop-divider"></div>
            <div class="pop-billing">
                <div>
                    <div class="pop-bill-lbl">TOTAL BILL</div>
                    <div class="pop-bill-amt" id="pop-total">$0.00</div>
                </div>
                <div style="text-align:right">
                    <div class="pop-bill-lbl">OUTSTANDING</div>
                    <div class="pop-bill-amt red" id="pop-outstanding">$0.00</div>
                </div>
            </div>
            <div class="pop-actions">
                <button class="pop-action" id="pop-view-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    View Detail
                </button>
                <button id="pop-checkin-room" class="pop-action btn-green">Check-in Room</button>
                <button id="pop-checkout-room" class="pop-action btn-red">Check-out Room</button>

                <button id="pop-group-checkin" class="pop-action btn-blue">Group Check-in</button>
                <button id="pop-group-checkout" class="pop-action btn-dark">Group Check-out</button>
                <button class="pop-action">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <path d="M2 10h20" />
                    </svg>Payment
                </button>
                <!-- <button class="pop-action">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>Room
                </button>
                <button class="pop-action">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4" />
                    </svg>Key Card
                </button> -->
            </div>
            <button class="pop-cancel" id="pop-cancel-btn">Cancel Booking</button>
        </div>
    </div>

    {{-- Reservation creation is handled by ReservationResource (/filament/reservations/create) --}}

    <div class="hc-toast" id="hc-toast"></div>
    @push('scripts')
    <script>
        (function() {
            'use strict';

            /* ════════════════════════════════════════════════════════
               LIVEWIRE BRIDGE
               Calls a method on the Livewire component that owns this
               page. Works with Livewire 3 (used by Filament v3).
            ════════════════════════════════════════════════════════ */
            async function lwCall(method, ...args) {
                // window.__livewireCalendarId is set inline in the blade above
                const id = window.__livewireCalendarId;
                if (!id) throw new Error('Livewire component ID not set');

                // Livewire 3 API
                if (window.Livewire && typeof window.Livewire.find === 'function') {
                    const component = window.Livewire.find(id);
                    if (component && typeof component.call === 'function') {
                        return await component.call(method, ...args);
                    }
                }

                // Fallback: Livewire 2 API
                if (window.livewire && typeof window.livewire.find === 'function') {
                    const component = window.livewire.find(id);
                    if (component) return await component.call(method, ...args);
                }

                throw new Error('Livewire component not found. Make sure Livewire is loaded.');
            }

            /* ════════════════════════════════════════════════════════
               CONSTANTS & STATE
            ════════════════════════════════════════════════════════ */
            const ROOM_TYPES = @json($groupedRooms);
            let BOOKINGS = @json($reservations ?? []);
            const STATUS_OPTIONS = [{
                    key: 'clean',
                    label: 'Mark as Clean',
                    color: '#22c55e'
                },
                {
                    key: 'dirty',
                    label: 'Mark as Dirty',
                    color: '#ef4444'
                },
                {
                    key: 'ooo',
                    label: 'Mark as OOO',
                    color: '#6b7280'
                },
                {
                    key: 'complaint',
                    label: 'Mark as Guest Complaint',
                    color: '#f97316'
                },
                {
                    key: 'sanitised',
                    label: 'Mark as Sanitised',
                    color: '#06b6d4'
                },
                {
                    key: 'vip',
                    label: 'Mark as VIP Guest',
                    color: '#8b5cf6'
                },
                {
                    key: 'inspect',
                    label: 'Mark as To be Inspected',
                    color: '#f59e0b'
                },
                {
                    key: 'discrepancy',
                    label: 'Mark as Discrepancy',
                    color: '#ec4899'
                },
                {
                    key: 'mnt',
                    label: 'Mark as Maintenance',
                    color: '#facc15'
                },
            ];

            const NUM_DAYS = 30;
            const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            let startDate = new Date();
            startDate.setHours(0, 0, 0, 0);

            /* ════════════════════════════════════════════════════════
               HELPERS
            ════════════════════════════════════════════════════════ */
            function addDays(d, n) {
                const x = new Date(d);
                x.setDate(x.getDate() + n);
                return x;
            }

            function fmtISO(d) {
                return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
            }

            function pad2(n) {
                return String(n).padStart(2, '0');
            }

            function mkEl(tag, cls) {
                const e = document.createElement(tag);
                if (cls) e.className = cls;
                return e;
            }

            let toastTimer;

            function showToast(msg, type = 'success') {
                const t = document.getElementById('hc-toast');
                t.textContent = msg;
                t.className = `hc-toast show ${type}`;
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => {
                    t.className = 'hc-toast';
                }, 3800);
            }

            function statusToKey(s) {
                const map = {
                    clean: 'clean',
                    vacant: 'clean',
                    dirty: 'dirty',
                    mnt: 'mnt',
                    maintenance: 'mnt',
                    ooo: 'ooo',
                    complaint: 'complaint',
                    sanitised: 'sanitised',
                    vip: 'vip',
                    inspect: 'inspect',
                    discrepancy: 'discrepancy',
                };
                return map[(s || '').toLowerCase()] || 'clean';
            }

            /* ════════════════════════════════════════════════════════
               BUILD GRID
            ════════════════════════════════════════════════════════ */
            function buildGrid() {
                const grid = document.getElementById('hc-grid');
                grid.innerHTML = '';
                grid.style.setProperty('--hc-days', NUM_DAYS);

                const today = fmtISO(new Date());
                const days = Array.from({
                    length: NUM_DAYS
                }, (_, i) => addDays(startDate, i));

                /* Header row */
                const rnLbl = mkEl('div', 'hc-head-cell room-label');
                rnLbl.textContent = 'Room No';
                grid.appendChild(rnLbl);

                days.forEach(d => {
                    const iso = fmtISO(d);
                    const el = mkEl('div', 'hc-head-cell' + (iso === today ? ' today' : ''));
                    el.innerHTML = `<span class="day-num">${d.getDate()}</span>
                    <span class="day-month">${MONTH_SHORT[d.getMonth()]}</span>
                    <span class="day-name">${DAY_NAMES[d.getDay()]}</span>`;
                    grid.appendChild(el);
                });

                /* Vacant row */
                const baseCount = "{{$totalVacant}}"; // Total clean/vacant rooms
                const vacLbl = mkEl('div', 'hc-vacant-cell label');
                vacLbl.innerHTML = '<span>Vacant</span><span>▼</span>';
                grid.appendChild(vacLbl);

                // Populate the row
                days.forEach(d => {
                    const iso = fmtISO(d); // This produces YYYY-MM-DD
                    const vc = mkEl('div', 'hc-vacant-cell' + (iso === today ? ' today-col' : ''));

                    // Updated Logic: Count the room as occupied if:
                    // 1. The calendar date is between check-in and check-out
                    // 2. OR the calendar date exactly matches check-in (handles same-day bookings)
                    const occupiedToday = BOOKINGS.filter(b => {
                        const isSameDay = b.check_in === b.check_out;
                        if (isSameDay) {
                            return iso === b.check_in; // Count on that single day
                        }
                        return iso >= b.check_in && iso < b.check_out;
                    }).length;

                    vc.textContent = Math.max(0, baseCount - occupiedToday);
                    grid.appendChild(vc);
                });
                console.log('ROOM_TYPES:', ROOM_TYPES);
                console.log('Bookings:', BOOKINGS);

                /* Room type groups */
                ROOM_TYPES.forEach(rt => {
                    const th = mkEl('div', 'hc-type-cell label');
                    th.innerHTML = `<b>${rt.code}</b>`;
                    grid.appendChild(th);

                    days.forEach(d => {
                        const iso = fmtISO(d);
                        const tc = mkEl('div', 'hc-type-cell count-cell');

                        const typeOccupied = BOOKINGS.filter(b => {
                            const matchesType = b.room_type_id === rt.id;
                            const isSameDay = b.check_in === b.check_out;
                            console.log('b.room_type_id:', b.room_type_id, 'rt.id:', rt.id);
                            console.log(`Evaluating booking ${b.id} for date ${iso}: matchesType=${matchesType}, check_in=${b.check_in}, check_out=${b.check_out}, isSameDay=${isSameDay}`);
                            if (matchesType) {
                                console.log(`Checking booking ${b.id} for room type ${rt.code} on date ${iso}: check_in=${b.check_in}, check_out=${b.check_out}, isSameDay=${isSameDay}`);
                                return isSameDay ? (iso === b.check_in) : (iso >= b.check_in && iso < b.check_out);
                            }
                            return false;
                        }).length;
                        console.log(`Date: ${iso}, Type: ${rt.code}, totalRooms: ${rt.totalRooms}, Occupied: ${typeOccupied}`);
                        tc.textContent = Math.max(0, rt.totalRooms - typeOccupied);
                        grid.appendChild(tc);
                    });

                    rt.rooms.forEach(room => {
                        const roomNo = room.room_number;
                        const sKey = statusToKey(room.status);

                        /* ── Room label cell ── */
                        const rl = mkEl('div', `hc-room-label s-${sKey}`);
                        rl.dataset.room = roomNo;
                        rl.dataset.status = sKey;

                        const nameSpan = mkEl('span');
                        nameSpan.style.cssText = 'flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap';
                        nameSpan.textContent = roomNo;
                        rl.appendChild(nameSpan);

                        /* Brush icon */
                        const brushWrap = mkEl('div', 'hc-brush-wrap');
                        const brushBtn = mkEl('button', 'hc-brush-btn');
                        brushBtn.title = 'Set housekeeping status';
                        brushBtn.type = 'button';
                        brushBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18.37 2.63 14 7l-1.59-1.59a2 2 0 0 0-2.82 0L8 7l9 9
                                 1.59-1.59a2 2 0 0 0 0-2.82L17 10l4.37-4.37a2.12 2.12 0 1 0-3-3Z"/>
                        <path d="M9 8c-2 3-4 3.5-7 4l8 10c2-1 6-5 6-7"/>
                    </svg>`;

                        /* Status dropdown */
                        const dd = mkEl('div', 'hc-status-dd');

                        STATUS_OPTIONS.forEach(opt => {
                            const item = mkEl('div', 'hc-status-dd-item' + (sKey === opt.key ? ' active' : ''));
                            item.innerHTML = `<span class="hc-s-dot" style="background:${opt.color}"></span>
                            ${opt.label}
                            ${sKey === opt.key ? '<span class="dd-tick">✓</span>' : ''}`;

                            item.addEventListener('click', async (e) => {
                                e.stopPropagation();
                                const prev = rl.dataset.status;
                                if (prev === opt.key) {
                                    dd.classList.remove('open');
                                    return;
                                }

                                /* Optimistic update */
                                // rl.className = `hc-room-label s-${opt.key}`;
                                // rl.dataset.status = opt.key;
                                // dd.querySelectorAll('.hc-status-dd-item').forEach(i => {
                                //     i.classList.remove('active');
                                //     i.querySelector('.dd-tick')?.remove();
                                // });
                                // item.classList.add('active', 'saving');
                                // const tick = mkEl('span', 'dd-tick');
                                // tick.textContent = '✓';
                                // item.appendChild(tick);
                                // dd.classList.remove('open');
                                item.classList.add('saving');
                                try {
                                    const res = await lwCall('updateRoomStatus', roomNo, opt.key);
                                    if (res && res.success) {
                                        // 2. UPDATE LOCAL DATA: Find the room in our JS object and change its status
                                        ROOM_TYPES.forEach(rt => {
                                            const room = rt.rooms.find(r => String(r.room_number) === String(roomNo));
                                            if (room) {
                                                room.status = opt.key;
                                            }
                                        });

                                        // 3. RE-RENDER: Redraw the grid with the new data
                                        buildGrid();

                                        showToast(`Room ${roomNo} updated to ${opt.label}`, 'success');
                                    } else {
                                        throw new Error(res.message || 'Update failed');
                                    }
                                } catch (err) {
                                    /* Revert */
                                    rl.className = `hc-room-label s-${prev}`;
                                    rl.dataset.status = prev;
                                    showToast('Failed to update status: ' + err.message, 'error');
                                } finally {
                                    item.classList.remove('saving');
                                }
                            });

                            dd.appendChild(item);
                        });

                        brushBtn.addEventListener('click', e => {
                            e.stopPropagation();
                            /* Close all other dropdowns */
                            document.querySelectorAll('.hc-status-dd.open').forEach(x => {
                                if (x !== dd) x.classList.remove('open');
                            });
                            dd.classList.toggle('open');
                        });

                        brushWrap.appendChild(brushBtn);
                        brushWrap.appendChild(dd);
                        rl.appendChild(brushWrap);
                        grid.appendChild(rl);

                        /* ── Day cells ── */
                        days.forEach(d => {
                            const iso = fmtISO(d);
                            const dc = mkEl('div', 'hc-day-cell' + (iso === today ? ' today-col' : ''));
                            console.log('/BOOKINGS', BOOKINGS)
                            /* Booking chips */
                            BOOKINGS.forEach(b => {
                                // Ensure room_no comes from reservation_room_details
                                const bookingRoom = String(b.room_no || '').trim();
                                const gridRoom = String(roomNo || '').trim();
                                console.log(`Checking booking ${b.id} for room ${bookingRoom} against grid room ${gridRoom} on date ${iso}: check_in=${b.check_in}`);
                                // ISO check for the start date
                                if (bookingRoom === gridRoom && b.check_in === iso) {
                                    const chip = mkEl('div', `hc-booking ${b.booking_type || 'occupied'}`);

                                    // Use the detail_id for popover context
                                    chip.dataset.detailId = b.detail_id;

                                    const nights = parseInt(b.nights) || 1;
                                    chip.style.width = ((nights * 110) - 4) + 'px';

                                    const gName = [b.first_name, b.last_name].filter(Boolean).join(' ');
                                    chip.innerHTML = (gName || 'Guest') + (b.verified ? ' <span class="chk">✓</span>' : '');

                                    chip.addEventListener('click', e => {
                                        e.stopPropagation();
                                        openPopover(b, e);
                                    });
                                    dc.appendChild(chip);
                                }
                            });

                            /* Empty cell click → new reservation */
                            dc.addEventListener('click', () => openModal({
                                roomNo,
                                date: iso
                            }));
                            grid.appendChild(dc);
                        });
                    });
                });
            }

            /* ════════════════════════════════════════════════════════
               BOOKING DETAIL POPOVER
            ════════════════════════════════════════════════════════ */
            const popover = document.getElementById('hc-popover');

            function openPopover(b, event) {
                const cin = new Date(b.check_in + 'T00:00:00');
                const nights = b.nights || 1;
                const cout = addDays(cin, nights);
                const total = (parseFloat(b.rate) || 0) * nights;
                const outst = b.outstanding != null ? parseFloat(b.outstanding) : total;

                document.getElementById('pop-res-id').textContent = b.reservation_id || ('#' + b.id) || '#---';
                document.getElementById('pop-ref-val').textContent = b.ref_id || b.id || '---';
                document.getElementById('pop-cin-d').textContent = pad2(cin.getDate());
                document.getElementById('pop-cin-m').textContent = MONTH_SHORT[cin.getMonth()].toUpperCase() + ' ' + cin.getFullYear();
                document.getElementById('pop-cout-d').textContent = pad2(cout.getDate());
                document.getElementById('pop-cout-m').textContent = MONTH_SHORT[cout.getMonth()].toUpperCase() + ' ' + cout.getFullYear();
                document.getElementById('pop-nights').textContent = nights;
                document.getElementById('pop-room').textContent = 'Room ' + b.room_no + (b.room_type ? ' · ' + b.room_type : '');
                document.getElementById('pop-name').textContent = [b.title, b.first_name, b.last_name].filter(Boolean).join(' ') || 'Guest';
                document.getElementById('pop-email').textContent = b.email || '';
                document.getElementById('pop-status').textContent = (b.status || 'confirmed').toUpperCase();
                document.getElementById('pop-source').textContent = b.source || 'Direct';
                document.getElementById('pop-total').textContent = '$' + total.toFixed(2);
                document.getElementById('pop-outstanding').textContent = '$' + outst.toFixed(2);

                /* Position near cursor, keep inside viewport */
                const pw = 340,
                    ph = 490;
                const left = Math.min(event.clientX + 14, window.innerWidth - pw - 14);
                const top = Math.min(event.clientY + 14, window.innerHeight - ph - 14);
                popover.style.left = left + 'px';
                popover.style.top = top + 'px';
                popover.dataset.bookingId = String(b.id || '');

                const checkInRoomBtn = document.getElementById('pop-checkin-room');
                const checkOutRoomBtn = document.getElementById('pop-checkout-room');
                const groupCheckInBtn = document.getElementById('pop-group-checkin');
                const groupCheckOutBtn = document.getElementById('pop-group-checkout');
                // Show Check-in if status is confirmed/tentative; show Check-out if already checked_in
                // 1. Partial Logic (Specific to the room clicked)
                if (b.status === 'checked_in') {
                    checkInRoomBtn.style.display = 'none';
                    checkOutRoomBtn.style.display = 'flex';
                } else {
                    checkInRoomBtn.style.display = 'flex';
                    checkOutRoomBtn.style.display = 'none';
                }

                // 2. Group Logic (Affects all rooms in the ID)
                // Only show Group Check-out if at least one room is checked in
                if (b.status === 'checked_in') {
                    groupCheckInBtn.style.display = 'none';
                    groupCheckOutBtn.style.display = 'flex';
                } else {
                    groupCheckInBtn.style.display = 'flex';
                    groupCheckOutBtn.style.display = 'none';
                }

                // Bind Group Checkout
                groupCheckOutBtn.onclick = async () => {
                    if (confirm("Check-out all rooms for this reservation?")) {
                        const res = await lwCall('updateReservationStatus', b.id, 'checked_out');
                        if (res.success) window.location.reload();
                    }
                };

                // Bind Partial Checkout
                checkOutRoomBtn.onclick = async () => {
                    const res = await lwCall('updateRoomStatusInBooking', b.detail_id, 'checked_out');
                    if (res.success) window.location.reload();
                };
                popover.dataset.bookingId = b.id;
                // 1. Show Room and Meal Plan
                const roomInfo = `Room ${b.room_no} · ${b.room_type || ''} (${b.meal_plan || 'EP'})`;
                document.getElementById('pop-room').textContent = roomInfo;

                // 2. Set Redirects for Edit and View
                document.getElementById('pop-edit').onclick = () => {
                    window.location.href = `/admin/reservations/${b.id}/edit`;
                };

                document.getElementById('pop-view-btn').onclick = () => {
                    window.location.href = `/admin/reservations/${b.id}`;
                };

                popover.dataset.bookingId = b.id;
                popover.dataset.detailId = b.detail_id;
                popover.classList.add('open');
            }
            // Event Listeners for the new actions
            // document.getElementById('pop-checkin-btn').addEventListener('click', () => handleStatusChange('checked_in'));
            // document.getElementById('pop-checkout-btn').addEventListener('click', () => handleStatusChange('checked_out'));

            document.getElementById('pop-close').addEventListener('click', () => popover.classList.remove('open'));

            document.addEventListener('click', e => {
                if (popover.classList.contains('open') && !popover.contains(e.target)) {
                    popover.classList.remove('open');
                }
            });

            // document.getElementById('pop-edit').addEventListener('click', () => {
            //     const id = popover.dataset.bookingId;
            //     if (id) {
            //         window.location.href = `/filament/reservations/reservations/${id}/edit`;
            //     }
            // });

            document.getElementById('pop-edit').onclick = () => {
                // Navigates to the standard Filament Edit page
                window.location.href = `/admin/reservations/${b.id}/edit`;
            };

            document.getElementById('pop-view-btn').onclick = () => {
                // Navigates to the standard Filament View/Detail page
                window.location.href = `/admin/reservations/${b.id}`;
            };
            // Partial Check-in: Only updates the room currently clicked
            // document.getElementById('pop-checkin-btn').onclick = async () => {
            //     const res = await lwCall('updateRoomStatusInBooking', b.room_stay_id, 'checked_in');
            //     if (res.success) window.location.reload();
            // };

            // Group Check-in: Updates all rooms linked to this reservation
            // document.getElementById('pop-group-checkin-btn').onclick = async () => {
            //     const res = await lwCall('updateReservationStatus', b.id, 'checked_in');
            //     if (res.success) window.location.reload();
            // };
            document.getElementById('pop-cancel-btn').addEventListener('click', async () => {
                const id = parseInt(popover.dataset.bookingId);
                if (!id) return;
                if (!confirm('Are you sure you want to cancel this booking?')) return;

                const btn = document.getElementById('pop-cancel-btn');
                btn.disabled = true;
                btn.textContent = 'Cancelling…';

                try {
                    const res = await lwCall('cancelReservation', id);
                    if (res && res.success) {
                        BOOKINGS = BOOKINGS.filter(b => b.id !== id);
                        buildGrid();
                        popover.classList.remove('open');
                        showToast('Booking cancelled', 'success');
                    } else {
                        throw new Error((res && res.message) || 'Cancel failed');
                    }
                } catch (err) {
                    showToast('Failed to cancel: ' + err.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'Cancel Booking';
                }
            });

            /* Empty cell click → redirect to ReservationResource create page
               with room_no and check_in pre-filled as query params               */
            function openModal(ctx = {}) {
                const hotelId = document.getElementById('hc-property-select').value;
                const params = new URLSearchParams();

                params.set('hotel_id', hotelId);
                if (ctx.roomNo) params.set('room_no', ctx.roomNo);
                if (ctx.date) params.set('check_in', ctx.date);

                const baseUrl = "{{ route('filament.admin.resources.reservations.create') }}";
                window.location.href = `${baseUrl}?${params.toString()}`;
            }

            async function handleStatusChange(newStatus) {
                const id = popover.dataset.bookingId;
                if (!id || !confirm(`Are you sure you want to ${newStatus.replace('_', ' ')}?`)) return;

                try {
                    const res = await lwCall('updateReservationStatus', id, newStatus);
                    if (res && res.success) {
                        showToast(`Guest ${newStatus.replace('_', ' ')} successfully`, 'success');
                        window.location.reload(); // Refresh to update grid bars and room statuses
                    } else {
                        throw new Error(res.message);
                    }
                } catch (err) {
                    showToast('Error: ' + err.message, 'error');
                }
            }

            // "New Reservation" button is now an <a> tag — no JS needed for it

            /* ════════════════════════════════════════════════════════
               NAVIGATION
            ════════════════════════════════════════════════════════ */
            const dateInput = document.getElementById('hc-date-input');
            dateInput.value = fmtISO(startDate);

            dateInput.addEventListener('change', () => {
                startDate = new Date(dateInput.value + 'T00:00:00');
                buildGrid();
            });
            document.getElementById('hc-prev').addEventListener('click', () => {
                startDate = addDays(startDate, -1);
                dateInput.value = fmtISO(startDate);
                buildGrid();
            });
            document.getElementById('hc-next').addEventListener('click', () => {
                startDate = addDays(startDate, 1);
                dateInput.value = fmtISO(startDate);
                buildGrid();
            });

            /* Close all dropdowns on outside click */
            document.addEventListener('click', () => {
                document.querySelectorAll('.hc-status-dd.open').forEach(d => d.classList.remove('open'));
            });

            /* ════════════════════════════════════════════════════════
               INIT — wait for Livewire to be ready before building
            ════════════════════════════════════════════════════════ */
            function initCalendar() {
                // Re-fetch the latest data from the window if Livewire updated it
                const rawRooms = document.querySelector('[data-grouped-rooms]');
                if (rawRooms) {
                    // Update local state before building
                    ROOM_TYPES = JSON.parse(rawRooms.getAttribute('data-grouped-rooms'));
                }
                buildGrid();
            }
            if (window.Livewire) {
                // Livewire 3: fires after all components are initialized
                window.Livewire.hook('morph.updated', ({
                    component
                }) => {
                    if (component.id === window.__livewireCalendarId) {
                        buildGrid();
                    }
                });
                buildGrid();
            } else {
                // Fallback: wait for DOMContentLoaded + a short tick
                document.addEventListener('livewire:init', buildGrid);
                document.addEventListener('livewire:initialized', buildGrid);
                // Ultimate fallback
                setTimeout(buildGrid, 100);
            }

            document.getElementById('hc-prev-week').addEventListener('click', () => {
                startDate = addDays(startDate, -7);
                dateInput.value = fmtISO(startDate);
                buildGrid();
            });

            document.getElementById('hc-next-week').addEventListener('click', () => {
                startDate = addDays(startDate, 7);
                dateInput.value = fmtISO(startDate);
                buildGrid();
            });

        })();
    </script>
    @endpush

</x-filament::page>