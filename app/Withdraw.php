<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Withdraw extends Model
{

    protected $guarded = [];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'vk_id', 'vk_id');
    }

    public function changeStatus($id)
    {
        $status = $this->findOrFail($id)->update(['status' => true]);

        return true;
    }

    /* пока что лишнее */ 
    public function changeBalance($id, $amount)
    {
        $result = $this->findOrFail($id);
        $balance = $result->amount - $amount;

        if ($balance >= 0) {
            $result->update(['amount' => $balance]);
            return true;
        };

        return false;
    }
}
