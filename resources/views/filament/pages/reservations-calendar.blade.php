<x-filament::page>

    <style>
        /* ── Google Font ── */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        /* ── Reset & Base ── */
        #hotel-calendar-wrap * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        #hotel-calendar-wrap {
            --primary: #45556c;
            --primary-hover: #45556c;
            --bg: #f4f6fa;
            --surface: #ffffff;
            --border: #e2e6ef;
            --text: #1a1f36;
            --muted: #6b7a99;
            --today-bg: #45556c;
            --today-fg: #ffffff;
            --occupied: #f87171;
            --mng: #4ade80;
            --mnt: #facc15;
            --advance: #34d399;
            --partial: #60a5fa;
            --vacant-bg: #f0f4ff;
            --room-w: 76px;
            --col-w: 110px;
            --row-h: 34px;
            --header-h: 52px;
            font-size: 13px;
            color: var(--text);
            background: var(--bg);
            padding: 16px;
            border-radius: 12px;
            --vacant: #22c55e;
            /* Green */
            --dirty: #ef4444;
            /* Red */
            --mnt: #facc15;
            /* Yellow/Maintenance */
            --check-in: #3b82f6;
            /* Blue (Optional extra) */
            --check-out: #a855f7;
            /* Purple (Optional extra) */
        }

        /* ── Top toolbar ── */
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

        .hc-toolbar select:focus,
        .hc-toolbar input[type="date"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 110, 245, .12);
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
            transition: background .15s, border-color .15s;
        }

        .hc-btn:hover {
            background: #f0f4ff;
            border-color: var(--primary);
        }

        .hc-btn.primary {
            background: #45556c;
            color: #fff;
            border-color: #45556c;
        }

        .hc-btn.primary:hover {
            background: var(--primary-hover);
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

        /* ── Calendar wrapper ── */
        .hc-scroll-wrap {
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .hc-grid {
            display: grid;
            /* room-col + N day columns */
            grid-template-columns: var(--room-w) repeat(var(--hc-days, 9), var(--col-w));
            min-width: max-content;
        }

        /* ── Sticky header row ── */
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

        /* ── Vacant row ── */
        .hc-vacant-row {
            display: contents;
        }

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
            letter-spacing: .05em;
        }

        /* ── Room-type group header ── */
        .hc-type-header {
            display: contents;
        }

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
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hc-type-cell.count-cell {
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
            color: #45556c;
        }

        /* ── Room rows ── */
        .hc-room-row {
            display: contents;
        }

        .hc-room-label {
            height: var(--row-h);
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text);
            border-right: 2px solid var(--border);
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            position: sticky;
            left: 0;
            z-index: 5;
        }

        .hc-room-label .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            flex-shrink: 0;
        }

        .hc-room-label .status-dot.mnt {
            background: var(--mnt);
        }

        .hc-room-label .status-dot.vacant {
            background: transparent;
            border: 1.5px solid var(--border);
        }

        .hc-day-cell {
            height: var(--row-h);
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            position: relative;
            cursor: pointer;
            transition: background .1s;
            overflow: hidden;
        }

        .hc-day-cell:hover {
            background: #eef3ff;
        }

        .hc-day-cell.today-col {
            background: #f5f8ff;
        }

        /* ── Booking chip ── */
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
        }

        .hc-booking:hover {
            filter: brightness(1.08);
        }

        .hc-booking.occupied {
            background: var(--occupied);
        }

        .hc-booking.mng {
            background: var(--mng);
            color: #fff;
        }

        .hc-booking .check-icon {
            width: 14px;
            height: 14px;
            background: rgba(255, 255, 255, .35);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            font-size: 9px;
        }

        /* ── Legend ── */
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

        /* ════════════════════════════════════════
       QUICK RESERVATION MODAL
    ════════════════════════════════════════ */
        .hc-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 20, 50, .45);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .hc-modal-overlay.open {
            display: flex;
        }

        .hc-modal {
            background: #ffffff !important;
            /* background: var(--surface); */
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .22);
            width: 760px;
            max-width: 96vw;
            max-height: 92vh;
            overflow-y: auto;
            animation: modalIn .2s ease;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(.96) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .hc-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            background: #f8f9fd;
            border-radius: 14px 14px 0 0;
        }

        .hc-modal-hotel {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }

        .hc-modal-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .hc-modal-close {
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
            transition: background .15s;
        }

        .hc-modal-close:hover {
            background: #fee2e2;
            color: #ef4444;
        }

        .hc-modal-body {
            padding: 18px 20px;
        }

        /* form grid */
        .hc-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 20px;
        }

        .hc-form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .hc-form-group.span2 {
            grid-column: span 2;
        }

        .hc-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .hc-label .req {
            color: #ef4444;
            margin-left: 2px;
        }

        .hc-input,
        .hc-select {
            padding: 8px 11px;
            border: 1px solid var(--border);
            border-radius: 7px;
            font-size: 13px;
            color: var(--text);
            background: var(--surface);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
            width: 100%;
        }

        .hc-input:focus,
        .hc-select:focus {
            border-color: #45556c;
            box-shadow: 0 0 0 3px rgba(30, 110, 245, .12);
        }

        .hc-input-group {
            display: flex;
            gap: 6px;
        }

        .hc-input-group .hc-input {
            flex: 1;
        }

        .hc-input-group .hc-input.sm {
            width: 80px;
            flex: none;
        }

        .hc-input-icon {
            position: relative;
        }

        .hc-input-icon .hc-input {
            padding-right: 32px;
        }

        .hc-input-icon .icon {
            position: absolute;
            right: 9px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 14px;
            pointer-events: none;
        }

        /* Divider */
        .hc-divider {
            height: 1px;
            background: var(--border);
            margin: 14px 0;
        }

        /* Guest table */
        .hc-guest-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            font-size: 12px;
        }

        .hc-guest-table th {
            background: #f8f9fd;
            padding: 8px 10px;
            font-weight: 600;
            text-align: left;
            border-bottom: 1px solid var(--border);
            color: var(--muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .hc-guest-table td {
            padding: 6px 10px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .hc-guest-table tr:last-child td {
            border-bottom: none;
        }

        .hc-guest-table .hc-input {
            padding: 5px 8px;
            font-size: 12px;
        }

        .hc-guest-table .hc-select {
            padding: 5px 8px;
            font-size: 12px;
        }

        .hc-guest-actions {
            display: flex;
            gap: 4px;
        }

        .hc-icon-btn {
            width: 26px;
            height: 26px;
            border: 1px solid var(--border);
            border-radius: 5px;
            background: var(--surface);
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            transition: background .15s;
        }

        .hc-icon-btn:hover {
            background: #f0f4ff;
            color: var(--primary);
        }

        /* Rate row */
        .hc-rate-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .hc-rate-row .hc-input {
            width: 90px;
        }

        .hc-rate-row .hc-input.lg {
            width: 130px;
        }

        /* Plan badge */
        .hc-plan-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .hc-plan-badge .remove {
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            color: #b45309;
        }

        .hc-plan-badge .remove:hover {
            color: #ef4444;
        }

        /* Modal footer */
        .hc-modal-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 20px;
            border-top: 1px solid var(--border);
            background: #f8f9fd;
            border-radius: 0 0 14px 14px;
        }

        .hc-modal-footer .hc-spacer {
            flex: 1;
        }

        .hc-tab-btn {
            padding: 7px 14px;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--surface);
            color: var(--text);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }

        .hc-tab-btn:hover {
            background: #f0f4ff;
            border-color: var(--primary);
        }

        .hc-tab-btn.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .hc-save-btn {
            padding: 8px 22px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s;
        }

        .hc-save-btn:hover {
            background: var(--primary-hover);
        }

        /* Update status dot colors */
        .hc-room-label .status-dot.vacant {
            background: var(--vacant);
            border: none;
        }

        .hc-room-label .status-dot.dirty {
            background: var(--dirty);
        }

        .hc-room-label .status-dot.mnt {
            background: var(--mnt);
        }

        .hc-room-label .status-dot.check-in {
            background: var(--check-in);
        }

        .hc-room-label .status-dot.check-out {
            background: var(--check-out);
        }

        /* ── Responsive ── */
        @media (max-width: 640px) {
            .hc-form-grid {
                grid-template-columns: 1fr;
            }

            .hc-form-group.span2 {
                grid-column: span 1;
            }
        }
    </style>

    <div id="hotel-calendar-wrap">

        {{-- ── TOP TOOLBAR ── --}}
        <div class="hc-toolbar">
            <select id="hc-property-select">
                @foreach($hotels as $hotel)
                <option>{{$hotel['name']}}</option>
                @endforeach
            </select>
            <select id="hc-type-filter">
                <option>All Types</option>
                @foreach($roomTypes as $type)
                <option>{{$type['name']}}</option>
                @endforeach
            </select>
            <button class="hc-nav-btn" id="hc-prev">&#8249;</button>
            <input type="date" id="hc-date-input" />
            <button class="hc-nav-btn" id="hc-next">&#8250;</button>
            <span class="hc-spacer"></span>
            <button class="hc-btn primary" id="hc-new-reservation-btn">
                ＋ &nbsp;New Reservation
            </button>
        </div>

        {{-- ── CALENDAR GRID ── --}}
        <div class="hc-scroll-wrap">
            <div class="hc-grid" id="hc-grid"></div>
        </div>

        {{-- ── LEGEND ── --}}
        <div class="hc-legend">
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--vacant)"></div> Clean / Vacant
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--dirty)"></div> Dirty
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--mnt)"></div> Maintenance
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--occupied)"></div> Occupied
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:#a78bfa"></div> Guest Block
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--mng)"></div> MNG Block
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--mnt)"></div> MNT Block
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--advance)"></div> Advance Paid
            </div>
            <div class="hc-legend-item">
                <div class="hc-legend-dot" style="background:var(--partial)"></div> Partial Booking Advance
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
     QUICK RESERVATION MODAL
════════════════════════════════════════ --}}
    <div class="hc-modal-overlay" id="hc-modal-overlay">
        <div class="hc-modal" id="hc-modal">

            {{-- Header --}}
            <div class="hc-modal-header">
                <div>
                    <div class="hc-modal-hotel">Grand Hotel</div>
                    <div class="hc-modal-title">Quick Reservation</div>
                </div>
                <button class="hc-modal-close" id="hc-modal-close">✕</button>
            </div>

            {{-- Body --}}
            <div class="hc-modal-body">
                <div class="hc-form-grid">

                    {{-- Room Type --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Room Type <span class="req">*</span></label>
                        <select class="hc-select" id="qr-room-type">
                            @foreach($roomTypes as $type)
                            <option value="{{$type['code']}}">{{$type['name']}}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Status <span class="req">*</span></label>
                        <select class="hc-select" id="qr-status">
                            <option>Confirmed</option>
                            <option>Tentative</option>
                            <option>Waitlist</option>
                        </select>
                    </div>

                    {{-- Room No --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Room No</label>
                        <div class="hc-input-icon">
                            <input type="text" class="hc-input" id="qr-room-no" placeholder="Search…">
                            <span class="icon">🔍</span>
                        </div>
                    </div>

                    {{-- Rooms --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Rooms <span class="req">*</span></label>
                        <div class="hc-input-group">
                            <input type="number" class="hc-input sm" id="qr-rooms" value="1" min="1">
                            <input type="text" class="hc-input" id="qr-reserve-no" placeholder="Reserve No">
                        </div>
                    </div>

                    {{-- Arrival --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Arrival <span class="req">*</span></label>
                        <div class="hc-input-group">
                            <input type="date" class="hc-input" id="qr-arrival">
                            <input type="time" class="hc-input sm" id="qr-arrival-time" value="12:00">
                        </div>
                    </div>

                    {{-- Nights & Departure --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Nights / Departure <span class="req">*</span></label>
                        <div class="hc-input-group">
                            <input type="number" class="hc-input sm" id="qr-nights" value="1" min="1" placeholder="Nights">
                            <input type="date" class="hc-input" id="qr-departure">
                            <input type="time" class="hc-input sm" id="qr-dep-time" value="12:00">
                        </div>
                    </div>

                    {{-- Pax --}}
                    <div class="hc-form-group">
                        <label class="hc-label">Pax</label>
                        <div class="hc-input-group">
                            <input type="number" class="hc-input" id="qr-adult" value="1" min="1" placeholder="Adult">
                            <input type="number" class="hc-input" id="qr-child" value="0" min="0" placeholder="Child">
                            <input type="number" class="hc-input" id="qr-infant" value="0" min="0" placeholder="Infant">
                        </div>
                    </div>

                    <div></div>{{-- spacer --}}

                </div>

                {{-- Guest table --}}
                <div class="hc-divider"></div>
                <table class="hc-guest-table">
                    <thead>
                        <tr>
                            <th style="width:50px">#</th>
                            <th style="width:90px">Title</th>
                            <th>Last Name</th>
                            <th>First Name</th>
                            <th style="width:100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="qr-guest-body">
                        <tr>
                            <td>Gst 1</td>
                            <td>
                                <select class="hc-select">
                                    <option>Mr.</option>
                                    <option>Mrs.</option>
                                    <option>Ms.</option>
                                    <option>Dr.</option>
                                </select>
                            </td>
                            <td><input type="text" class="hc-input" placeholder="Last name"></td>
                            <td><input type="text" class="hc-input" placeholder="First name"></td>
                            <td>
                                <div class="hc-guest-actions">
                                    <button class="hc-icon-btn" title="Search">🔍</button>
                                    <button class="hc-icon-btn" title="Edit">✏️</button>
                                    <button class="hc-icon-btn" title="Profile">P</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="hc-divider"></div>

                {{-- Travel / Company --}}
                <div class="hc-form-grid">
                    <div class="hc-form-group">
                        <label class="hc-label">Travel Agent</label>
                        <div class="hc-input-icon">
                            <input type="text" class="hc-input" id="qr-travel-agent" placeholder="Search…">
                            <span class="icon">🔍</span>
                        </div>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Group</label>
                        <div class="hc-input-icon">
                            <input type="text" class="hc-input" id="qr-group" placeholder="Search…">
                            <span class="icon">🔍</span>
                        </div>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Company</label>
                        <div class="hc-input-icon">
                            <input type="text" class="hc-input" id="qr-company" placeholder="Search…">
                            <span class="icon">🔍</span>
                        </div>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Bus Source</label>
                        <select class="hc-select" id="qr-bus-source">
                            <option value="">— Select —</option>
                            <option>Direct</option>
                            <option>OTA</option>
                            <option>Corporate</option>
                        </select>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Rate Code <span class="req">*</span></label>
                        <div class="hc-input-icon">
                            <input type="text" class="hc-input" id="qr-rate-code" value="Regular Tariff">
                            <span class="icon">🔍</span>
                        </div>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Booker</label>
                        <div class="hc-input-icon">
                            <input type="text" class="hc-input" id="qr-booker" placeholder="Search…">
                            <span class="icon">🔍</span>
                        </div>
                    </div>
                </div>

                <div class="hc-divider"></div>

                {{-- Rate & Plan --}}
                <div class="hc-form-grid">
                    <div class="hc-form-group span2">
                        <label class="hc-label">Rate <span class="req">*</span></label>
                        <div class="hc-rate-row">
                            <input type="number" class="hc-input" id="qr-rate" value="270.00" step="0.01">
                            <span style="color:var(--muted);font-size:12px">Disc %</span>
                            <input type="number" class="hc-input sm" id="qr-disc-pct" placeholder="0">
                            <span style="color:var(--muted);font-size:12px">Disc Amt</span>
                            <input type="number" class="hc-input sm" id="qr-disc-amt" placeholder="0">
                            <span style="color:var(--muted);font-size:12px">Plan</span>
                            <div class="hc-plan-badge" id="qr-plan-badge">
                                Continental Plan
                                <span class="remove" id="qr-plan-remove">✕</span>
                            </div>
                        </div>
                    </div>
                    <div class="hc-form-group span2">
                        <label class="hc-label">Plan Amount / Ch1</label>
                        <div class="hc-rate-row">
                            <input type="number" class="hc-input" id="qr-plan-amt" value="0.000" step="0.001">
                            <span style="color:var(--muted);font-size:12px">Ch1</span>
                            <input type="number" class="hc-input" id="qr-ch1" value="0.000" step="0.001">
                            <span style="color:var(--muted);font-size:12px">Disc %</span>
                            <input type="number" class="hc-input sm" id="qr-ch1-disc-pct" placeholder="0">
                            <span style="color:var(--muted);font-size:12px">Disc Amt</span>
                            <input type="number" class="hc-input sm" id="qr-ch1-disc-amt" placeholder="0">
                        </div>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Guest Type</label>
                        <select class="hc-select" id="qr-guest-type">
                            <option value="">— Select —</option>
                            <option>FIT</option>
                            <option>Group</option>
                            <option>Corporate</option>
                        </select>
                    </div>
                    <!-- <div class="hc-form-group">
                        <label class="hc-label">Upgrade</label>
                        <select class="hc-select" id="qr-upgrade">
                            <option>None</option>
                            <option>Room Upgrade</option>
                            <option>Suite Upgrade</option>
                        </select>
                    </div>
                    <div class="hc-form-group">
                        <label class="hc-label">Segment</label>
                        <select class="hc-select" id="qr-segment">
                            <option value="">— Select —</option>
                            <option>Leisure</option>
                            <option>Business</option>
                            <option>MICE</option>
                        </select>
                    </div> -->
                </div>

            </div>

            {{-- Footer --}}
            <div class="hc-modal-footer">
                <button class="hc-tab-btn">Routing</button>
                <button class="hc-tab-btn active">Rate Edit</button>
                <button class="hc-tab-btn">Package</button>
                <button class="hc-tab-btn">Others</button>
                <button class="hc-tab-btn">Auto Charge</button>
                <button class="hc-tab-btn">⋮</button>
                <span class="hc-spacer"></span>
                <button class="hc-save-btn" id="qr-save">Save</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // const ROOM_TYPES = [{
            //         code: 'DLR',
            //         label: 'Deluxe Rooms',
            //         totalRooms: 13,
            //         rooms: ['211', '212', '214', '215', '216', '217', '218', '219', '220', '501', '502', '504', '505']
            //     },
            //     {
            //         code: 'EXE',
            //         label: 'Executive Rooms',
            //         totalRooms: 43,
            //         rooms: ['201', '202', '203', '208', '209', '210', '311', '312', '314', '315', '316', '317', '318', '319', '320']
            //     }
            // ];

            const ROOM_TYPES = @json($groupedRooms);

            // Bookings: { roomNo, startDate (YYYY-MM-DD), nights, guestName, type }
            const BOOKINGS = [{
                    roomNo: '217',
                    startDate: '2026-03-26',
                    nights: 1,
                    guestName: 'Mr.JAYANTA',
                    type: 'occupied',
                    verified: true
                },
                {
                    roomNo: '219',
                    startDate: '2026-03-26',
                    nights: 1,
                    guestName: 'Mr.SARMIS',
                    type: 'occupied',
                    verified: false
                },
            ];

            // Maintenance rooms
            const MNT_ROOMS = ['211', '504', '202'];

            const NUM_DAYS = 30;

            /* ── State ── */
            let startDate = new Date();
            startDate.setHours(0, 0, 0, 0);

            /* ── Helpers ── */
            function addDays(date, n) {
                const d = new Date(date);
                d.setDate(d.getDate() + n);
                return d;
            }

            function fmtISO(d) {
                return d.toISOString().slice(0, 10);
            }

            function fmtDisplay(d) {
                return d.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short'
                });
            }
            const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const MONTH_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            // Add this function to your @push('scripts') block
            function updateRoomStatus(roomNo, status) {
                // You can use Livewire.dispatch or a standard fetch request
                // This example assumes you'll create a route to handle the update
                fetch(`/admin/rooms/${roomNo}/status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            status: status.toLowerCase()
                        })
                    })
                    .then(response => {
                        if (response.ok) {
                            window.location.reload(); // Refresh to show new colors
                        } else {
                            alert('Failed to update status.');
                        }
                    });
            }
            /* ── Build grid ── */
            function buildGrid() {
                const grid = document.getElementById('hc-grid');
                grid.innerHTML = '';
                grid.style.setProperty('--hc-days', NUM_DAYS);

                const today = fmtISO(new Date());
                const days = [];
                for (let i = 0; i < NUM_DAYS; i++) days.push(addDays(startDate, i));

                // ── Header row ──
                // Room No label
                const rnLabel = cell('div', 'hc-head-cell room-label');
                rnLabel.textContent = 'Room No';
                grid.appendChild(rnLabel);

                days.forEach(d => {
                    const iso = fmtISO(d);
                    const el = cell('div', 'hc-head-cell' + (iso === today ? ' today' : ''));
                    el.innerHTML = `
                <span class="day-num">${d.getDate()}</span>
                <span class="day-month">${MONTH_SHORT[d.getMonth()]}</span>
                <span class="day-name">${DAY_NAMES[d.getDay()]}</span>
            `;
                    grid.appendChild(el);
                });

                // ── Vacant row ──
                const vacLbl = cell('div', 'hc-vacant-cell label');
                vacLbl.innerHTML = `<span>Vacant</span><span>▼</span>`;
                grid.appendChild(vacLbl);

                days.forEach(d => {
                    const vc = cell('div', 'hc-vacant-cell' + (fmtISO(d) === today ? ' today-col' : ''));
                    vc.textContent = '{{$totalVacant}}';
                    grid.appendChild(vc);
                });

                // ── Room type groups ──
                ROOM_TYPES.forEach(rt => {
                    // Type header
                    const thLabel = cell('div', 'hc-type-cell label');
                    thLabel.innerHTML = `<span>${rt.code}</span><span style="color:var(--primary)">${rt.code}</span>`;
                    thLabel.innerHTML = `<b>${rt.code}</b>`;
                    grid.appendChild(thLabel);

                    days.forEach(d => {
                        const tc = cell('div', 'hc-type-cell count-cell');
                        tc.textContent = rt.totalRooms;
                        grid.appendChild(tc);
                    });

                    // Room rows
                    rt.rooms.forEach(room => {
                        // room is now an object: { room_number: "101", status: "dirty" }
                        const roomNo = room.room_number;
                        const status = room.status ? room.status.toLowerCase() : 'clean';

                        // Map your specific requested colors
                        // Green = clean/vacant, Red = dirty, Yellow = mnt
                        let statusClass = 'vacant'; // Default Green
                        if (status === 'dirty') statusClass = 'dirty'; // Red
                        if (status === 'maintenance' || status === 'mnt') statusClass = 'mnt'; // Yellow
                        if (status === 'check-in') statusClass = 'check-in'; // Blue
                        if (status === 'check-out') statusClass = 'check-out'; // Purple

                        const rl = cell('div', 'hc-room-label');
                        rl.style.cursor = 'pointer'; // Make it look clickable
                        rl.innerHTML = `<span class="status-dot ${statusClass}"></span>${roomNo}`;
                        // ACTION: Add Click Event to Change Status
                        rl.addEventListener('click', () => {
                            const newStatus = prompt(`Change status for Room ${roomNo}:`, currentStatus);

                            if (newStatus && newStatus !== currentStatus) {
                                updateRoomStatus(roomNo, newStatus);
                            }
                        });
                        grid.appendChild(rl);

                        days.forEach(d => {
                            const iso = fmtISO(d);
                            const dc = cell('div', 'hc-day-cell' + (iso === today ? ' today-col' : ''));

                            // Booking logic remains the same, using roomNo variable
                            BOOKINGS.forEach(b => {
                                if (b.roomNo === roomNo && b.startDate === iso) {
                                    const chip = document.createElement('div');
                                    chip.className = `hc-booking ${b.type}`;
                                    const w = (b.nights * 110) - 4;
                                    chip.style.width = w + 'px';
                                    chip.innerHTML = b.guestName + (b.verified ? ' <span class="check-icon">✓</span>' : '');
                                    dc.appendChild(chip);
                                }
                            });

                            dc.addEventListener('click', () => openModal({
                                roomNo,
                                date: iso
                            }));
                            grid.appendChild(dc);
                        });
                    });
                });
            }

            function cell(tag, cls) {
                const el = document.createElement(tag);
                el.className = cls;
                return el;
            }

            /* ── Date input sync ── */
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

            /* ── Modal ── */
            const overlay = document.getElementById('hc-modal-overlay');

            function openModal(ctx) {
                // pre-fill fields
                if (ctx && ctx.date) {
                    document.getElementById('qr-arrival').value = ctx.date;
                    const dep = addDays(new Date(ctx.date + 'T00:00:00'), 1);
                    document.getElementById('qr-departure').value = fmtISO(dep);
                }
                if (ctx && ctx.roomNo) {
                    document.getElementById('qr-room-no').value = ctx.roomNo;
                }
                overlay.classList.add('open');
            }

            document.getElementById('hc-new-reservation-btn').addEventListener('click', () => openModal({}));
            document.getElementById('hc-modal-close').addEventListener('click', () => overlay.classList.remove('open'));
            overlay.addEventListener('click', e => {
                if (e.target === overlay) overlay.classList.remove('open');
            });

            // Nights ↔ Departure sync
            document.getElementById('qr-nights').addEventListener('input', function() {
                const arr = document.getElementById('qr-arrival').value;
                if (!arr) return;
                const dep = addDays(new Date(arr + 'T00:00:00'), parseInt(this.value) || 1);
                document.getElementById('qr-departure').value = fmtISO(dep);
            });
            document.getElementById('qr-departure').addEventListener('input', function() {
                const arr = document.getElementById('qr-arrival').value;
                if (!arr || !this.value) return;
                const diff = Math.round((new Date(this.value) - new Date(arr)) / 86400000);
                document.getElementById('qr-nights').value = Math.max(1, diff);
            });

            // Plan remove
            document.getElementById('qr-plan-remove').addEventListener('click', () => {
                document.getElementById('qr-plan-badge').style.display = 'none';
            });

            // Save stub
            document.getElementById('qr-save').addEventListener('click', () => {
                alert('Reservation saved! (Wire up to your Laravel backend)');
                overlay.classList.remove('open');
            });

            /* ── Init ── */
            buildGrid();
        });
    </script>
    @endpush

</x-filament::page>