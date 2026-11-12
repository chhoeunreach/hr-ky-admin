@php($editing = $record->exists)
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" enctype="multipart/form-data" action="{{ $editing ? route('admin.social-marketing.update', $record) : route('admin.social-marketing.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="row">
        <div class="col-md-6">
            <label class="form-label" for="employee_id">Employee</label>
            <select class="form-select mb-3" id="employee_id" name="employee_id" @disabled($editing) required>
                <option value="">Select employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" @selected((int) old('employee_id', $record->existing_employee_id) === $employee->id)>{{ $employee->name }} @if($employee->employee_code)({{ $employee->employee_code }})@endif</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="log_date">Submission date</label>
            <input class="form-control mb-3" type="date" id="log_date" name="log_date" value="{{ old('log_date', optional($record->log_date)->format('Y-m-d') ?? now()->toDateString()) }}" @disabled($editing) required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4"><label class="form-label" for="fb_post_url">Facebook post URL</label><input class="form-control mb-3" type="url" id="fb_post_url" name="fb_post_url" maxlength="2048" value="{{ old('fb_post_url', $record->fb_post_url) }}" required></div>
        <div class="col-md-4"><label class="form-label" for="fb_story_url">Facebook story URL</label><input class="form-control mb-3" type="url" id="fb_story_url" name="fb_story_url" maxlength="2048" value="{{ old('fb_story_url', $record->fb_story_url) }}" required></div>
        <div class="col-md-4"><label class="form-label" for="tiktok_url">TikTok URL</label><input class="form-control mb-3" type="url" id="tiktok_url" name="tiktok_url" maxlength="2048" value="{{ old('tiktok_url', $record->tiktok_url) }}" required></div>
    </div>
    <div class="row">
        @foreach(['fb_post_photo' => 'Facebook post photo', 'fb_story_photo' => 'Facebook story photo', 'tiktok_photo' => 'TikTok photo'] as $field => $label)
            <div class="col-md-4">
                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                <input class="form-control mb-1" type="file" id="{{ $field }}" name="{{ $field }}" accept="image/jpeg,image/png,image/gif,image/webp">
                @if($record->{$field})
                    <a class="small" href="{{ $record->photoUrl($record->{$field}) }}" target="_blank" rel="noopener">View current photo</a>
                @endif
            </div>
        @endforeach
    </div>
    <p class="small text-muted mt-2">Images up to 5 MB each. Uploading a new photo replaces the old one; replaced and deleted photos are removed when no record uses them.</p>
    <div class="row mt-2">
        <div class="col-md-8">
            <label class="form-label" for="reason">Override reason {{ $editing ? '' : '(optional)' }}</label>
            <textarea class="form-control mb-3" id="reason" name="reason" rows="3" maxlength="2000" placeholder="Explain why this submission is being changed">{{ old('reason') }}</textarea>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            @if($editing)
                <label class="form-check mb-3"><input class="form-check-input" type="checkbox" name="unlock" value="1" @checked(old('unlock', !$record->is_locked))> Mark as overridden / unlocked</label>
            @else
                <p class="small text-muted mb-3">New submissions start locked at 1 point until an admin overrides them.</p>
            @endif
        </div>
    </div>
    <button class="btn btn-primary">{{ $editing ? 'Save Override' : 'Create Submission' }}</button>
    <a class="btn btn-light" href="{{ $editing ? route('admin.social-marketing.show', $record) : route('admin.social-marketing.index') }}">Cancel</a>
</form>
