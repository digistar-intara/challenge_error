<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use App\Models\User;
use App\Models\Room;

class Subscription extends Model
{
    use HasFactory, Uuids;

    protected $primaryKey = 'id_subscription';
    protected $table = 'subscriptions';
    protected $fillable = [
        'id_user',
        'id_occupy',
        'subscription_status',
        'payment_receipt',
        'subscription_date',
        'subscription_end_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room', 'id_room');
    }
}
