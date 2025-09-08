<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use App\Chain;
use App\Jobs\SubJob;
use App\Jobs\TestJob;

class EmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $msg;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    { 
        

        Chain::create([
            'name' => 'start',
            'description' => 'start',
            'vk_id' => date('i:s'),
        ]);



            TestJob::dispatch();
            SubJob::dispatch();

        Chain::create([
            'name' => 'end',
            'description' => 'use',
            'vk_id' => date('i:s'),
        ]);
    
   
    }
}
