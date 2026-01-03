<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fundraiser extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'start_date', 'end_date', 'user_id', 'created_by'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->belongsTo(User::class, 'authorised_by');
    }

    public function paymentsToFundraiser()
    {
        return $this->hasMany(Payment::class, 'fundraiser_id', 'id');
    }

    // public function payments()
    // {
    //     return $this->hasMany(Payment::class, 'fundraiser_id', 'id');
    // }

    // public function user()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id');
    // }
}
