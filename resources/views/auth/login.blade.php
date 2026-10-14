@extends('auth.main')

@section('title', __('auth.login'))

@section('page-styles')
    <style>
        html {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            color: #172033;
            background:
                radial-gradient(circle at 14% 18%, rgba(20, 184, 166, .16), transparent 28%),
                radial-gradient(circle at 88% 12%, rgba(234, 179, 8, .14), transparent 26%),
                linear-gradient(135deg, #f8fafc 0%, #eef6f5 48%, #f6f4ef 100%);
        }

        .login-page {
            min-height: 100vh;
            padding: 38px 18px;
        }

        .login-shell {
            width: min(1120px, 100%);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, .09);
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 26px 70px rgba(15, 23, 42, .15);
        }

        .login-brand-panel {
            position: relative;
            min-height: 100%;
            padding: 44px;
            color: #fff;
            background:
                linear-gradient(145deg, rgba(12, 74, 110, .88), rgba(15, 118, 110, .9)),
                url("{{ asset('assets/images/clock.jpg') }}") center/cover;
            isolation: isolate;
        }

        .login-brand-panel::before {
            position: absolute;
            inset: 0;
            z-index: -1;
            content: "";
            background:
                linear-gradient(145deg, rgba(15, 23, 42, .34), rgba(15, 118, 110, .84)),
                linear-gradient(0deg, rgba(15, 23, 42, .18), rgba(15, 23, 42, .18));
        }

        .login-brand-panel::after {
            position: absolute;
            right: 28px;
            bottom: 28px;
            width: 172px;
            height: 172px;
            content: "";
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 50%;
            opacity: .65;
            pointer-events: none;
        }

        .login-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            color: rgba(255, 255, 255, .9);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .login-eyebrow svg {
            width: 14px;
            height: 14px;
        }

        .login-logo-frame {
            width: 96px;
            height: 96px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .38);
            border-radius: 8px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 18px 42px rgba(15, 23, 42, .2);
        }

        .login-logo-frame img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 12px;
        }

        .login-brand-title {
            max-width: 390px;
            margin-top: 26px;
            font-size: 34px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .login-brand-copy {
            max-width: 410px;
            margin-top: 14px;
            color: rgba(255, 255, 255, .82);
            font-size: 15px;
            line-height: 1.7;
        }

        .login-highlights {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 38px;
        }

        .login-highlight {
            min-height: 94px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 8px;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(8px);
        }

        .login-highlight i,
        .login-highlight svg {
            width: 18px;
            height: 18px;
            margin-bottom: 8px;
        }

        .login-highlight span {
            display: block;
            color: rgba(255, 255, 255, .9);
            font-size: 13px;
            font-weight: 600;
        }

        .login-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 28px;
            color: rgba(255, 255, 255, .72);
            font-size: 13px;
        }

        .login-meta::before {
            width: 32px;
            height: 1px;
            content: "";
            background: rgba(255, 255, 255, .34);
        }

        .login-form-panel {
            min-height: 620px;
            padding: 46px 50px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, .98), rgba(250, 252, 252, .98));
        }

        .login-form-inner {
            max-width: 500px;
            margin: 0 auto;
        }

        .login-company {
            color: #0f172a;
            font-size: 25px;
            font-weight: 800;
            line-height: 1.25;
            text-decoration: none;
        }

        .login-company:hover {
            color: var(--primary-color);
            text-decoration: none;
        }

        .login-subtitle {
            max-width: 420px;
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }

        .login-download-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin: 24px 0 28px;
            padding: 14px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }

        .login-download-copy {
            min-width: 160px;
        }

        .login-download-copy strong {
            display: block;
            color: #172033;
            font-size: 13px;
            font-weight: 800;
        }

        .login-download-copy span {
            display: block;
            margin-top: 3px;
            color: #64748b;
            font-size: 12px;
        }

        .login-downloads .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 6px;
            font-weight: 600;
            white-space: nowrap;
        }

        .login-downloads .link-icon {
            width: 15px;
            height: 15px;
        }

        .auth-form-wrapper {
            padding: 0 !important;
        }

        .login-field {
            position: relative;
        }

        .login-field-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            width: 18px;
            height: 18px;
            color: #94a3b8;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .login-field .form-control,
        .login-field .form-select {
            min-height: 48px;
            padding-left: 45px;
            border-color: #dbe3ec;
            border-radius: 8px;
            color: #172033;
            font-size: 14px;
            box-shadow: none;
        }

        .login-field .form-control:focus,
        .login-field .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 .16rem rgba(15, 118, 110, .14);
        }

        .login-password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            transform: translateY(-50%);
        }

        .login-password-toggle:hover,
        .login-password-toggle:focus {
            background: #eef6f5;
            color: var(--primary-color);
            outline: none;
        }

        .login-password-toggle svg {
            width: 17px;
            height: 17px;
        }

        .login-field-password .form-control {
            padding-right: 52px;
        }

        .login-submit-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 26px;
        }

        .login-submit-row .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 154px;
            min-height: 46px;
            border: 0;
            border-radius: 8px;
            background: var(--primary-color);
            box-shadow: 0 14px 28px rgba(15, 118, 110, .22);
            font-weight: 700;
        }

        .login-submit-row .btn-primary:hover,
        .login-submit-row .btn-primary:focus {
            background: var(--hover-color);
            box-shadow: 0 16px 30px rgba(15, 118, 110, .26);
        }

        .login-submit-row .btn-link {
            color: #64748b;
            font-weight: 600;
            text-decoration: none;
        }

        .login-submit-row .btn-link:hover {
            color: var(--primary-color);
        }

        .login-assurance {
            display: flex;
            gap: 10px;
            margin-top: 24px;
            padding: 13px 14px;
            border: 1px solid #e6edf3;
            border-radius: 8px;
            background: #fff;
            color: #64748b;
            font-size: 12px;
            line-height: 1.55;
        }

        .login-assurance svg {
            flex: 0 0 auto;
            width: 18px;
            height: 18px;
            margin-top: 1px;
            color: #0f766e;
        }

        @media (max-width: 767.98px) {
            body {
                background: #f7faf9;
            }

            .login-page {
                align-items: flex-start !important;
                padding: 14px 10px;
            }

            .login-shell {
                border-radius: 8px;
                box-shadow: 0 18px 40px rgba(15, 23, 42, .12);
            }

            .login-brand-panel,
            .login-form-panel {
                min-height: auto;
                padding: 24px;
            }

            .login-brand-panel {
                gap: 24px;
            }

            .login-brand-panel::after {
                display: none;
            }

            .login-logo-frame {
                width: 74px;
                height: 74px;
            }

            .login-brand-title {
                margin-top: 20px;
                font-size: 25px;
            }

            .login-highlights {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-top: 24px;
            }

            .login-highlight {
                min-height: 80px;
                padding: 12px;
            }

            .login-form-inner {
                max-width: none;
            }

            .login-company {
                font-size: 22px;
            }

            .login-download-card {
                display: block;
                margin: 20px 0 24px;
                padding: 12px;
            }

            .login-downloads {
                width: 100%;
                margin-top: 12px;
            }

            .login-downloads .btn {
                flex: 1 1 130px;
                justify-content: center;
            }

            .login-submit-row {
                display: block;
            }

            .login-submit-row .btn-primary {
                width: 100%;
            }

            .login-submit-row .btn-link {
                display: block;
                width: 100%;
                margin-top: 12px;
                text-align: center;
            }
        }

        @media (max-width: 420px) {
            .login-brand-panel,
            .login-form-panel {
                padding: 20px;
            }

            .login-highlights {
                grid-template-columns: 1fr;
            }

            .login-downloads {
                display: block !important;
            }

            .login-downloads .btn {
                width: 100%;
            }

            .login-downloads .btn + .btn {
                margin-top: 8px;
            }
        }
    </style>
@endsection

@section('auth-content')
    <section class="content">
        <div class="main-wrapper">
            <div class="page-wrapper full-page">
                <div class="page-content login-page d-flex align-items-center justify-content-center">
                    <div class="login-shell">
                        <div class="row g-0 align-items-stretch">
                            <div class="col-lg-5">
                                <div class="login-brand-panel d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="login-eyebrow">
                                            <i data-feather="lock"></i>
                                            HR Operations Portal
                                        </div>
                                        <div class="login-logo-frame">
                                            <img src="
                                                {{$companyDetail && $companyDetail->logo ?
                                                    asset(\App\Models\Company::UPLOAD_PATH.$companyDetail->logo) :
                                                    asset('assets/images/img.png')
                                                }}"
                                                 alt="{{ $companyDetail ? ucfirst($companyDetail->name) : 'Company logo' }}">
                                        </div>
                                        <div class="login-brand-title">
                                            {{ $companyDetail  ? ucfirst($companyDetail->name) : 'Digital HR' }}
                                        </div>
                                        <p class="login-brand-copy mb-0">
                                            Secure workforce access for attendance, payroll, approvals, and daily operations.
                                        </p>
                                    </div>

                                    <div class="login-highlights">
                                        <div class="login-highlight">
                                            <i data-feather="shield"></i>
                                            <span>Protected Access</span>
                                        </div>
                                        <div class="login-highlight">
                                            <i data-feather="clock"></i>
                                            <span>Attendance Ready</span>
                                        </div>
                                        <div class="login-highlight">
                                            <i data-feather="users"></i>
                                            <span>Team Management</span>
                                        </div>
                                        <div class="login-highlight">
                                            <i data-feather="bar-chart-2"></i>
                                            <span>Live Reporting</span>
                                        </div>
                                    </div>
                                    <div class="login-meta">
                                        Secure admin and employee sign in
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-7">
                                <div class="login-form-panel">
                                    <div class="auth-form-wrapper login-form-inner">
                                        <div class="mb-3">
                                            <a href="#" class="login-company d-block mb-2">{{ $companyDetail  ? ucfirst($companyDetail->name) : 'Digital HR'}}</a>
                                            <p class="login-subtitle mb-0">
                                                {{ __('auth.welcome_back') }}. Sign in to manage attendance, payroll, leave, approvals, and employee records.
                                            </p>
                                        </div>

                                        <div class="login-download-card">
                                            <div class="login-download-copy">
                                                <strong>Mobile attendance app</strong>
                                                <span>Download the employee app for Android or iOS.</span>
                                            </div>
                                            <div class="login-downloads d-flex align-items-center gap-2 flex-wrap justify-content-end">
                                                <a href="{{ $androidApkUrl }}"
                                                   class="btn btn-outline-primary btn-sm"
                                                   download>
                                                    <i class="link-icon" data-feather="download"></i>
                                                    Download Android
                                                </a>
                                                <a href="https://testflight.apple.com/join/hPG4ZA38"
                                                   class="btn btn-outline-secondary btn-sm"
                                                   target="_blank"
                                                   rel="noopener noreferrer">
                                                    <i class="link-icon" data-feather="smartphone"></i>
                                                    Download iOS
                                                </a>
                                            </div>
                                        </div>

                                        @include('admin.section.flash_message')

                                        <form class="forms-sample" method="POST" action="{{ route('admin.login.process') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="userType" class="form-label">{{ __('auth.user_type') }}</label>
                                                <div class="login-field">
                                                    <i class="login-field-icon" data-feather="briefcase"></i>
                                                    <select class="form-select @error('user_type') is-invalid @enderror" id="userType" name="user_type">
                                                    <option selected value="admin">Admin</option>
                                                    <option value="employee">Employee</option>
                                                    </select>
                                                </div>
                                                @if ($errors->has('user_type'))
                                                    <span class="text-danger">
                                                        <strong>{{ $errors->first('user_type') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label for="userEmail" class="form-label">{{ __('auth.email_username') }}</label>
                                                <div class="login-field">
                                                    <i class="login-field-icon" data-feather="user"></i>
                                                    <input
                                                        id="userEmail"
                                                        class="form-control @error('email') is-invalid @enderror"
                                                        name="email" value="{{ old('email') }}"
                                                        required
                                                        autocomplete="email"
                                                        autofocus
                                                        placeholder="{{ __('auth.email_username') }}"
                                                    >
                                                </div>
                                                @if ($errors->has('email'))
                                                    <span class="text-danger">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <label for="userPassword" class="form-label">{{ __('auth.password') }}</label>
                                                <div class="login-field login-field-password">
                                                    <i class="login-field-icon" data-feather="key"></i>
                                                    <input id="password"
                                                           type="password"
                                                           class="form-control @error('password') is-invalid @enderror"
                                                           name="password"
                                                           required
                                                           autocomplete="current-password"
                                                           placeholder="{{ __('auth.password') }}"
                                                    >
                                                    <button type="button"
                                                            class="login-password-toggle"
                                                            id="passwordToggle"
                                                            aria-label="Show password">
                                                        <i data-feather="eye"></i>
                                                    </button>
                                                </div>
                                                @if ($errors->has('password'))
                                                    <span class="text-danger">
                                                        <strong>{{ $errors->first('password') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

{{--                                                <div class="form-check mb-3">--}}
{{--                                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>--}}
{{--                                                    <label class="form-check-label" for="remember">--}}
{{--                                                        Remember me--}}
{{--                                                    </label>--}}
{{--                                                </div>--}}

                                            <div class="login-submit-row">
                                                <button type="submit" class="btn btn-primary text-white">
                                                    {{ __('auth.login') }}
                                                    <i data-feather="arrow-right"></i>
                                                </button>

                                                @if (Route::has('password.request'))
                                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                                        {{ __('auth.forgot_password') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </form>

                                        <div class="login-assurance">
                                            <i data-feather="shield"></i>
                                            <span>Your session is protected. Use an authorized company account to continue.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('page-scripts')
    <script>
        (function () {
            var passwordInput = document.getElementById('password');
            var passwordToggle = document.getElementById('passwordToggle');

            if (!passwordInput || !passwordToggle) {
                return;
            }

            passwordToggle.addEventListener('click', function () {
                var isHidden = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isHidden ? 'text' : 'password');
                passwordToggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
                passwordToggle.innerHTML = '<i data-feather="' + (isHidden ? 'eye-off' : 'eye') + '"></i>';

                if (window.feather) {
                    window.feather.replace();
                }
            });
        })();
    </script>
@endsection
