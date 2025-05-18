<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'quantity',
        'purchased_at'
    ];

    protected $casts = [
        'purchased_at' => 'datetime'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paypalTransaction()
    {
        return $this->hasOne(PayPalTransaction::class);
    }
}
