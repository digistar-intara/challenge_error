<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RoomPhoto extends Model
{
    use HasFactory, Uuids;

    protected $table = 'room_photos';
    protected $primaryKey = 'id_room_photo';

    protected $fillable = [
        'id_room_type',
        'room_photo',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class, 'id_room_type', 'id_room_type');
    }
}
