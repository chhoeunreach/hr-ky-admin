    <!-- sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="{{route('admin.dashboard')}}" class="sidebar-brand">
               <img src="{{ asset(\App\Models\Company::UPLOAD_PATH.\App\Helpers\AppHelper::getCompanyLogo())}}"  />
            </a>
            <div class="sidebar-toggler not-active">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>

        <div class="sidebar-body">
            <ul class="nav sidebar-menu">
                {{-- Dashboard --}}
                @include('admin.section.partial.dashboard')

                {{-- I. Core HR --}}
                <li class="nav-item nav-category">{{ __('index.core_hr') }}</li>
                @include('admin.section.partial.company')
                @include('admin.section.partial.companyDocuments')
                @include('admin.section.partial.user')
                @include('admin.section.partial.userManagement')

                {{-- II. Time & Attendance --}}
                <li class="nav-item nav-category">{{ __('index.time_and_attendance') }}</li>
                @include('admin.section.partial.attendance')
                @include('admin.section.partial.shiftManagement')
                @include('admin.section.partial.leave')

                {{-- III. Payroll & Finance --}}
                <li class="nav-item nav-category">{{ __('index.payroll_and_finance') }}</li>
                @include('admin.section.partial.payroll')
                @include('admin.section.partial.payroll-queue')

                {{-- IV. Talent & Performance --}}
                <li class="nav-item nav-category">{{ __('index.talent_and_performance') }}</li>
                @include('admin.section.partial.staffEvaluation')
                @include('admin.section.partial.trainingManagement')
                @include('admin.section.partial.certificateManagement')
                @include('admin.section.partial.hrAdminSetup')

                {{-- V. Operations & Engagement --}}
                <li class="nav-item nav-category">{{ __('index.operations_and_assets') }}</li>
                @include('admin.daily-records.menu')
                @include('admin.section.partial.projectManagement')
                @include('admin.section.partial.assetManagement')
                @include('admin.section.partial.repairManagement')
                @include('admin.section.partial.notice')
                @include('admin.section.partial.event')
                @include('admin.section.partial.team-meeting')
                @include('admin.section.partial.community')
                @include('admin.section.partial.link')
                @include('admin.section.partial.ticket')

                {{-- VI. System & Settings --}}
                <li class="nav-item nav-category">{{ __('index.system_and_settings') }}</li>
                @include('admin.section.partial.attendanceMethod')
                @include('admin.section.partial.mobileTool')
                @include('admin.section.partial.staticPageContent')
                @include('admin.section.partial.setting')
            </ul>
        </div>
    </nav>
    <!-- partial -->
