<?php

namespace Tests\Feature;

use App\Models\DCardEmployee;
use App\Models\User;
use App\Support\EmployeeAvatar;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EmployeeAvatarPhotoTest extends TestCase
{
    public function test_d_card_profile_photo_takes_precedence_over_user_avatar(): void
    {
        $user = new User([
            'employee_code' => 'KY-00021',
            'avatar' => 'avatar-from-user.png',
        ]);
        $overrides = collect([
            new DCardEmployee([
                'employee_code' => 'KY-00021',
                'name_khmer' => 'រេច',
                'profile_photo_url' => 'https://example.test/d-card/reach.png',
            ]),
        ])->keyBy('employee_code');

        $this->assertSame(
            'https://example.test/d-card/reach.png',
            EmployeeAvatar::url($user, $overrides)
        );
    }

    public function test_generated_employee_code_matches_d_card_photo(): void
    {
        $user = (new User(['avatar' => 'avatar-from-user.png']))->forceFill(['id' => 21]);
        $overrides = collect([
            new DCardEmployee([
                'employee_code' => 'EMP-0021',
                'name_khmer' => 'រេច',
                'profile_photo_url' => 'https://example.test/d-card/generated.png',
            ]),
        ])->keyBy('employee_code');

        $this->assertSame(
            'https://example.test/d-card/generated.png',
            EmployeeAvatar::url($user, $overrides)
        );
    }

    public function test_user_avatar_is_used_when_there_is_no_d_card_photo(): void
    {
        $user = new User([
            'employee_code' => 'KY-00021',
            'avatar' => 'avatar-from-user.png',
        ]);

        $this->assertSame(
            asset(User::AVATAR_UPLOAD_PATH . 'avatar-from-user.png'),
            EmployeeAvatar::url($user, new Collection())
        );
    }

    public function test_default_photo_is_used_when_both_sources_are_empty(): void
    {
        $user = new User(['employee_code' => 'KY-00021']);

        $this->assertSame(
            asset('assets/images/img.png'),
            EmployeeAvatar::url($user, new Collection())
        );
    }
}
