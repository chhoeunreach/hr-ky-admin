@extends('auth.main')

@section('title', __('auth.login'))

@section('page-styles')
    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 18%, rgba(20, 184, 166, .14), transparent 32%),
                radial-gradient(circle at 82% 10%, rgba(15, 118, 110, .12), transparent 30%),
                linear-gradient(135deg, #f7fbfa 0%, #eef5f3 48%, #f8fafc 100%);
        }

        .login-page {
            min-height: 100vh;
            padding: 32px 16px;
        }

        .login-shell {
            width: min(1080px, 100%);
            overflow: hidden;
            border: 1px solid rgba(15, 23, 42, .08);
            border-radius: 8px;
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 24px 70px rgba(15, 23, 42, .14);
        }

        .login-brand-panel {
            position: relative;
            min-height: 100%;
            padding: 42px;
            color: #fff;
            background:
                linear-gradient(145deg, rgba(15, 118, 110, .96), rgba(17, 94, 89, .94)),
                url("{{ asset('assets/images/clock.jpg') }}") center/cover;
            isolation: isolate;
        }

        .login-brand-panel::before {
            position: absolute;
            inset: 0;
            z-index: -1;
            content: "";
            background: linear-gradient(145deg, rgba(15, 23, 42, .18), rgba(15, 118, 110, .86));
        }

        .login-logo-frame {
            width: 118px;
            height: 118px;
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
            max-width: 360px;
            margin-top: 34px;
            font-size: 32px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0;
        }

        .login-brand-copy {
            max-width: 390px;
            margin-top: 14px;
            color: rgba(255, 255, 255, .82);
            font-size: 15px;
            line-height: 1.7;
        }

        .login-highlights {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 42px;
        }

        .login-highlight {
            padding: 14px;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 8px;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(8px);
        }

        .login-highlight i {
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

        .login-form-panel {
            padding: 44px;
        }

        .login-company {
            color: #0f172a;
            font-size: 24px;
            font-weight: 800;
            line-height: 1.25;
            text-decoration: none;
        }

        .login-company:hover {
            color: var(--primary-color);
            text-decoration: none;
        }

        .login-downloads .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 6px;
            font-weight: 600;
        }

        .login-downloads .link-icon {
            width: 15px;
            height: 15px;
        }

        .auth-form-wrapper {
            padding: 0 !important;
        }

        @media (max-width: 767.98px) {
            .login-page {
                padding: 18px 12px;
            }

            .login-brand-panel,
            .login-form-panel {
                padding: 28px;
            }

            .login-brand-title {
                margin-top: 24px;
                font-size: 26px;
            }

            .login-highlights {
                grid-template-columns: 1fr;
                margin-top: 28px;
            }

            .login-downloads {
                width: 100%;
            }

            .login-downloads .btn {
                flex: 1 1 160px;
                justify-content: center;
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
                                        <div class="login-logo-frame">
                                            <img src="
                                                {{$companyDetail && $companyDetail->logo ?
                                                    asset(\App\Models\Company::UPLOAD_PATH.$companyDetail->logo) :
                                                    asset('assets/images/img.png')
                                                }}"
                                                 alt="{{ __('auth.company_logo_alt') }}">
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
                                </div>
                            </div>

                            <div class="col-lg-7">
                                <div class="login-form-panel">
                                    <div class="auth-form-wrapper">
                                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
                                            <a href="#" class="login-company d-block mb-0">{{ $companyDetail  ? ucfirst($companyDetail->name) : ''}}</a>
                                            <div class="login-downloads d-flex align-items-center gap-2 flex-wrap">
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
                                        <h5 class="text-muted fw-normal mb-4">{{ __('auth.welcome_back') }}</h5>
                                        @include('admin.section.flash_message')

                                        <form class="forms-sample" method="POST" action="{{ route('admin.login.process') }}">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="userEmail" class="form-label">{{ __('auth.user_type') }}</label>
                                                <select class="form-select @error('user_type') is-invalid @enderror" id="exampleFormControlSelect1" name="user_type">
                                                    <option selected value="admin">Admin</option>
                                                    <option value="employee">Employee</option>
                                                </select>
                                                @if ($errors->has('user_type'))
                                                    <span class="text-danger">
                                                        <strong>{{ $errors->first('user_type') }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label for="userEmail" class="form-label">{{ __('auth.email_username') }}</label>
                                                <input
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    name="email" value="{{ old('email') }}"
                                                    required
                                                    autocomplete="email"
                                                    autofocus
                                                >
                                                @if ($errors->has('email'))
                                                    <span class="text-danger">
                                                        <strong>{{ $errors->first('email') }}</strong>
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <label for="userPassword" class="form-label">{{ __('auth.password') }}</label>
                                                <input id="password"
                                                       type="password"
                                                       class="form-control @error('password') is-invalid @enderror"
                                                       name="password"
                                                       required
                                                       autocomplete="current-password"
                                                >
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

                                            <div>
                                                <button type="submit" class=" btn btn-primary me-2 mb-2 mb-md-0 text-white">
                                                    {{ __('auth.login') }}
                                                </button>

                                                @if (Route::has('password.request'))
                                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                                        {{ __('auth.forgot_password') }}
                                                    </a>
                                                @endif
                                            </div>
                                        </form>

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
