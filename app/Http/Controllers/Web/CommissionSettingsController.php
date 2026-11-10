<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\SellOut\CommissionSettingsService;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionSettingsController extends Controller
{
    use CustomAuthorizesRequests;

    public function index(CommissionSettingsService $settings)
    {
        $this->authorize('manage_commission_settings');
        $record = DB::table('commission_settings')->where('id',1)->first();
        abort_unless($record,503,'Apply the commission settings migration first.');
        return view('admin.commissionSettings.index',[
            'configuration'=>$settings->configuration(), 'revision'=>$record->revision,
            'roles'=>Role::query()->orderBy('name')->get(['id','name','slug']),
            'history'=>DB::table('commission_setting_changes')->latest('id')->limit(10)->get(),
        ]);
    }

    public function update(Request $request, CommissionSettingsService $settings)
    {
        $this->authorize('manage_commission_settings');
        $types = array_keys(CommissionSettingsService::defaults()['rules']);
        $validation = [
            'revision'=>['required','integer','min:0'],
            'enabled'=>['required','boolean'],'visible'=>['required','boolean'],
            'calculator_enabled'=>['required','boolean'],'allow_mode_switch'=>['required','boolean'],
            'default_mode'=>['required','in:with_conditions,without_conditions'],
            'rules'=>['required','array:'.implode(',',$types)], 'roles'=>['nullable','array'],
        ];
        foreach ($types as $type) {
            $prefix = 'rules.'.$type;
            $validation[$prefix] = ['required','array:enabled,rate,basis,require_phone,minimum_amount,part_time_quantity,full_time_quantity'];
            foreach (['enabled','require_phone'] as $key) $validation[$prefix.'.'.$key] = ['required','boolean'];
            foreach (['rate','minimum_amount'] as $key) $validation[$prefix.'.'.$key] = ['required','numeric','min:0','max:1000000'];
            foreach (['part_time_quantity','full_time_quantity'] as $key) $validation[$prefix.'.'.$key] = ['required','integer','min:0','max:1000000'];
            $validation[$prefix.'.basis'] = ['required','in:item,invoice'];
        }
        $roleIds = Role::query()->pluck('id')->map(fn ($id)=>(string)$id)->all();
        foreach ((array)$request->input('roles',[]) as $id=>$role) {
            abort_unless(in_array((string)$id,$roleIds,true),422,'Unknown role.');
            $validation['roles.'.$id] = ['required','array:view,calculate,change_mode'];
            foreach (['view','calculate','change_mode'] as $flag) $validation['roles.'.$id.'.'.$flag] = ['required','boolean'];
        }
        $data = $request->validate($validation);
        $actor = Auth::guard('admin')->check() ? 'admin:'.Auth::guard('admin')->id() : 'user:'.Auth::id();
        DB::transaction(function () use ($data,$actor) {
            $row = DB::table('commission_settings')->where('id',1)->lockForUpdate()->first();
            abort_unless($row,503,'Apply the commission settings migration first.');
            abort_if((int)$row->revision !== (int)$data['revision'],409,'Commission settings changed. Reload this page before saving again.');
            $configuration = array_diff_key($data,['revision'=>true]);
            foreach (['enabled','visible','calculator_enabled','allow_mode_switch'] as $key) $configuration[$key] = (bool)$configuration[$key];
            foreach ($configuration['rules'] as &$rule) {
                $rule['enabled']=(bool)$rule['enabled']; $rule['require_phone']=(bool)$rule['require_phone'];
                foreach (['rate','minimum_amount'] as $key) $rule[$key]=round((float)$rule[$key],4);
                foreach (['part_time_quantity','full_time_quantity'] as $key) $rule[$key]=(int)$rule[$key];
            }
            unset($rule);
            foreach ($configuration['roles'] ?? [] as $id=>$role) foreach ($role as $flag=>$value) $configuration['roles'][$id][$flag]=(bool)$value;
            $json = json_encode($configuration,JSON_THROW_ON_ERROR);
            DB::table('commission_settings')->where('id',1)->update(['configuration'=>$json,'revision'=>$row->revision+1,'updated_by'=>$actor,'updated_at'=>now()]);
            DB::table('commission_setting_changes')->insert(['revision'=>$row->revision+1,'before'=>$row->configuration,'after'=>$json,'actor'=>$actor,'created_at'=>now()]);
        });
        $settings->refresh();
        return redirect()->route('admin.commission-settings.index')->with('success',__('commission.saved'));
    }
}
