<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayFastWebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'response',
    ];
}
