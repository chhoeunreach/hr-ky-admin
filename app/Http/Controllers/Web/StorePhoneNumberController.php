<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\StorePhoneNumber;
use App\Models\User;
use App\Repositories\StorePhoneNumberRepository;
use App\Requests\StorePhoneNumber\StorePhoneNumberRequest;
use Exception;
use Illuminate\Http\Request;

class StorePhoneNumberController extends Controller
{
    private string $view = 'admin.storePhoneNumber.';

    public function __construct(
        protected StorePhoneNumberRepository $phoneRepo
    ) {}

    public function index(Request $request)
    {
        try {
            $filterParameters = [
                'search' => $request->search ?? null,
                'branch_id' => $request->branch_id ?? null,
                'operator' => $request->operator ?? null,
                'sim_slot' => $request->sim_slot ?? null,
                'purpose' => $request->purpose ?? null,
                'status' => $request->status ?? null,
                'assigned_to' => $request->assigned_to ?? null,
                'asset_id' => $request->asset_id ?? null,
                'is_assigned' => $request->is_assigned ?? null,
                'is_standalone' => $request->is_standalone ?? null,
                'per_page' => $request->per_page ?? 25,
                'sort' => $request->sort ?? null,
                'direction' => $request->direction ?? 'asc',
            ];

            $phoneNumbers = $this->phoneRepo->getAllStorePhoneNumbersPaginated($filterParameters, $filterParameters['per_page']);

            $stats = [
                'total' => StorePhoneNumber::count(),
                'active' => StorePhoneNumber::where('status', 'active')->count(),
                'inactive' => StorePhoneNumber::where('status', '!=', 'active')->count(),
                'assigned' => StorePhoneNumber::whereNotNull('assigned_to')->count(),
                'standalone' => StorePhoneNumber::whereNull('asset_id')->count(),
                'branches' => Branch::count(),
            ];

            $branches = Branch::select('id', 'name')->orderBy('name')->get();
            $employees = User::select('id', 'name')->orderBy('name')->get();
            $assets = Asset::select('id', 'name', 'asset_code')->orderBy('name')->get();
            $operators = StorePhoneNumber::getTranslatedOperators();
            $purposes = StorePhoneNumber::getTranslatedPurposes();
            $statuses = StorePhoneNumber::getTranslatedStatuses();
            $simSlots = StorePhoneNumber::getTranslatedSimSlots();
            $planTypes = StorePhoneNumber::getTranslatedPlanTypes();

            return view($this->view . 'index', compact(
                'phoneNumbers',
                'filterParameters',
                'stats',
                'branches',
                'employees',
                'assets',
                'operators',
                'purposes',
                'statuses',
                'simSlots',
                'planTypes'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        try {
            $branches = Branch::select('id', 'name')->orderBy('name')->get();
            $employees = User::select('id', 'name')->orderBy('name')->get();
            $assets = Asset::select('id', 'name', 'asset_code')->orderBy('name')->get();
            $operators = StorePhoneNumber::getTranslatedOperators();
            $purposes = StorePhoneNumber::getTranslatedPurposes();
            $statuses = StorePhoneNumber::getTranslatedStatuses();
            $simSlots = StorePhoneNumber::getTranslatedSimSlots();
            $planTypes = StorePhoneNumber::getTranslatedPlanTypes();

            return view($this->view . 'create', compact(
                'branches',
                'employees',
                'assets',
                'operators',
                'purposes',
                'statuses',
                'simSlots',
                'planTypes'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(StorePhoneNumberRequest $request)
    {
        try {
            $validatedData = $request->validated();
            if (!empty($validatedData['assigned_to']) && empty($validatedData['assigned_date'])) {
                $validatedData['assigned_date'] = now()->toDateString();
            }

            $this->phoneRepo->store($validatedData);

            return redirect()->route('admin.store-phone-numbers.index')
                ->with('success', __('message.sim_card_saved_successfully') ?? 'Store phone number registered successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $phoneNumber = $this->phoneRepo->findById($id);
            if (!$phoneNumber) {
                return redirect()->route('admin.store-phone-numbers.index')->with('danger', __('message.sim_card_not_found'));
            }

            $branches = Branch::select('id', 'name')->orderBy('name')->get();
            $employees = User::select('id', 'name')->orderBy('name')->get();
            $assets = Asset::select('id', 'name', 'asset_code')->orderBy('name')->get();
            $operators = StorePhoneNumber::getTranslatedOperators();
            $purposes = StorePhoneNumber::getTranslatedPurposes();
            $statuses = StorePhoneNumber::getTranslatedStatuses();
            $simSlots = StorePhoneNumber::getTranslatedSimSlots();
            $planTypes = StorePhoneNumber::getTranslatedPlanTypes();

            return view($this->view . 'edit', compact(
                'phoneNumber',
                'branches',
                'employees',
                'assets',
                'operators',
                'purposes',
                'statuses',
                'simSlots',
                'planTypes'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(StorePhoneNumberRequest $request, $id)
    {
        try {
            $phoneNumber = $this->phoneRepo->findById($id);
            if (!$phoneNumber) {
                return redirect()->route('admin.store-phone-numbers.index')->with('danger', __('message.sim_card_not_found'));
            }

            $validatedData = $request->validated();
            if (!empty($validatedData['assigned_to']) && empty($validatedData['assigned_date'])) {
                $validatedData['assigned_date'] = now()->toDateString();
            }

            $this->phoneRepo->update($phoneNumber, $validatedData);

            return redirect()->route('admin.store-phone-numbers.index')
                ->with('success', __('message.sim_card_updated_successfully') ?? 'Store phone number updated successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $phoneNumber = $this->phoneRepo->findById($id);
            if (!$phoneNumber) {
                return redirect()->route('admin.store-phone-numbers.index')->with('danger', __('message.sim_card_not_found'));
            }

            $this->phoneRepo->delete($phoneNumber);

            return redirect()->route('admin.store-phone-numbers.index')
                ->with('success', __('message.sim_card_deleted_successfully') ?? 'Store phone number deleted successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
