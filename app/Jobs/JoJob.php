<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\SubJob;
use App\Jobs\TestJob;
use App\Chain;
class JoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Chain::create([
            'name' => 'start Job',
            'description' => 'start job',
            'vk_id' => date('i:s'),
       ]);

       dispatch(new SubJob);
        dispatch(new TestJob);



        Chain::create([
            'name' => 'end Job',
            'description' => 'end job',
            'vk_id' => date('i:s'),
       ]);
    }
}
