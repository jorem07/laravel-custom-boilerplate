<?php

namespace App\Console\Commands;

use App\DTO\Announcement\AnnouncementDTO;
use App\Events\AnnouncementEvent;
use App\Models\Announcement;
use App\Models\AnnouncementStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonitorAnnouncement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:monitor-announcement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log::debug('running nani');
        DB::beginTransaction();
        try{

            $announcement = Announcement::with(['status:id,name']);
            $statuses = AnnouncementStatus::get();

            $expire = (clone $announcement)->where('expire_schedule', '<=', now())
            ->whereHas('status', function ($q) {
                $q->whereNot('name', 'Expired');
            });

            $expire_affected = $expire->exists() ? $expire->update([
                'announcement_status_id' => $statuses->firstWhere('name', 'Expired')->id,
            ]) : 0;

            if ($expire_affected > 0) {
                $published = Announcement::with(['status:id,name', 'office:id,name'])
                    ->whereHas('status', fn ($q) => $q->where('name', 'Published'))
                    ->get();

                event(new AnnouncementEvent([
                    'action' => 'expired',
                    'message' => 'Announcement expired',
                    'body' => AnnouncementDTO::fromCollection($published),
                    'announcements' => AnnouncementDTO::fromCollection($published),
                ]));
            }

            $publish = (clone $announcement)->where('publish_schedule', '<=', now())
            ->whereHas('status', function ($q) {
                $q->where('name', 'Scheduled');
            });

            $publish_affected = $publish->exists() ? $publish->update([
                'announcement_status_id' => $statuses->firstWhere('name', 'Published')->id,
            ]) : 0;

            if ($publish_affected > 0) {
                $published = Announcement::with(['status:id,name', 'office:id,name'])
                    ->whereHas('status', fn ($q) => $q->where('name', 'Published'))
                    ->get();

                event(new AnnouncementEvent([
                    'action' => 'published',
                    'message' => 'Announcement Published',
                    'body' => AnnouncementDTO::fromCollection($published),
                    'announcements' => AnnouncementDTO::fromCollection($published),
                ]));
            }


            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
