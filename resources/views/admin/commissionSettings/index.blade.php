@extends('layouts.master')
@section('title', __('commission.title'))
@section('main-content')
<section class="content">
    @include('admin.section.flash_message')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4>{{ __('commission.title') }}</h4><p class="text-muted mb-0">{{ __('commission.help') }}</p></div>
        @can('view_sell_staff_report')<a href="{{ route('admin.sell-staff-report.index') }}" class="btn btn-outline-primary">{{ __('commission.reports') }}</a>@endcan
    </div>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('admin.commission-settings.update') }}">
        @csrf @method('PUT')
        <input type="hidden" name="revision" value="{{ old('revision', $revision) }}">
        <div class="card mb-3"><div class="card-body">
            <h5 class="mb-3">{{ __('commission.global') }}</h5>
            <div class="row g-3">
                @foreach(['enabled','visible','calculator_enabled','allow_mode_switch'] as $flag)
                    <div class="col-md-6"><input type="hidden" name="{{ $flag }}" value="0"><label class="form-check-label d-flex gap-2 align-items-center"><input class="form-check-input" type="checkbox" name="{{ $flag }}" value="1" @checked(old($flag, $configuration[$flag]))>{{ __('commission.'.$flag) }}</label></div>
                @endforeach
                <div class="col-md-6"><label class="form-label">{{ __('commission.default_mode') }}</label><select class="form-select" name="default_mode">@foreach(['with_conditions','without_conditions'] as $mode)<option value="{{ $mode }}" @selected(old('default_mode',$configuration['default_mode']) === $mode)>{{ __('commission.'.$mode) }}</option>@endforeach</select></div>
            </div>
        </div></div>
        <div class="card mb-3"><div class="card-body">
            <h5>{{ __('commission.service_rules') }}</h5><p class="text-muted small">{{ __('commission.conditions_help') }}</p>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr>@foreach(['service','enabled_short','rate','basis','require_phone','minimum_amount','part_time_quantity','full_time_quantity'] as $label)<th>{{ __('commission.'.$label) }}</th>@endforeach</tr></thead>
                <tbody>@foreach($configuration['rules'] as $type=>$rule)<tr>
                    <th>{{ __('commission.types.'.$type) }}</th>
                    <td><input type="hidden" name="rules[{{ $type }}][enabled]" value="0"><input aria-label="{{ __('commission.enabled_short').' '.__('commission.types.'.$type) }}" type="checkbox" name="rules[{{ $type }}][enabled]" value="1" @checked(old('rules.'.$type.'.enabled',$rule['enabled']))></td>
                    <td><input aria-label="{{ __('commission.rate').' '.__('commission.types.'.$type) }}" class="form-control" style="min-width:100px" type="number" min="0" max="1000000" step="0.0001" required name="rules[{{ $type }}][rate]" value="{{ old('rules.'.$type.'.rate',$rule['rate']) }}"></td>
                    <td><select aria-label="{{ __('commission.basis').' '.__('commission.types.'.$type) }}" class="form-select" style="min-width:130px" name="rules[{{ $type }}][basis]">@foreach(['item','invoice'] as $basis)<option value="{{ $basis }}" @selected(old('rules.'.$type.'.basis',$rule['basis']) === $basis)>{{ __('commission.'.$basis) }}</option>@endforeach</select></td>
                    <td><input type="hidden" name="rules[{{ $type }}][require_phone]" value="0"><input aria-label="{{ __('commission.require_phone').' '.__('commission.types.'.$type) }}" type="checkbox" name="rules[{{ $type }}][require_phone]" value="1" @checked(old('rules.'.$type.'.require_phone',$rule['require_phone']))></td>
                    @foreach(['minimum_amount','part_time_quantity','full_time_quantity'] as $field)<td><input aria-label="{{ __('commission.'.$field).' '.__('commission.types.'.$type) }}" class="form-control" style="min-width:110px" required type="number" min="0" max="1000000" step="{{ $field === 'minimum_amount' ? '0.0001' : '1' }}" name="rules[{{ $type }}][{{ $field }}]" value="{{ old('rules.'.$type.'.'.$field,$rule[$field]) }}"></td>@endforeach
                </tr>@endforeach</tbody>
            </table></div>
        </div></div>
        <div class="card mb-3"><div class="card-body">
            <h5>{{ __('commission.role_controls') }}</h5><p class="text-muted small">{{ __('commission.roles_help') }}</p>
            <div class="table-responsive"><table class="table"><thead><tr><th>{{ __('commission.role') }}</th>@foreach(['view','calculate','change_mode'] as $flag)<th>{{ __('commission.'.$flag) }}</th>@endforeach</tr></thead><tbody>
                @foreach($roles as $role)<tr><th>{{ $role->name }}</th>@foreach(['view','calculate','change_mode'] as $flag)<td><input type="hidden" name="roles[{{ $role->id }}][{{ $flag }}]" value="0"><input aria-label="{{ __('commission.'.$flag).' '.$role->name }}" type="checkbox" name="roles[{{ $role->id }}][{{ $flag }}]" value="1" @checked(old('roles.'.$role->id.'.'.$flag,$configuration['roles'][$role->id][$flag] ?? true))></td>@endforeach</tr>@endforeach
            </tbody></table></div>
        </div></div>
        <div class="alert alert-info">{{ __('commission.history_help') }}</div>
        <button class="btn btn-primary mb-4" type="submit">{{ __('commission.save') }}</button>
    </form>
    <div class="card"><div class="card-body"><h5>{{ __('commission.recent_changes') }}</h5><div class="table-responsive"><table class="table"><thead><tr><th>{{ __('commission.revision') }}</th><th>{{ __('commission.actor') }}</th><th>{{ __('commission.date') }}</th></tr></thead><tbody>@forelse($history as $change)<tr><td>{{ $change->revision }}</td><td>{{ $change->actor }}</td><td>{{ $change->created_at }}</td></tr>@empty<tr><td colspan="3">{{ __('commission.no_changes') }}</td></tr>@endforelse</tbody></table></div></div></div>
</section>
@endsection
