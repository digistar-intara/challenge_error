<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use App\Models\Room;

class RoomType extends Model
{
    use HasFactory, Uuids;

    protected $primaryKey = 'id_room_type';
    protected $table = 'room_types';

    protected $fillable = [
        'room_type',
        'room_type_price'
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'id_room_type', 'id_room_type');
    }

    public function roomPhotos()
    {
        return $this->hasMany(RoomPhoto::class, 'id_room_type', 'id_room_type');
    }
}
