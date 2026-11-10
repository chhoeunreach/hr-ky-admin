<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RepairPrice\RepairDeviceSeries;
use App\Models\RepairPrice\RepairDeviceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RepairCatalogWebController extends Controller
{
    use \App\Http\Controllers\Web\Concerns\ValidatesRepairInput;

    public function storeType(Request $request)
    {
        return $this->saveType($request, new RepairDeviceType());
    }

    public function updateType(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403);
        return $this->saveType($request, RepairDeviceType::findOrFail($id));
    }

    private function saveType(Request $request, RepairDeviceType $type)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403);
        $request->merge(['slug' => $request->input('slug') ?: ($type->slug ?: (Str::slug($request->input('name', '')) ?: 'type-'.Str::lower(Str::random(8))))]);
        $data = $this->validateRepairInput($request, [
            'name' => ['required', 'string', 'max:255', Rule::unique('repair_device_types')->ignore($type->id)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('repair_device_types')->ignore($type->id)],
            'icon' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);
        $data['status'] = $request->boolean('status');
        $type->fill($data)->save();
        return redirect()->route('admin.repair-price.index', ['active_tab' => 'types'])->with('success', __('repair.type_saved'));
    }

    public function deleteType($id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403);
        $type = RepairDeviceType::findOrFail($id);
        if ($type->devices()->exists() || $type->series()->exists()) {
            return redirect()->back()->with('danger', __('repair.type_used'));
        }
        $type->delete();
        return redirect()->route('admin.repair-price.index', ['active_tab' => 'types'])->with('success', __('repair.type_deleted'));
    }

    public function storeSeries(Request $request)
    {
        return $this->saveSeries($request, new RepairDeviceSeries());
    }

    public function updateSeries(Request $request, $id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403);
        return $this->saveSeries($request, RepairDeviceSeries::findOrFail($id));
    }

    private function saveSeries(Request $request, RepairDeviceSeries $series)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403);
        $request->merge(['slug' => $request->input('slug') ?: ($series->slug ?: (Str::slug($request->input('name', '')) ?: 'series-'.Str::lower(Str::random(8))))]);
        $data = $this->validateRepairInput($request, [
            'repair_brand_id' => 'required|exists:repair_brands,id',
            'repair_device_type_id' => 'required|exists:repair_device_types,id',
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', Rule::unique('repair_device_series')
                ->where('repair_brand_id', $request->repair_brand_id)
                ->where('repair_device_type_id', $request->repair_device_type_id)->ignore($series->id)],
            'status' => 'nullable|boolean',
        ]);
        if ($series->exists && $series->devices()->exists()
            && ($series->repair_brand_id != $data['repair_brand_id'] || $series->repair_device_type_id != $data['repair_device_type_id'])) {
            return redirect()->back()->withInput()->withErrors(['repair_brand_id' => __('repair.series_reassign')]);
        }
        $data['status'] = $request->boolean('status');
        $series->fill($data)->save();
        return redirect()->route('admin.repair-price.index', ['active_tab' => 'series'])->with('success', __('repair.series_saved'));
    }

    public function deleteSeries($id)
    {
        abort_unless(Gate::allows('repair_price.manage_devices'), 403);
        $series = RepairDeviceSeries::findOrFail($id);
        if ($series->devices()->exists()) {
            return redirect()->back()->with('danger', __('repair.series_used'));
        }
        $series->delete();
        return redirect()->route('admin.repair-price.index', ['active_tab' => 'series'])->with('success', __('repair.series_deleted'));
    }
}
