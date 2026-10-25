<?php

namespace Tests\Unit;

use App\Models\DCardEmployee;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

class DCardPhotoUrlTest extends TestCase
{
    private Container $app;
    private Container $previousContainer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->app = new Container();
        Container::setInstance($this->app);
    }

    protected function tearDown(): void
    {
        Container::setInstance($this->previousContainer);
        parent::tearDown();
    }

    public function test_existing_local_photos_follow_the_current_site(): void
    {
        $this->app->instance('request', Request::create('https://hr.kytech.info/admin/d-card-print'));

        foreach ([
            'https://hr.kneayerng.com/storage/d-card-employees/photos/photo.jpg' => '/storage/d-card-employees/photos/photo.jpg',
            'http://127.0.0.1:8000/uploads/user/avatar/Thumb-photo' => '/uploads/user/avatar/Thumb-photo',
            '//old.example/storage/d-card-employees/photos/photo.jpg' => '/storage/d-card-employees/photos/photo.jpg',
            'uploads/user/avatar/Thumb-photo' => '/uploads/user/avatar/Thumb-photo',
        ] as $savedUrl => $expectedPath) {
            $employee = new DCardEmployee();
            $employee->setRawAttributes(['profile_photo_url' => $savedUrl]);

            $this->assertSame('https://hr.kytech.info' . $expectedPath, $employee->profile_photo_url);
        }
    }

    public function test_photo_domain_changes_with_the_current_request(): void
    {
        $employee = new DCardEmployee([
            'profile_photo_url' => 'https://hr.kneayerng.com/storage/d-card-employees/photos/photo.jpg',
        ]);

        foreach (['https://hr.kytech.info', 'https://new.example.test', 'http://127.0.0.1:8000'] as $domain) {
            $this->app->instance('request', Request::create($domain . '/admin/d-card-print'));

            $this->assertSame($domain . '/storage/d-card-employees/photos/photo.jpg', $employee->profile_photo_url);
        }
    }

    public function test_local_photos_are_saved_without_a_domain(): void
    {
        $employee = new DCardEmployee([
            'profile_photo_url' => 'https://hr.kytech.info/storage/d-card-employees/photos/photo.jpg',
        ]);

        $this->assertSame('/storage/d-card-employees/photos/photo.jpg', $employee->getAttributes()['profile_photo_url']);
    }

    public function test_external_photos_and_empty_values_are_preserved(): void
    {
        foreach (['https://images.example.com/portraits/photo.jpg', null, ''] as $value) {
            $employee = new DCardEmployee(['profile_photo_url' => $value]);

            $this->assertSame($value, $employee->profile_photo_url);
            $this->assertSame($value, $employee->getAttributes()['profile_photo_url']);
        }
    }
}
