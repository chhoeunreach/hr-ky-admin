<?php

namespace App\Repositories;


use App\Models\Post;
use Illuminate\Support\Facades\DB;

class PostRepository
{

    public function getAllDepartmentPosts($filterParameters,$with=[],$select=['*'])
    {
        $allowedPerPage = [10, 25, 50, 100, 200, 500, 1000];
        $requestedPerPage = $filterParameters['per_page'] ?? 25;

        $baseQuery = Post::query()
            ->when(!empty($filterParameters['name']), function ($query) use ($filterParameters) {
                $query->where('post_name', 'like', '%' . $filterParameters['name'] . '%');
            })
            ->when(!empty($filterParameters['search']), function ($query) use ($filterParameters) {
                $search = '%' . $filterParameters['search'] . '%';
                $query->where(function ($query) use ($search) {
                    $query->where('post_name', 'like', $search)
                        ->orWhereHas('department', function ($departmentQuery) use ($search) {
                            $departmentQuery->where('dept_name', 'like', $search);
                        })
                        ->orWhereHas('branch', function ($branchQuery) use ($search) {
                            $branchQuery->where('name', 'like', $search);
                        });
                });
            })
            ->when(!empty($filterParameters['branch_id']), function ($query) use ($filterParameters) {
                $query->whereIn('branch_id', $filterParameters['branch_id']);
            })
           ->when(!empty($filterParameters['department_id']), function ($query) use ($filterParameters) {
                $query->whereIn('dept_id', $filterParameters['department_id']);
            })
            ->when(($filterParameters['is_active'] ?? '') !== '' && $filterParameters['is_active'] !== null, function ($query) use ($filterParameters) {
                $query->where('is_active', $filterParameters['is_active']);
            });

        // Group by post_name so that posts across branches/departments are consolidated
        $nameQuery = (clone $baseQuery)
            ->select('post_name', DB::raw('MAX(id) as max_id'))
            ->groupBy('post_name')
            ->orderByDesc('max_id');

        if ($requestedPerPage === 'all') {
            $totalRecord = (clone $nameQuery)->get()->count();
            $paginatedNames = $nameQuery->paginate($totalRecord > 0 ? $totalRecord : 1);
        } else {
            $perPage = in_array((int) $requestedPerPage, $allowedPerPage, true)
                ? (int) $requestedPerPage
                : 25;
            $paginatedNames = $nameQuery->paginate($perPage);
        }

        $targetNames = $paginatedNames->pluck('post_name')->all();
        $postsGrouped = Post::with(['branch:id,name', 'department:id,dept_name', 'employees:id,name,post_id,avatar'])
            ->whereIn('post_name', $targetNames)
            ->get()
            ->groupBy('post_name');

        $items = $paginatedNames->getCollection()->map(function ($row) use ($postsGrouped) {
            $group = $postsGrouped->get($row->post_name, collect());
            $first = $group->first() ?? new Post();

            $mergedEmployees = $group->flatMap->employees->unique('id')->values();
            $branches = $group->pluck('branch')->filter()->unique('id')->values();
            $departments = $group->pluck('department')->filter()->unique('id')->values();
            $isActive = $group->contains(fn ($p) => (int)$p->is_active === 1) ? 1 : 0;

            $post = clone $first;
            $post->id = $row->max_id;
            $post->post_name = $row->post_name;
            $post->setRelation('branch', $branches->first());
            $post->setRelation('branches', $branches);
            $post->setRelation('department', $departments->first());
            $post->setRelation('departments', $departments);
            $post->setRelation('employees', $mergedEmployees);
            $post->employees_count = $mergedEmployees->count();
            $post->is_active = $isActive;

            return $post;
        });

        return $paginatedNames->setCollection($items);
    }

    public function store($validatedData)
    {
        return Post::create($validatedData)->fresh();
    }

    public function getPostById($id)
    {
        return Post::where('id',$id)->first();
    }

    public function delete($postDetail)
    {
        return $postDetail->delete();
    }

    public function update($postDetail,$validatedData)
    {
        return $postDetail->update($validatedData);
    }

    public function toggleStatus($id)
    {
        $postDetail = Post::where('id',$id)->first();
        return $postDetail->update([
            'is_active' => !$postDetail->is_active,
        ]);
    }

    public function getAllActivePostsByDepartmentId($deptId,$with=[],$select=['*'])
    {
        return Post::with($with)
            ->select($select)
            ->where('is_active',1)
            ->where('dept_id',$deptId)
            ->get();
    }
}
