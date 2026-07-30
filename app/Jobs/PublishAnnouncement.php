<?php

namespace App\Jobs;

use App\Models\Announcement;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Announcement $announcement)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->announcement->update([
            // 'announement_status' => 2,
            'published_schedule'       => now(),
        ]);
    }
}
