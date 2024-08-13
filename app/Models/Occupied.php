<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use App\Models\User;
use App\Models\Room;

class Occupied extends Model
{
    use HasFactory, Uuids;

    protected $primaryKey = 'id_occupy';
    protected $table = 'occupies';

    protected $fillable = [
        'id_user',
        'id_room',
        'check_in',
        'subscription_model',
        'status_occupy',
        'is_double_bed',
        'check_out',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room', 'id_room');
    }

    public function resident()
    {
        return $this->hasMany(Residents::class, 'id_occupy', 'id_occupy');
    }

}
