<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use App\Models\RoomType;

class Room extends Model
{
    use HasFactory, Uuids;

    protected $primaryKey = 'id_room';
    protected $table = 'rooms';

    protected $fillable = [
        'id_room_type',
        'room_number',
        'room_floor'
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'id_room_type', 'id_room_type');
    }
}
