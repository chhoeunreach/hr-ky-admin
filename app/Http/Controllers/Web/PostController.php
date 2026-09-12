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

            return view($this->view . 'index', compact('posts',
                'filterParameters','companyDetail'));
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
            $with = [];
            $select = ['id', 'dept_name'];
            $departmentDetail = $this->departmentRepo->getAllActiveDepartments($with, $select);
            $with = ['branches:id,name'];
            $select = ['id', 'name'];
            $companyDetail = $this->companyRepository->getCompanyDetail($select, $with);
            $relatedPosts = Post::query()
                ->where('post_name', $postDetail->post_name)
                ->whereHas('branch', fn ($query) => $query->where('company_id', AppHelper::getAuthUserCompanyId()))
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
                throw new \Exception('Post Detail Not Found',404);
            }
            DB::beginTransaction();
            $oldPostName = $postDetail->post_name;
            $departments = Department::query()
                ->whereIn('id', collect($validatedData['dept_id'])->unique()->values()->all())
                ->get(['id', 'branch_id']);

            $departments->each(function (Department $department) use ($validatedData, $oldPostName, $postDetail) {
                $post = $department->id == $postDetail->dept_id
                    ? $postDetail
                    : Post::query()
                        ->where('dept_id', $department->id)
                        ->whereIn('post_name', [$oldPostName, $validatedData['post_name']])
                        ->first();

                if (!$post) {
                    $post = new Post();
                }

                $post->fill([
                    'post_name' => $validatedData['post_name'],
                    'branch_id' => $department->branch_id,
                    'dept_id' => $department->id,
                    'is_active' => $validatedData['is_active'] ?? Post::IS_ACTIVE,
                ]);
                $post->save();
            });
            DB::commit();
            return redirect()->route('admin.posts.index')->with('success', __('message.post_update'));
        }catch(\Exception $exception){
            return redirect()->back()->with('danger', $exception->getMessage())
                ->withInput();
        }

    }

    public function toggleStatus($id)
    {
        $this->authorize('edit_post');
        try {
            DB::beginTransaction();
            $this->postRepo->toggleStatus($id);
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
            if(!($postDetail->hasEmployee->isEmpty())){
                throw new Exception(__('message.post_delete_error'),400);
            }
            DB::beginTransaction();
                $this->postRepo->delete($postDetail);
            DB::commit();
            return redirect()->back()->with('success', __('message.post_delete'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

}
