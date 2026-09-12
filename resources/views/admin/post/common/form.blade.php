<div class="row g-3">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-6 col-12 mb-3">
            <label for="branch_id" class="form-label fw-semibold text-dark">{{ __('index.branch') }} <span class="text-danger">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id[]" multiple required data-placeholder="{{ __('index.select_branch') }}">
                <option value="" disabled>{{ __('index.select_branch') }}</option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        @php
                            $selectedBranchIds = collect(old('branch_id', $selectedBranchIds ?? (isset($postDetail) ? [$postDetail->branch_id] : [])))
                                ->map(fn ($id) => (string) $id)
                                ->all();
                        @endphp
                        <option value="{{$branch->id}}"
                            {{ in_array((string) $branch->id, $selectedBranchIds, true) ? 'selected': '' }}>
                            {{ucfirst($branch->name)}}</option>
                    @endforeach
                @endif
            </select>
            <div class="form-text text-muted small">Select one or more branches where this position exists.</div>
        </div>
    @else
        <input type="hidden" readonly id="branch_id" name="branch_id[]" value="{{ auth()->user()->branch_id }}">
    @endif

    <div class="col-lg-6 col-12 mb-3">
        <label for="department_id" class="form-label fw-semibold text-dark">{{ __('index.department_label') }} <span class="text-danger">*</span></label>
        <select class="form-select" id="department_id" name="dept_id[]" multiple required data-placeholder="{{ __('index.select_department') }}">
            <option disabled>{{ __('index.select_department') }}</option>
        </select>
        <div class="form-text text-muted small">Select the department(s) associated with this position.</div>
    </div>

    <div class="col-lg-6 col-12 mb-3">
        <label for="post_name" class="form-label fw-semibold text-dark">{{ __('index.post_name_label') }} <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
                <i class="link-icon" data-feather="briefcase" style="width: 16px; height: 16px;"></i>
            </span>
            <input type="text" class="form-control border-start-0 ps-0" id="post_name" required name="post_name" value="{{ old('post_name', $postDetail->post_name ?? '') }}" autocomplete="off" placeholder="e.g. Senior Software Engineer">
        </div>
    </div>

    <div class="col-lg-6 col-12 mb-3">
        <label for="postStatusSelect" class="form-label fw-semibold text-dark">{{ __('index.status_label') }}</label>
        @php
            $currentStatus = old('is_active', isset($postDetail) ? (string)$postDetail->is_active : '1');
        @endphp
        <select class="form-select" id="postStatusSelect" name="is_active">
            <option value="" disabled>{{ __('index.select_status') }}</option>
            <option value="1" {{ (string)$currentStatus === '1' ? 'selected' : '' }}>{{ __('index.active_option') }}</option>
            <option value="0" {{ (string)$currentStatus === '0' ? 'selected' : '' }}>{{ __('index.inactive_option') }}</option>
        </select>
    </div>

    <div class="col-12 mt-4 pt-2 border-top d-flex align-items-center justify-content-end gap-2">
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary rounded-3 px-4">
            {{ __('index.button_back') }}
        </a>
        <button type="submit" class="btn btn-primary rounded-3 px-4 d-inline-flex align-items-center gap-2">
            <i class="link-icon" data-feather="{{ isset($postDetail) ? 'check' : 'plus' }}" style="width: 16px; height: 16px;"></i>
            <span>{{ isset($postDetail) ? __('index.update') : __('index.create') }}</span>
        </button>
    </div>
</div>
