<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Repositories\FeatureRepository;
use App\Services\Authorization\MobileFeatureControls;
use App\Traits\CustomAuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class FeatureController extends Controller
{
    use CustomAuthorizesRequests;

    public function __construct(
        private FeatureRepository $featureRepository,
        private MobileFeatureControls $controls,
    ) {}

    public function index()
    {
        $this->authorize('feature_control');
        $legacy = $this->featureRepository->getAllFeatures()->keyBy('key');
        $catalog = $this->controls->catalog();
        $statuses = $this->controls->statuses($legacy);
        $controlsReady = Schema::hasTable('mobile_feature_controls');

        foreach ($legacy as $key => $feature) {
            $catalog[$key] ??= ['name' => $feature->name, 'menus' => [$feature->group], 'actions' => []];
            $catalog[$key]['name'] = $feature->name;
        }
        ksort($catalog);

        return view('admin.feature.index', compact('catalog', 'statuses', 'controlsReady'));
    }

    public function updateStatus(Request $request)
    {
        $this->authorize('feature_control');
        $data = $request->validate([
            'key' => ['required', 'string', 'max:191'],
            'enabled' => ['required', 'boolean'],
        ]);
        $key = $data['key'];
        abort_unless(isset($this->controls->catalog()[$key]) || $this->featureRepository->findFeatureByKey($key), 404);
        abort_unless(Schema::hasTable('mobile_feature_controls'), 503);

        $this->controls->set($key, (bool) $data['enabled']);

        return redirect()->back()->with('success', __('message.status_changed'));
    }
}
