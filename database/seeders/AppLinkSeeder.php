<?php

namespace Database\Seeders;

use App\Models\AppLink;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyId = Company::first()?->id;
        $adminId = User::first()?->id;

        $links = [
            [
                'name' => 'Official Website',
                'link_type' => 'website',
                'url' => 'https://hr.kneayerng.com',
                'description' => 'Visit our official website for company information and online services.',
                'order' => 1,
                'status' => 1,
                'company_id' => $companyId,
                'created_by' => $adminId,
            ],
            [
                'name' => 'Telegram Channel',
                'link_type' => 'telegram',
                'url' => 'https://t.me/kneayerng',
                'description' => 'Join our official Telegram channel for real-time news and announcements.',
                'order' => 2,
                'status' => 1,
                'company_id' => $companyId,
                'created_by' => $adminId,
            ],
            [
                'name' => 'Facebook Page',
                'link_type' => 'facebook',
                'url' => 'https://facebook.com/kneayerng',
                'description' => 'Follow our Facebook page to stay updated on our activities and events.',
                'order' => 3,
                'status' => 1,
                'company_id' => $companyId,
                'created_by' => $adminId,
            ],
            [
                'name' => 'TikTok',
                'link_type' => 'tiktok',
                'url' => 'https://tiktok.com/@kneayerng',
                'description' => 'Watch our short videos and fun team moments on TikTok.',
                'order' => 4,
                'status' => 1,
                'company_id' => $companyId,
                'created_by' => $adminId,
            ],
            [
                'name' => 'YouTube Channel',
                'link_type' => 'youtube',
                'url' => 'https://youtube.com/@kneayerng',
                'description' => 'Subscribe to our YouTube channel for tutorials and highlight videos.',
                'order' => 5,
                'status' => 1,
                'company_id' => $companyId,
                'created_by' => $adminId,
            ],
            [
                'name' => 'Instagram',
                'link_type' => 'instagram',
                'url' => 'https://instagram.com/kneayerng',
                'description' => 'Check out our team culture and behind-the-scenes on Instagram.',
                'order' => 6,
                'status' => 1,
                'company_id' => $companyId,
                'created_by' => $adminId,
            ],
        ];

        foreach ($links as $linkData) {
            AppLink::firstOrCreate(
                [
                    'name' => $linkData['name'],
                    'link_type' => $linkData['link_type'],
                ],
                $linkData
            );
        }
    }
}
