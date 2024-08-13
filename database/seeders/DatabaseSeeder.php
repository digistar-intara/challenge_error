<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use crypt
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $roomType = [
            [
                'room_type' => 'Kamar Mandi Dalam',
                'room_type_price' => '1000000',
            ],
            [
                'room_type' => 'Kamar Mandi Luar',
                'room_type_price' => '850000',
            ],
        ];

        foreach ($roomType as $room) {
            \App\Models\RoomType::create($room);
        }

        \App\Models\User::create([
            'username' => 'jkendil',
            'full_name' => 'Joko Kendil',
            'phone_number' => '081234567890',
            'account_verified_at' => now(),
            'address' => 'Jl. Kenangan No. 1',
            'password' => bcrypt('12345678a'),
            'role' => 'user',
            'nik' => '1234567812345678',
            'nik_image' => Crypt::encryptString('nik.jpg'),
            'ktm_image' => Crypt::encryptString('ktm.png'),
            'user_image' => Crypt::encryptString('user.jpg'),
        ]);

        \App\Models\User::create([
            'username' => 'admin',
            'full_name' => 'Admin',
            'phone_number' => '081234567891',
            'account_verified_at' => now(),
            'address' => 'Jl. Kenangan No. 1',
            'password' => bcrypt('12345678a'),
            'role' => 'admin',
            'nik' => '1234567812345679',
            'nik_image' => Crypt::encryptString('nik.jpg'),
            'ktm_image' => Crypt::encryptString('ktm.png'),
            'user_image' => Crypt::encryptString('user.jpg'),
        ]);

        $idRoomTypeKMDalam = \App\Models\RoomType::where('room_type', 'Kamar Mandi Dalam')->first()->id_room_type;
        $idRoomTypeKMLuar = \App\Models\RoomType::where('room_type', 'Kamar Mandi Luar')->first()->id_room_type;

        // room number 1 - 8 at floor 1 with room type Kamar Mandi Dalam
        // room number 9 - 19 at floor 2 with room type Kamar Mandi Dalam and at 1B - 8B and Kamar Mandi Luar
        // room number 20 - 35 at floor 3 with room type Kamar Mandi Dalam
        $rooms = [
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '01',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '02',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '03',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '04',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '05',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '06',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '07',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '08',
                'room_floor' => '1',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '09',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '10',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '11',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '12',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '13',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '14',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '15',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_number' => '16',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '17',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '18',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '19',
                'room_floor' => '2',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '20',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '21',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '22',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '23',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '24',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '25',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '26',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '27',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '28',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '29',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '30',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '31',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '32',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '33',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '34',
                'room_floor' => '3',
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_number' => '35',
                'room_floor' => '3',
            ],
        ];

        foreach ($rooms as $room) {
            \App\Models\Room::create($room);
        }

        $idUser = \App\Models\User::where('username', 'jkendil')->first()->id_user;
        $idRoom = \App\Models\Room::where('room_number', '01')->first()->id_room;


        \App\Models\Occupied::create([
            'id_user' => $idUser,
            'id_room' => $idRoom,
            'subscription_model' => '6 months',
            'status_occupy' => 'occupied', // 'pending', 'occupied', 'checkout', 'canceled', 'expired', 'rejected'
            'is_double_bed' => 'no',
            'check_in' => '2021-01-01',
        ]);

        $idOccupied = \App\Models\Occupied::where('id_user', $idUser)->first()->id_occupy;

        \App\Models\Residents::create([
            'id_occupy' => $idOccupied,
            'id_user' => $idUser,
            'resident_name' => 'Joko Kendil',
            'resident_phone_number' => '081234567890',
            'resident_address' => 'Jl. Kenangan No. 1',
            'resident_nik' => '1234567812345678',
            'resident_nik_image' => Crypt::encryptString('nik.jpg'),
            'resident_ktm_image' => Crypt::encryptString('ktm.png'),
            'resident_user_image' => Crypt::encryptString('user.jpg'),
        ]);

        $dateCheckIn = \App\Models\Occupied::where('id_user', $idUser)->first()->check_in;

        // convert subscription model to month
        $subscriptionModel = \App\Models\Occupied::where('id_user', $idUser)->first()->subscription_model;

        if ($subscriptionModel == '1 month') {
            $subscriptionModel = 1;
        } elseif ($subscriptionModel == '3 months') {
            $subscriptionModel = 3;
        } else {
            $subscriptionModel = 6;
        } 

        $inDays = $subscriptionModel * 30;

        \App\Models\Subscription::create([
            'id_user' => $idUser,
            'id_occupy' => $idOccupied,
            'payment_receipt' => Crypt::encryptString('receipt.jpg'),
            'subscription_status' => 'paid',
            'subscription_date' => $dateCheckIn,
            'subscription_end_date' => Carbon::parse($dateCheckIn)->addDays($inDays),
        ]);

        // each room type has 6 photo

        $imageRoom = [
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_photo' => Crypt::encryptString('km-dalam/km-dalam-a.jpg'),
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_photo' => Crypt::encryptString('km-dalam/km-dalam-b.jpg'),
            ],
            [
                'id_room_type' => $idRoomTypeKMDalam,
                'room_photo' => Crypt::encryptString('km-dalam/km-dalam-c.jpg'),
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_photo' => Crypt::encryptString('km-luar/km-luar-a.jpg'),
            ],
            [
                'id_room_type' => $idRoomTypeKMLuar,
                'room_photo' => Crypt::encryptString('km-luar/km-luar-b.jpg'),
            ]
        ];

        foreach ($imageRoom as $image) {
            \App\Models\RoomPhoto::create($image);
        }
    }

}
