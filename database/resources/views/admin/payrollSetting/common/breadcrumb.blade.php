<nav class="page-breadcrumb d-flex align-items-center justify-content-between">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">{{ __('index.dashboard') }}</a></li>
        <li class="breadcrumb-item">{{ __('index.payroll_setting') }}</li>
        <li class="breadcrumb-item active" aria-current="page">@yield('page')</li>
        <li class="breadcrumb-item active" aria-current="page">@yield('sub_page')</li>
    </ol>

{{--    <div class="justify-content-end">--}}
{{--        <a class="btn btn-{{request()->routeIs('admin.salary-components.*') ? 'primary' : 'secondary'}} btn-xs"--}}
{{--           href="{{ route('admin.salary-components.index')}}">--}}
{{--                Salary Component--}}
{{--        </a>--}}

{{--        <a class="btn btn-{{request()->routeIs('admin.salary-groups.*') ? 'primary' : 'secondary'}} btn-xs"--}}
{{--           href="{{ route('admin.salary-groups.index')}}">--}}
{{--            Salary Group--}}
{{--        </a>--}}

{{--        <a class="btn btn-{{request()->routeIs('admin.salary-tds.*') ? 'primary' : 'secondary'}} btn-xs"--}}
{{--           href="{{ route('admin.salary-tds.index')}}">--}}
{{--            Salary TDS--}}
{{--        </a>--}}

{{--        <a class="btn btn-{{request()->routeIs('admin.overtime.*') ? 'primary' : 'secondary'}} btn-xs"--}}
{{--           href="{{ route('admin.overtime.create')}}">--}}
{{--            OverTime--}}
{{--        </a>--}}

{{--        <a class="btn btn-{{request()->routeIs('admin.under-time.*') ? 'primary' : 'secondary'}} btn-xs"--}}
{{--           href="{{ route('admin.under-time.create')}}">--}}
{{--            UnderTime--}}
{{--        </a>--}}

{{--        <a class="btn btn-{{request()->routeIs('admin.payment-methods.*') ? 'primary' : 'secondary'}} btn-xs"--}}
{{--           href="{{ route('admin.payment-methods.index')}}">--}}
{{--           Payment Method--}}
{{--        </a>--}}

{{--        <a class="btn btn-{{request()->routeIs('admin.payment-currency.*') ? 'primary' : 'secondary'}} btn-xs mt-2"--}}
{{--           href="{{ route('admin.payment-currency.index')}}">--}}
{{--            Payment Currency--}}
{{--        </a>--}}
{{--        @yield('button')--}}
{{--    </div>--}}
</nav>
