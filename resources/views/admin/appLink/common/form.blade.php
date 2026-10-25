@php
    $selectedPlatform = old('link_type', $linkDetail->link_type ?? '');
    $selectedPlatform = is_string($selectedPlatform) ? $selectedPlatform : '';
@endphp
<div class="row">
    <div class="col-lg-6 col-md-6 mb-4">
        <label for="name" class="form-label">Link Name <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="name" name="name" required
               value="{{ old('name', $linkDetail->name ?? '') }}"
               autocomplete="off" placeholder="e.g. Official Telegram Channel">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="link_type" class="form-label">{{ __('index.link_platform') }} <span style="color: red">*</span></label>
        <select class="form-select" id="link_type" name="link_type" required>
            <option value="" disabled {{ !old('link_type', $linkDetail->link_type ?? '') ? 'selected' : '' }}>{{ __('index.link_select_platform') }}</option>
            @foreach($linkTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('link_type', $linkDetail->link_type ?? '') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-6 col-md-6 mb-4" data-account-type-wrapper>
        <label for="account_type" class="form-label">{{ __('index.link_account_type') }} <span class="text-danger">*</span></label>
        <select class="form-select" id="account_type" name="account_type" data-account-type
                data-platform-id="link_type" data-account-required="1"
                data-selected="{{ old('account_type', $linkDetail->account_type ?? '') }}">
            <option value="">{{ __('index.link_select_account_type') }}</option>
            @foreach(\App\Models\AppLink::ACCOUNT_TYPES[$selectedPlatform] ?? [] as $key => $label)
                <option value="{{ $key }}" @selected(old('account_type', $linkDetail->account_type ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('account_type')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="username" class="form-label">{{ __('index.link_username') }}</label>
        <input type="text" class="form-control" id="username" name="username" maxlength="255" autocomplete="off"
               value="{{ old('username', $linkDetail->username ?? '') }}">
        @error('username')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="phone_number" class="form-label">{{ __('index.phone_number') }}</label>
        <input type="tel" class="form-control" id="phone_number" name="phone_number" maxlength="50" autocomplete="off"
               value="{{ old('phone_number', $linkDetail->phone_number ?? '') }}" placeholder="+855 12 345 678">
        @error('phone_number')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="email" class="form-label">{{ __('index.email') }}</label>
        <input type="email" class="form-control" id="email" name="email" maxlength="255" autocomplete="off"
               value="{{ old('email', $linkDetail->email ?? '') }}">
        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="password" class="form-label">{{ __('index.link_password') }}</label>
        <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" maxlength="1000" autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button" data-toggle-link-password aria-controls="password" aria-pressed="false">{{ __('index.link_show_password') }}</button>
        </div>
        @if(isset($linkDetail) && $linkDetail->getRawOriginal('password'))
            <div class="form-text">{{ __('index.link_keep_password') }}</div>
            <label class="form-check-label mt-2"><input type="checkbox" name="clear_password" value="1" @checked(old('clear_password'))> {{ __('index.link_clear_password') }}</label>
        @endif
        @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="created_by_name" class="form-label">{{ __('index.link_created_by') }}</label>
        <input type="text" id="created_by_name" class="form-control" readonly
               value="{{ isset($linkDetail) ? ($linkDetail->created_by_name ?: $linkDetail->createdBy?->name ?: '—') : (auth('admin')->user()?->name ?: auth()->user()?->name ?: '—') }}">
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="manager_ids" class="form-label">{{ __('index.link_managed_by') }}</label>
        @php $selectedManagers = array_map('strval', (array) old('manager_ids', isset($linkDetail) ? $linkDetail->managers->modelKeys() : [])); @endphp
        <select id="manager_ids" name="manager_ids[]" class="form-select" multiple size="5" data-placeholder="{{ __('index.link_select_managers') }}">
            @foreach($managerOptions ?? [] as $manager)
                <option value="{{ $manager->id }}" @selected(in_array((string) $manager->id, $selectedManagers, true))>
                    {{ $manager->name }}{{ $manager->english_name ? ' / '.$manager->english_name : '' }}{{ $manager->employee_code ? ' · '.$manager->employee_code : '' }}
                </option>
            @endforeach
        </select>
        <div class="form-text">{{ __('index.link_managers_help') }}</div>
        @error('manager_ids')<div class="text-danger small">{{ $message }}</div>@enderror
        @foreach($errors->get('manager_ids.*') as $messages)
            @foreach($messages as $message)<div class="text-danger small">{{ $message }}</div>@endforeach
        @endforeach
    </div>

    <div class="col-lg-6 col-md-6 mb-4">
        <label for="url" class="form-label">URL / Link <span style="color: red">*</span></label>
        <input type="url" class="form-control" id="url" name="url" required
               value="{{ old('url', $linkDetail->url ?? '') }}"
               autocomplete="off" placeholder="https://t.me/yourchannel">
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="order" class="form-label">Display Order</label>
        <input type="number" class="form-control" id="order" name="order" min="0"
               value="{{ old('order', $linkDetail->order ?? 0) }}"
               placeholder="0">
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <label for="status" class="form-label">Status</label>
        <div class="mt-2">
            <label class="switch">
                <input type="hidden" name="status" value="0">
                <input type="checkbox" name="status" value="1" @checked(old('status', $linkDetail->status ?? true))>
                <span class="slider round"></span>
            </label>
            <span class="ms-2">Active</span>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <label for="image" class="form-label">Photo / Icon</label>
        <input class="form-control" type="file" id="image" name="image" accept=".jpeg,.png,.jpg,.webp,.svg">
        <div class="mt-3">
            <img class="{{ (isset($linkDetail) && $linkDetail->image) ? '' : 'd-none' }}"
                 id="image-preview"
                 src="{{ (isset($linkDetail) && $linkDetail->image) ? asset(\App\Models\AppLink::UPLOAD_PATH . $linkDetail->image) : '' }}"
                 style="object-fit: contain; max-height: 120px; border-radius: 8px; border: 1px solid #dee2e6; padding: 4px;"
                 width="120" height="120">
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <label for="description" class="form-label">Other / Description</label>
        <textarea class="form-control" name="description" id="description" rows="4"
                  placeholder="Additional notes or description">{{ old('description', $linkDetail->description ?? '') }}</textarea>
    </div>

    <div class="col-lg-12">
        <button type="submit" class="btn btn-primary">
            <i class="link-icon" data-feather="{{ isset($linkDetail) ? 'check' : 'plus' }}"></i>
            {{ isset($linkDetail) ? 'Update Link' : 'Create Link' }}
        </button>
        <a href="{{ route('admin.app-links.index') }}" class="btn btn-secondary ms-2">Cancel</a>
    </div>
</div>
