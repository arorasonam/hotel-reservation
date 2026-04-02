{{-- resources/views/filament/pages/auth/login.blade.php --}}
<x-filament-panels::page.simple>
    <div class="pms-login-wrapper">
        {{-- Background overlay --}}
        <div class="pms-bg-overlay"></div>

        {{-- Login Card --}}
        <div class="pms-login-card">

            {{-- Logo / Brand --}}
            <div class="pms-brand">
                <div class="pms-logo-ring">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" fill="none" class="pms-logo-icon">
                        <path d="M6 38V16l18-8 18 8v22H30v-10h-12v10H6z" fill="currentColor" opacity="0.15" />
                        <path d="M6 38V16l18-8 18 8v22" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" fill="none" />
                        <rect x="18" y="28" width="12" height="10" rx="1" stroke="currentColor" stroke-width="2.5" fill="none" />
                        <path d="M20 22h8M24 18v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                </div>
                <h1 class="pms-hotel-name">Grand Vista Hotel</h1>
                <p class="pms-system-label">Property Management System</p>
            </div>

            {{-- Divider --}}
            <div class="pms-divider">
                <span>Sign in to continue</span>
            </div>

            {{-- Form --}}
            <x-filament-panels::form id="form" wire:submit="authenticate">
                {{ $this->form }}

                <div class="pms-form-actions">
                    <x-filament::button
                        type="submit"
                        size="lg"
                        class="pms-submit-btn"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Sign In</span>
                        <span wire:loading class="pms-loading-text">
                            <svg class="pms-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Signing in...
                        </span>
                    </x-filament::button>
                </div>
            </x-filament-panels::form>

            {{-- Forgot Password --}}
            @if (filament()->hasPasswordReset())
            <div class="pms-forgot-link">
                <a href="{{ filament()->getResetPasswordUrl() }}">
                    Forgot your password?
                </a>
            </div>
            @endif

            {{-- Footer --}}
            <div class="pms-footer">
                <span>v{{ config('app.version', '1.0.0') }}</span>
                <span class="pms-footer-dot">·</span>
                <span>© {{ date('Y') }} {{ config('app.name', 'Grand Vista Hotel') }}. All rights reserved.</span>
            </div>

        </div>
    </div>

    @push('styles')
    <style>
        /* ─── Reset & Root ───────────────────────────────────────── */
        :root {
            --gold: #C9A84C;
            --gold-light: #E2C47A;
            --gold-dim: #8A6F32;
            --dark-bg: #0D0F14;
            --card-bg: rgba(18, 21, 28, 0.92);
            --border: rgba(201, 168, 76, 0.25);
            --text-primary: #F5F0E8;
            --text-muted: #8B8680;
            --input-bg: rgba(255, 255, 255, 0.04);
            --input-border: rgba(201, 168, 76, 0.3);
            --danger: #E05252;
        }

        /* ─── Full-page wrapper ──────────────────────────────────── */
        .pms-login-wrapper {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--dark-bg);
            background-image:
                url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w=1800&q=80&auto=format&fit=crop'),
                linear-gradient(135deg, #0D0F14 0%, #1a1e2a 100%);
            background-size: cover;
            background-position: center;
            font-family: 'Cormorant Garamond', 'Playfair Display', Georgia, serif;
            min-height: 100vh;
            z-index: 0;
        }

        /* ─── Dark gradient overlay ──────────────────────────────── */
        .pms-bg-overlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg,
                    rgba(8, 10, 14, 0.88) 0%,
                    rgba(13, 15, 20, 0.75) 50%,
                    rgba(18, 14, 8, 0.88) 100%);
            z-index: 1;
        }

        /* ─── Login card ─────────────────────────────────────────── */
        .pms-login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 460px;
            margin: 1.5rem;
            padding: 2.75rem 2.5rem 2rem;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 4px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow:
                0 0 0 1px rgba(201, 168, 76, 0.08),
                0 32px 80px rgba(0, 0, 0, 0.7),
                inset 0 1px 0 rgba(201, 168, 76, 0.15);
            animation: cardReveal 0.7s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes cardReveal {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ─── Gold corner accents ────────────────────────────────── */
        .pms-login-card::before,
        .pms-login-card::after {
            content: '';
            position: absolute;
            width: 28px;
            height: 28px;
            border-color: var(--gold);
            border-style: solid;
            pointer-events: none;
        }

        .pms-login-card::before {
            top: -1px;
            left: -1px;
            border-width: 2px 0 0 2px;
            border-radius: 4px 0 0 0;
        }

        .pms-login-card::after {
            bottom: -1px;
            right: -1px;
            border-width: 0 2px 2px 0;
            border-radius: 0 0 4px 0;
        }

        /* ─── Brand / Logo ───────────────────────────────────────── */
        .pms-brand {
            text-align: center;
            margin-bottom: 1.75rem;
            animation: fadeUp 0.6s 0.1s both;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pms-logo-ring {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 68px;
            height: 68px;
            border-radius: 50%;
            border: 1.5px solid var(--gold);
            background: radial-gradient(circle, rgba(201, 168, 76, 0.12) 0%, transparent 70%);
            color: var(--gold);
            margin-bottom: 1rem;
            box-shadow: 0 0 24px rgba(201, 168, 76, 0.2);
        }

        .pms-logo-icon {
            width: 34px;
            height: 34px;
        }

        .pms-hotel-name {
            font-size: 1.65rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--gold-light);
            margin: 0 0 0.2rem;
            line-height: 1.2;
        }

        .pms-system-label {
            font-size: 0.72rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin: 0;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif;
            font-weight: 400;
        }

        /* ─── Section divider ────────────────────────────────────── */
        .pms-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            animation: fadeUp 0.6s 0.15s both;
        }

        .pms-divider::before,
        .pms-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
        }

        .pms-divider span {
            font-size: 0.7rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--text-muted);
            white-space: nowrap;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif;
        }

        /* ─── Filament form overrides ────────────────────────────── */
        .pms-login-card .fi-fo-field-wrp {
            animation: fadeUp 0.55s calc(var(--fi-delay, 0.2s)) both;
        }

        .pms-login-card .fi-fo-field-wrp:nth-child(2) {
            --fi-delay: 0.28s;
        }

        .pms-login-card .fi-fo-field-wrp:nth-child(3) {
            --fi-delay: 0.35s;
        }

        /* Labels */
        .pms-login-card label {
            color: var(--text-muted) !important;
            font-size: 0.72rem !important;
            letter-spacing: 0.14em !important;
            text-transform: uppercase !important;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif !important;
            font-weight: 500 !important;
        }

        /* Inputs */
        .pms-login-card input[type="email"],
        .pms-login-card input[type="text"],
        .pms-login-card input[type="password"] {
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            border-radius: 3px !important;
            color: var(--text-primary) !important;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif !important;
            font-size: 0.92rem !important;
            padding: 0.65rem 1rem !important;
            transition: border-color 0.2s, box-shadow 0.2s !important;
        }

        .pms-login-card input:focus {
            border-color: var(--gold) !important;
            box-shadow: 0 0 0 3px rgba(201, 168, 76, 0.12) !important;
            outline: none !important;
        }

        /* Prefix icons */
        .pms-login-card .fi-input-wrp-prefix-icon {
            color: var(--gold-dim) !important;
        }

        /* Checkbox */
        .pms-login-card input[type="checkbox"] {
            accent-color: var(--gold) !important;
            width: 15px !important;
            height: 15px !important;
            border-radius: 2px !important;
        }

        .pms-login-card .fi-checkbox-label {
            color: var(--text-muted) !important;
            font-size: 0.82rem !important;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif !important;
        }

        /* ─── Submit button ──────────────────────────────────────── */
        .pms-form-actions {
            margin-top: 1.25rem;
            animation: fadeUp 0.55s 0.42s both;
        }

        .pms-submit-btn,
        .pms-login-card .fi-btn-primary,
        .pms-login-card button[type="submit"] {
            width: 100% !important;
            background: linear-gradient(135deg, #B8912A 0%, #C9A84C 50%, #B8912A 100%) !important;
            background-size: 200% 100% !important;
            border: none !important;
            border-radius: 3px !important;
            color: #0D0F14 !important;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            letter-spacing: 0.2em !important;
            text-transform: uppercase !important;
            padding: 0.85rem 2rem !important;
            cursor: pointer !important;
            transition: background-position 0.4s, box-shadow 0.3s, transform 0.15s !important;
            box-shadow: 0 4px 20px rgba(201, 168, 76, 0.3) !important;
        }

        .pms-submit-btn:hover,
        .pms-login-card button[type="submit"]:hover {
            background-position: right center !important;
            box-shadow: 0 6px 28px rgba(201, 168, 76, 0.45) !important;
            transform: translateY(-1px) !important;
        }

        .pms-submit-btn:active,
        .pms-login-card button[type="submit"]:active {
            transform: translateY(0) !important;
        }

        .pms-submit-btn:disabled {
            opacity: 0.7 !important;
            transform: none !important;
        }

        /* ─── Spinner ────────────────────────────────────────────── */
        .pms-loading-text {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pms-spinner {
            width: 16px;
            height: 16px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ─── Forgot password ────────────────────────────────────── */
        .pms-forgot-link {
            text-align: center;
            margin-top: 1.1rem;
            animation: fadeUp 0.55s 0.48s both;
        }

        .pms-forgot-link a {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-decoration: none;
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif;
            letter-spacing: 0.04em;
            transition: color 0.2s;
        }

        .pms-forgot-link a:hover {
            color: var(--gold-light);
        }

        /* ─── Footer ─────────────────────────────────────────────── */
        .pms-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(201, 168, 76, 0.1);
            font-size: 0.68rem;
            color: rgba(139, 134, 128, 0.6);
            font-family: 'DM Sans', 'Helvetica Neue', sans-serif;
            letter-spacing: 0.06em;
            animation: fadeUp 0.55s 0.55s both;
        }

        .pms-footer-dot {
            color: var(--gold-dim);
        }

        /* ─── Hide Filament's default heading/subheading ─────────── */
        .fi-simple-header {
            display: none !important;
        }

        /* ─── Responsive ─────────────────────────────────────────── */
        @media (max-width: 480px) {
            .pms-login-card {
                padding: 2rem 1.5rem 1.5rem;
                margin: 1rem;
            }

            .pms-hotel-name {
                font-size: 1.35rem;
            }
        }
    </style>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @endpush
</x-filament-panels::page.simple>