<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['image_name', 'amount', 'reference', 'payment_type', 'user_id', 'verified', 'authorised_by', 'membership_number', 'fundraiser_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function authorisedBy()
    {
        return $this->belongsTo(User::class, 'authorised_by');
    }

    public function fundraiser()
    {
        return $this->belongsTo(Fundraiser::class, 'fundraiser_id', 'id');
    }

}
