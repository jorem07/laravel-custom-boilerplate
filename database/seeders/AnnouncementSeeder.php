<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $announcements = [
            [
                'id' => 1,
                'title' => 'Office will be closed on May 25, 2024 (Saturday) in observance of National Heroes Day.',
                'status' => 'Published',
                'type' => 'new',
                'icon' => 'mdi-alert-circle',
                'icon_color' => '#E53935',
                'icon_bg_color' => '#FFEBEE',
                'scheduled_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addDays(5),
                'created_by' => 1,
            ],
            [
                'id' => 2,
                'title' => 'Priority lanes are available for Senior Citizens, PWDs, Pregnant Women, and Solo Parents.',
                'status' => 'Published',
                'type' => 'info',
                'icon' => 'mdi-information',
                'icon_color' => '#1565C0',
                'icon_bg_color' => '#E3F2FD',
                'scheduled_at' => Carbon::now()->subDays(3),
                'expires_at' => Carbon::now()->addMonth(),
                'created_by' => 3,
            ],
            [
                'id' => 3,
                'title' => 'Please prepare all required documents for faster transaction. Thank you!',
                'status' => 'Published',
                'type' => 'doc',
                'icon' => 'mdi-file-document',
                'icon_color' => '#FB8C00',
                'icon_bg_color' => '#FFF3E0',
                'scheduled_at' => Carbon::now()->subDays(4),
                'expires_at' => Carbon::now()->addMonth(),
                'created_by' => 4,
            ],
            [
                'id' => 4,
                'title' => 'System maintenance on May 28, 2024 10:00 PM - 2:00 AM.',
                'status' => 'Scheduled',
                'type' => 'info',
                'icon' => 'mdi-clock-outline',
                'icon_color' => '#1565C0',
                'icon_bg_color' => '#E3F2FD',
                'scheduled_at' => Carbon::now()->addDays(2),
                'expires_at' => Carbon::now()->addDays(3),
                'created_by' => 2,
            ],
            [
                'id' => 5,
                'title' => 'New service: Document Authentication is now available.',
                'status' => 'Scheduled',
                'type' => 'new',
                'icon' => 'mdi-alert-circle',
                'icon_color' => '#2E7D32',
                'icon_bg_color' => '#E8F5E9',
                'scheduled_at' => Carbon::now()->addDays(3),
                'expires_at' => Carbon::now()->addMonth(),
                'created_by' => 1,
            ],
            [
                'id' => 6,
                'title' => 'Holiday Schedule: June 12, 2024 (Wed) Independence Day.',
                'status' => 'Draft',
                'type' => 'info',
                'icon' => 'mdi-information',
                'icon_color' => '#1565C0',
                'icon_bg_color' => '#E3F2FD',
                'scheduled_at' => null,
                'expires_at' => null,
                'created_by' => 5,
            ],
            [
                'id' => 7,
                'title' => 'Customer satisfaction survey is now available in the mobile app.',
                'status' => 'Draft',
                'type' => 'check',
                'icon' => 'mdi-shield-check',
                'icon_color' => '#2E7D32',
                'icon_bg_color' => '#E8F5E9',
                'scheduled_at' => null,
                'expires_at' => null,
                'created_by' => 6,
            ],
            [
                'id' => 8,
                'title' => 'Schedule Advisory: Counter 3 is temporarily unavailable.',
                'status' => 'Expired',
                'type' => 'info',
                'icon' => 'mdi-alert-circle',
                'icon_color' => '#E53935',
                'icon_bg_color' => '#FFEBEE',
                'scheduled_at' => Carbon::now()->subMonth(),
                'expires_at' => Carbon::now()->subDays(10),
                'created_by' => 3,
            ],
            [
                'id' => 9,
                'title' => 'New operating hours effective June 1, 2024: 8:00 AM - 5:00 PM.',
                'status' => 'Expired',
                'type' => 'info',
                'icon' => 'mdi-information',
                'icon_color' => '#1565C0',
                'icon_bg_color' => '#E3F2FD',
                'scheduled_at' => Carbon::now()->subMonths(2),
                'expires_at' => Carbon::now()->subMonth(),
                'created_by' => 4,
            ],
            [
                'id' => 10,
                'title' => 'Introducing our new queue tracking feature in the app.',
                'status' => 'Expired',
                'type' => 'new',
                'icon' => 'mdi-sparkles',
                'icon_color' => '#FB8C00',
                'icon_bg_color' => '#FFF3E0',
                'scheduled_at' => Carbon::now()->subMonths(3),
                'expires_at' => Carbon::now()->subMonths(2),
                'created_by' => 2,
            ],
        ];

        // Seed additional 21 announcements to total 31 records matching ANNOUNCEMENTS_MANAGEMENT_LIST
        for ($i = 11; $i <= 31; $i++) {
            $statuses = ['Published', 'Scheduled', 'Draft', 'Expired'];
            $status = $statuses[$i % 4];
            $announcements[] = [
                'id' => $i,
                'title' => "Public Service Announcement #{$i}: Routine information update and advisories for all PAO clients.",
                'status' => $status,
                'type' => 'info',
                'icon' => 'mdi-information',
                'icon_color' => '#1565C0',
                'icon_bg_color' => '#E3F2FD',
                'scheduled_at' => $status === 'Draft' ? null : Carbon::now()->subDays($i),
                'expires_at' => $status === 'Draft' ? null : Carbon::now()->addDays($i),
                'created_by' => ($i % 6) + 1,
            ];
        }

        foreach ($announcements as $a) {
            Announcement::updateOrCreate(['id' => $a['id']], $a);
        }
    }
}
