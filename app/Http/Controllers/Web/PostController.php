<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Post;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\PostRepository;
use App\Requests\Post\PostRequest;
use App\Traits\CustomAuthorizesRequests;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    use CustomAuthorizesRequests;
    private $view = 'admin.post.';


    public function __construct(protected PostRepository $postRepo, protected DepartmentRepository $departmentRepo,  protected CompanyRepository $companyRepository)
    {}


    public function index(Request $request)
    {
        $this->authorize('list_post');
        try {
            $branchIds = $this->resolveIdList($request->query('branch_id', []));
            $departmentIds = $this->resolveIdList($request->query('department_id', []));

            $filterParameters = [
                'name' =>  $request->name ?? null,
                'search' => $request->search ?? null,
                'branch_id' => $branchIds,
                'department_id' => $departmentIds,
                'is_active' => $request->is_active ?? null,
                'per_page' => $request->per_page ?? 25,
            ];
            if(!auth('admin')->check() && auth()->check()){
                $filterParameters['branch_id'] = [auth()->user()->branch_id];
            }
            $postSelect = ['*'];
            $with = ['branch:id,name', 'department:id,dept_name','employees:id,name,post_id,avatar'];

            $posts = $this->postRepo->getAllDepartmentPosts($filterParameters,$with,$postSelect);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            $companyId = AppHelper::getAuthUserCompanyId();
            $basePostQuery = Post::query()->whereHas('branch', fn ($q) => $q->where('company_id', $companyId));
            $totalDistinctPosts = (clone $basePostQuery)->distinct()->count('post_name');
            $activePostsCount = (clone $basePostQuery)->where('is_active', 1)->distinct()->count('post_name');
            $totalEmployeesInPosts = (clone $basePostQuery)->withCount('employees')->get()->sum('employees_count');
            $coveredBranchesCount = (clone $basePostQuery)->distinct()->count('branch_id');

            $stats = [
                'total_posts' => $totalDistinctPosts,
                'active_posts' => $activePostsCount,
                'inactive_posts' => max(0, $totalDistinctPosts - $activePostsCount),
                'total_employees' => $totalEmployeesInPosts,
                'total_branches' => $coveredBranchesCount,
            ];

            return view($this->view . 'index', compact('posts',
                'filterParameters','companyDetail', 'stats'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    private function resolveIdList($value): array
    {
        return collect(is_array($value) ? $value : [$value])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function create()
    {
        $this->authorize('create_post');
        try {
            $with = [];
            $select = ['id', 'dept_name'];
            $departmentDetail = $this->departmentRepo->getAllActiveDepartments($with, $select);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);

            return view($this->view . 'create', compact('departmentDetail','companyDetail'));
        } catch (\Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function getAllPostsByBranchId($deptId)
    {
        try {
            $with = [];
            $select = ['post_name', 'id'];
            $posts = $this->postRepo->getAllActivePostsByDepartmentId($deptId,$with,$select);
            return response()->json([
                'data' => $posts
            ]);
        } catch (Exception $exception) {
            return AppHelper::sendErrorResponse($exception->getMessage(),$exception->getCode());;
        }
    }

    public function store(PostRequest $request)
    {
        $this->authorize('create_post');
        try {
            $validatedData = $request->validated();
            DB::beginTransaction();
            $departments = Department::query()
                ->whereIn('id', collect($validatedData['dept_id'])->unique()->values()->all())
                ->get(['id', 'branch_id']);

            $departments->each(function (Department $department) use ($validatedData) {
                Post::firstOrCreate(
                    [
                        'post_name' => $validatedData['post_name'],
                        'branch_id' => $department->branch_id,
                        'dept_id' => $department->id,
                    ],
                    [
                        'is_active' => $validatedData['is_active'] ?? Post::IS_ACTIVE,
                    ]
                );
            });
            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', __('message.post_add'));
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('danger', $e->getMessage())
                ->withInput();
        }
    }

    public function edit($id)
    {
        $this->authorize('edit_post');
        try{
            $postDetail = $this->postRepo->getPostById($id);
            if (!$postDetail) {
                return redirect()->route('admin.posts.index')->with('danger', __('message.post_not_found'));
            }
            $with = [];
            $select = ['id', 'dept_name'];
            $departmentDetail = $this->departmentRepo->getAllActiveDepartments($with, $select);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $companyId = AppHelper::getAuthUserCompanyId();
            $relatedPosts = Post::withoutGlobalScopes()
                ->where('post_name', $postDetail->post_name)
                ->where(function ($query) use ($companyId) {
                    $query->whereHas('branch', fn ($b) => $b->where('company_id', $companyId))
                          ->orWhereHas('department', fn ($d) => $d->where('company_id', $companyId))
                          ->orWhereNull('branch_id');
                })
                ->get(['id', 'branch_id', 'dept_id']);
            $selectedBranchIds = $relatedPosts->pluck('branch_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();
            $selectedDepartmentIds = $relatedPosts->pluck('dept_id')->filter()->map(fn ($id) => (string) $id)->unique()->values()->all();

            return view($this->view.'edit',
                compact('postDetail','departmentDetail','companyDetail', 'selectedBranchIds', 'selectedDepartmentIds')
            );
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(PostRequest $request, $id)
    {
        $this->authorize('edit_post');
        try{
            $validatedData = $request->validated();
            $postDetail = $this->postRepo->getPostById($id);
            if(!$postDetail){
                throw new \Exception(__('message.post_not_found'), 404);
            }
            DB::beginTransaction();
            $oldPostName = $postDetail->post_name;
            $newPostName = $validatedData['post_name'];
            $targetDeptIds = collect($validatedData['dept_id'])->unique()->values()->all();

            $departments = Department::query()
                ->whereIn('id', $targetDeptIds)
                ->get(['id', 'branch_id']);

            $companyId = AppHelper::getAuthUserCompanyId();
            $existingPosts = Post::withoutGlobalScopes()
                ->where('post_name', $oldPostName)
                ->where(function ($query) use ($companyId) {
                    $query->whereHas('branch', fn ($b) => $b->where('company_id', $companyId))
                          ->orWhereHas('department', fn ($d) => $d->where('company_id', $companyId))
                          ->orWhereNull('branch_id');
                })
                ->with('hasEmployee')
                ->get();

            $departments->each(function (Department $department) use ($validatedData, $oldPostName, $newPostName) {
                $post = Post::withoutGlobalScopes()
                    ->where('dept_id', $department->id)
                    ->whereIn('post_name', [$oldPostName, $newPostName])
                    ->first();

                if (!$post) {
                    $post = new Post();
                }

                $post->fill([
                    'post_name' => $newPostName,
                    'branch_id' => $department->branch_id,
                    'dept_id' => $department->id,
                    'is_active' => $validatedData['is_active'] ?? Post::IS_ACTIVE,
                ]);
                $post->save();
            });

            foreach ($existingPosts as $existing) {
                if (!in_array($existing->dept_id, $targetDeptIds)) {
                    if ($existing->hasEmployee->isEmpty()) {
                        $this->postRepo->delete($existing);
                    }
                }
            }

            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', __('message.post_update'));
        }catch(\Exception $exception){
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_post');
        try {
            DB::beginTransaction();
            $post = Post::withoutGlobalScopes()->find($id);
            if ($post) {
                $newStatus = ((int)$post->is_active === 1) ? 0 : 1;
                $companyId = AppHelper::getAuthUserCompanyId();
                Post::withoutGlobalScopes()
                    ->where('post_name', $post->post_name)
                    ->where(function ($q) use ($companyId) {
                        $q->whereHas('branch', fn ($b) => $b->where('company_id', $companyId))
                          ->orWhereHas('department', fn ($d) => $d->where('company_id', $companyId))
                          ->orWhereNull('branch_id');
                    })
                    ->update(['is_active' => $newStatus]);
            } else {
                $this->postRepo->toggleStatus($id);
            }
            DB::commit();
            return redirect()->back()->with('success', __('message.status_changed'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function delete($id)
    {
        $this->authorize('delete_post');
        try {
            $postDetail = $this->postRepo->getPostById($id);
            if (!$postDetail) {
                throw new \Exception(__('message.post_not_found'), 404);
            }

            $companyId = AppHelper::getAuthUserCompanyId();
            $allRelated = Post::withoutGlobalScopes()
                ->where('post_name', $postDetail->post_name)
                ->where(function ($q) use ($companyId) {
                    $q->whereHas('branch', fn ($b) => $b->where('company_id', $companyId))
                      ->orWhereHas('department', fn ($d) => $d->where('company_id', $companyId))
                      ->orWhereNull('branch_id');
                })
                ->with(['hasEmployee', 'employees'])
                ->get();

            if ($allRelated->isEmpty()) {
                $allRelated = collect([$postDetail]);
            }

            $assignedEmployeesCount = $allRelated->sum(function ($item) {
                return $item->hasEmployee ? $item->hasEmployee->count() : 0;
            });

            if ($assignedEmployeesCount > 0) {
                $errorMsg = __('message.post_delete_error');
                if (empty($errorMsg) || $errorMsg === 'message.post_delete_error') {
                    $errorMsg = "Post with assigned employees cannot be deleted.";
                }
                throw new Exception($errorMsg . " ({$assignedEmployeesCount} " . ($assignedEmployeesCount === 1 ? 'employee' : 'employees') . " assigned)", 400);
            }

            DB::beginTransaction();
            foreach ($allRelated as $item) {
                $this->postRepo->delete($item);
            }
            DB::commit();
            return redirect()->back()->with('success', __('message.post_delete'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
