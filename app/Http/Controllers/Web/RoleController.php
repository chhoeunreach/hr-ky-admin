<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use App\Requests\Role\RoleRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class  RoleController extends Controller
{
    use CustomAuthorizesRequests;
    private $view = 'admin.role.';

    private RoleRepository $roleRepo;
    private UserRepository $userRepo;

    public function __construct(RoleRepository $roleRepo, UserRepository $userRepo)
    {
        $this->roleRepo = $roleRepo;
        $this->userRepo = $userRepo;
    }

    /**
     * @throws AuthorizationException
     */
    public function index()
    {
        $this->authorize('role_permission');
        try {
            $roles = $this->roleRepo->getAllUserRoles();
            return view($this->view . 'index', compact('roles'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function create()
    {
        $this->authorize('role_permission');
        try {
            return view($this->view . 'create');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function store(RoleRequest $request)
    {
        $this->authorize('role_permission');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $this->roleRepo->store($validatedData);
            DB::commit();
            Artisan::call('cache:clear');
            return redirect()->route('admin.roles.index')->with('success', __('message.add_role'));
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function edit($id)
    {
        $this->authorize('role_permission');
        try {
            $roleDetail = $this->roleRepo->getRoleById($id);
            if (!$roleDetail) {
                throw new Exception(__('message.role_not_found'), 204);
            }
            if ($roleDetail->slug == 'admin') {
                throw new Exception(__('message.admin_role_delete_error'), 402);
            }
            return view($this->view . 'edit', compact('roleDetail'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function update(RoleRequest $request, $id)
    {
        $this->authorize('role_permission');
        try {
            $validatedData = $request->validated();
            $roleDetail = $this->roleRepo->getRoleById($id);
            if (!$roleDetail) {
                throw new Exception(__('message.role_not_found'), 404);
            }
            DB::beginTransaction();
            $this->roleRepo->update($roleDetail, $validatedData);
            DB::commit();
            Artisan::call('cache:clear');
            return redirect()->route('admin.roles.index')->with('success', __('message.role_update'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }

    }

    /**
     * @throws AuthorizationException
     */
    public function toggleStatus($id)
    {
        $this->authorize('role_permission');
        try {
            DB::beginTransaction();
                $this->roleRepo->toggleStatus($id);
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function delete($id)
    {
        $this->authorize('role_permission');
        try {
            $roleDetail = $this->roleRepo->getRoleById($id);
            if (!$roleDetail) {
                throw new Exception(__('message.role_not_found'), 404);
            }
            if ($roleDetail->slug == 'admin') {
                throw new Exception(__('message.admin_role_delete_error'), 402);
            }
            $user = $this->userRepo->findUserDetailByRole($id);
            {
                if ($user) {
                    throw new Exception(__('message.assign_role_delete_error'), 402);
                }
            }
            DB::beginTransaction();
            $this->roleRepo->delete($roleDetail);
            DB::commit();
            Artisan::call('cache:clear');
            return redirect()->back()->with('success', __('message.role_delete'));
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function createPermission($roleId): Factory|View|RedirectResponse|Application
    {
        $this->authorize('role_permission');
        try {
            $selectPermissionGroup = ['*'];
            $selectRole = ['id', 'name', 'slug'];
            $withPermissionType = ['permissionGroups','permissionGroups.getPermission'];
            $withRole = ['permission'];
            $permissionGroupTypeList = $this->roleRepo->getPermissionGroupTypeDetails($selectPermissionGroup, $withPermissionType);
            $role = $this->roleRepo->getRoleById($roleId, $selectRole, $withRole);
            $allRoles = $this->roleRepo->getAllRolesExceptAdmin();
            if (!$role) {
                throw new Exception(__('message.role_not_found'), 404);
            }

            $isEdit = false;
            $role_permission = [];
            if ($role->getRolePermission->count() > 0) {
                $role_permission = $role->getRolePermission->pluck('permission_id')->toArray();
                $isEdit = true;
            }
            $permissionCatalog = $permissionGroupTypeList
                ->flatMap(fn ($type) => $type->permissionGroups)
                ->flatMap(fn ($group) => $group->getPermission)
                ->keyBy('permission_key');
            $grantedPermissionIds = array_fill_keys(array_map('intval', $role_permission), true);
            $grantedKeys = $permissionCatalog->filter(
                fn ($permission) => isset($grantedPermissionIds[(int) $permission->id])
            )->keys()->flip();
            $mobileStates = app(\App\Services\Authorization\MobileFeatureControls::class)->statuses();
            $functionsByMenu = [];
            foreach (config('mobile_permissions.functions', []) as $rule) {
                if (is_array($rule) && isset($rule[0], $rule[1])) {
                    $functionsByMenu[$rule[0]][$rule[1]] = true;
                }
            }
            $mobileMenuOptions = [];
            $mobileMenuPermissionOptions = [];
            foreach (config('mobile_permissions.menus', []) as $menu => $ability) {
                if (!$mobileStates->get($menu, true)) {
                    continue;
                }
                $uniqueFunctionAbilities = [];
                foreach (array_keys($functionsByMenu[$menu] ?? []) as $functionAbility) {
                    $menuCount = collect(config('mobile_permissions.functions', []))
                        ->filter(fn ($rule) => is_array($rule) && ($rule[1] ?? null) === $functionAbility)
                        ->pluck(0)->unique()->count();
                    if ($menuCount === 1) {
                        $uniqueFunctionAbilities[] = $functionAbility;
                    }
                }
                $hasExplicitMenuPermission = $permissionCatalog->has($ability);
                $allowedByMenu = $grantedKeys->has($ability);
                if (!$hasExplicitMenuPermission) {
                    foreach ($uniqueFunctionAbilities as $functionAbility) {
                        if ($grantedKeys->has($functionAbility)) {
                            $allowedByMenu = true;
                            break;
                        }
                    }
                }
                $mobileMenuPermissionOptions[$menu] = [
                    'name' => $permissionCatalog->get($ability)->name
                        ?? ucwords(str_replace('-', ' ', $menu)),
                    'ability' => $ability,
                    'explicit' => $hasExplicitMenuPermission,
                    'fallback_abilities' => $uniqueFunctionAbilities,
                ];
                if ($allowedByMenu) {
                    $mobileMenuOptions[$menu] = $mobileMenuPermissionOptions[$menu]['name'];
                }
            }
            $mobileLayout = app(\App\Services\Authorization\MobileRoleLayout::class)->forRole((int) $role->id);
            return view($this->view . 'permission', compact('permissionGroupTypeList',
                'role',
                'role_permission', 'isEdit','allRoles', 'mobileMenuOptions',
                'mobileMenuPermissionOptions', 'mobileLayout'
            ));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    /**
     * @throws AuthorizationException
     */
    public function assignPermissionToRole(Request $request, $roleId): RedirectResponse
    {
        $this->authorize('role_permission');
        try {
            $data = $request->all();
            $role = $this->roleRepo->getRoleById($roleId);
            $validatedPermissionData = $data['permission_value'] ?? [];
            DB::beginTransaction();
            $this->roleRepo->syncPermissionToRole($role, $validatedPermissionData);
            $permittedKeys = DB::table('permission_roles')
                ->join('permissions', 'permissions.id', '=', 'permission_roles.permission_id')
                ->where('permission_roles.role_id', $roleId)
                ->pluck('permissions.permission_key')->flip();
            $allowedMenus = [];
            foreach (config('mobile_permissions.menus', []) as $menu => $ability) {
                if ($permittedKeys->has($ability)) {
                    $allowedMenus[] = $menu;
                    continue;
                }
                foreach (config('mobile_permissions.functions', []) as $rule) {
                    if (!is_array($rule) || ($rule[0] ?? null) !== $menu || !$permittedKeys->has($rule[1] ?? '')) {
                        continue;
                    }
                    $mappedMenus = collect(config('mobile_permissions.functions', []))
                        ->filter(fn ($candidate) => is_array($candidate) && ($candidate[1] ?? null) === $rule[1])
                        ->pluck(0)->unique()->count();
                    if ($mappedMenus === 1) {
                        $allowedMenus[] = $menu;
                        break;
                    }
                }
            }
            $placements = array_intersect_key(
                (array) $request->input('mobile_layout', []),
                array_flip($allowedMenus)
            );
            $orders = array_map(
                fn ($menus) => array_values(array_intersect((array) $menus, $allowedMenus)),
                (array) $request->input('mobile_layout_order', [])
            );
            $mobileLayoutSaved = app(\App\Services\Authorization\MobileRoleLayout::class)
                ->save((int) $roleId, $placements, $orders);
            DB::commit();
            $redirect = redirect()->back()->with('success', __('message.permission_update'));
            if (!$mobileLayoutSaved) {
                $redirect->with('info', 'Role permissions were saved. Run the role mobile layout migration to store the function placements.');
            }
            return $redirect;
        } catch (Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
