<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BrowserTestSeeder extends Seeder
{
    public function run(): void
    {
        abort_unless(app()->environment('testing') && config('database.default') === 'sqlite'
            && str_starts_with(basename(config('database.connections.sqlite.database')), 'browser-'), 403);
        $user = User::factory()->create(['name' => 'Teste Navegador', 'username' => 'browser_test', 'email' => 'browser@example.test', 'password' => Hash::make('BrowserTest@123')]);
        foreach (range(1, 14) as $index) {
            $anime = Anime::create(['mal_id' => $index, 'title' => sprintf('Anime %02d', $index), 'episodes' => 12, 'genres' => 'Drama', 'image' => '/images/logoSapo.png']);
            UserAnimeMeta::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'is_favorite' => true, 'top_position' => $index <= 3 ? $index : null]);
            WatchPlan::create(['user_id' => $user->id, 'anime_id' => $anime->id, 'watch_status' => 'concluido', 'episodes_watched' => 12, 'start_date' => today()]);
        }
    }
}
