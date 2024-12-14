<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Chirp;

class ChirpSeeder extends Seeder
{
    public function run()
    {
        $user = User::factory()->create();
        Chirp::factory()->count(5)->create(['user_id' => $user->id]);
    }
}

