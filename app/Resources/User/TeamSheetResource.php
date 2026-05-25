<?php

namespace App\Resources\User;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class  TeamSheetResource extends JsonResource
{

    public function toArray($request)
    {
        $isAdmin = $this->hasAdminIdentity();

        return [
            'id' => $this->id ?? 0,
            'name' => $this->name ?? 'Admin',
            'username' => $this->username ?? 'admin',
            'email' => $this->email ?? '',
            'phone' => $this->phone ?? '',
            'department' => $this->department?->dept_name ?? '',
            'branch' => $this->branch?->name ?? '',
            'post' => $this->post?->post_name ?? '',
            'avatar' => ($this->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$this->avatar) : asset('assets/images/img.png'),
            'online_status' => (string) ((int) ($this->online_status ?? 0)),
            'role' => $this->mobileDirectoryRole(),
            'user_type' => $this->mobileDirectoryUserType(),
            'is_admin' => $isAdmin ? '1' : '0',
            'admin' => $isAdmin ? '1' : '0',
            'dob' => $this->dob ?? '',
            'gender' => $this->gender ?? '',
            'joining_date' => !is_null($this->joining_date) ? ($this->joining_date):'N/A',
        ];

    }

}
