<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class Residents extends Model
{
    use HasFactory, Uuids;

    protected $primaryKey = 'id_resident';
    protected $table = 'residents';

    protected $fillable = [
        'id_user',
        'id_occupy',
        'resident_name',
        'resident_phone_number',
        'resident_address',
        'resident_nik',
        'resident_user_image',
        'resident_nik_image',
        'resident_ktm_image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function occupy()
    {
        return $this->belongsTo(Occupied::class, 'id_occupy', 'id_occupy');
    }
}
