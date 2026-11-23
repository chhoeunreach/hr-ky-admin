<div class="row">
    <div class="col-lg-6 col-md-6 mb-3">
        <label for="phone_number" class="form-label">{{ __('index.phone_number') }} <span class="text-danger">*</span></label>
        <input type="tel" class="form-control @error('phone_number') is-invalid @enderror"
               id="phone_number" name="phone_number" required maxlength="50" autocomplete="off"
               value="{{ old('phone_number', $phoneNumber->phone_number ?? '') }}" placeholder="012 345 678 / 098 765 432">
        @error('phone_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <label for="operator" class="form-label">{{ __('index.operator') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('operator') is-invalid @enderror" id="operator" name="operator" required>
            @foreach($operators as $key => $label)
                <option value="{{ $key }}" @selected(old('operator', $phoneNumber->operator ?? 'smart') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('operator')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <label for="sim_slot" class="form-label">{{ __('index.sim_slot') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('sim_slot') is-invalid @enderror" id="sim_slot" name="sim_slot" required>
            @foreach($simSlots as $key => $label)
                <option value="{{ $key }}" @selected(old('sim_slot', $phoneNumber->sim_slot ?? 'sim_1') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('sim_slot')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="purpose" class="form-label">{{ __('index.purpose') }}</label>
        <select class="form-select @error('purpose') is-invalid @enderror" id="purpose" name="purpose">
            <option value="">-- {{ __('index.select_purpose') }} --</option>
            @foreach($purposes as $key => $label)
                <option value="{{ $key }}" @selected(old('purpose', $phoneNumber->purpose ?? '') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="plan_type" class="form-label">{{ __('index.plan_type') }}</label>
        <select class="form-select @error('plan_type') is-invalid @enderror" id="plan_type" name="plan_type">
            @foreach($planTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('plan_type', $phoneNumber->plan_type ?? 'prepaid') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('plan_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="serial_number" class="form-label">{{ __('index.sim_serial_number') }}</label>
        <input type="text" class="form-control @error('serial_number') is-invalid @enderror"
               id="serial_number" name="serial_number" maxlength="100" autocomplete="off"
               value="{{ old('serial_number', $phoneNumber->serial_number ?? '') }}" placeholder="89855...">
        @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="branch_id" class="form-label">{{ __('index.branch') }}</label>
        <select class="form-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
            <option value="">-- {{ __('index.select_branch') }} --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}" @selected((string) old('branch_id', $phoneNumber->branch_id ?? '') === (string) $branch->id)>
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>
        @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="asset_id" class="form-label">{{ __('index.assigned_device') }}</label>
        <select class="form-select @error('asset_id') is-invalid @enderror" id="asset_id" name="asset_id">
            <option value="">-- {{ __('index.standalone_no_device') }} --</option>
            @foreach($assets as $asset)
                <option value="{{ $asset->id }}" @selected((string) old('asset_id', $phoneNumber->asset_id ?? '') === (string) $asset->id)>
                    {{ $asset->name }} ({{ $asset->asset_code ?: __('index.no_code') }})
                </option>
            @endforeach
        </select>
        <div class="form-text small">{{ __('index.link_sim_to_device_note') }}</div>
        @error('asset_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="assigned_to" class="form-label">{{ __('index.custodian_assigned_staff') }}</label>
        <select class="form-select @error('assigned_to') is-invalid @enderror" id="assigned_to" name="assigned_to">
            <option value="">-- {{ __('index.not_assigned') }} --</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}" @selected((string) old('assigned_to', $phoneNumber->assigned_to ?? '') === (string) $emp->id)>
                    {{ $emp->name }}
                </option>
            @endforeach
        </select>
        <div class="form-text small">{{ __('index.independent_sim_custodian_note') }}</div>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="assigned_date" class="form-label">{{ __('index.assigned_date') }}</label>
        <input type="date" class="form-control @error('assigned_date') is-invalid @enderror"
               id="assigned_date" name="assigned_date"
               value="{{ old('assigned_date', isset($phoneNumber->assigned_date) ? $phoneNumber->assigned_date->format('Y-m-d') : date('Y-m-d')) }}">
        @error('assigned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-lg-4 col-md-6 mb-3">
        <label for="status" class="form-label">{{ __('index.status') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach($statuses as $key => $label)
                <option value="{{ $key }}" @selected(old('status', $phoneNumber->status ?? 'active') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 mb-3">
        <label for="notes" class="form-label">{{ __('index.notes_remarks') }}</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3"
                  placeholder="{{ __('index.phone_notes_placeholder') }}">{{ old('notes', $phoneNumber->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
