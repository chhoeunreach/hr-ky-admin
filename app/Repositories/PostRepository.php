<?php

namespace App\Repositories;


use App\Models\Post;

class PostRepository
{

    public function getAllDepartmentPosts($filterParameters,$with=[],$select=['*'])
    {
        $allowedPerPage = [10, 25, 50, 100, 200, 500, 1000];
        $requestedPerPage = $filterParameters['per_page'] ?? 25;

        $query = Post::select($select)
            ->with($with)
            ->withCount('employees')
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
            })
            ->latest();

        if ($requestedPerPage === 'all') {
            $totalRecord = (clone $query)->count();
            return $query->paginate($totalRecord > 0 ? $totalRecord : 1);
        }

        $perPage = in_array((int) $requestedPerPage, $allowedPerPage, true)
            ? (int) $requestedPerPage
            : 25;

        return $query->paginate($perPage);
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
