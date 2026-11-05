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
        $planned = Anime::create(['mal_id' => 15, 'title' => 'Anime em espera', 'episodes' => 12, 'genres' => 'Drama', 'image' => '/images/logoSapo.png']);
        WatchPlan::create(['user_id' => $user->id, 'anime_id' => $planned->id, 'watch_status' => 'pretendo_assistir', 'episodes_watched' => 0, 'start_date' => today()]);
        $other = User::factory()->create(['name' => 'Outra Pessoa', 'username' => 'browser_other', 'email' => 'other@example.test']);
        $shared = Anime::create(['mal_id' => 16, 'title' => 'Anime de outra pessoa', 'episodes' => 12, 'genres' => 'Drama', 'image' => '/images/logoSapo.png']);
        UserAnimeMeta::create(['user_id' => $other->id, 'anime_id' => $shared->id, 'is_favorite' => true]);
        $community = Anime::create(['mal_id' => 17, 'title' => 'Shingeki no Kyojin', 'episodes' => 25, 'genres' => 'Action', 'image' => '/images/logoSapo.png', 'anime_status' => 'Finished Airing',
            'aired_from' => '2013-04-07', 'aired_to' => '2013-09-29', 'aired_precision' => 'day', 'season' => 'spring', 'season_year' => 2013, 'media_type' => 'TV', 'source_material' => 'Manga',
            'duration' => '24 min per ep', 'age_rating' => 'R - 17+ (violence & profanity)', 'studios' => 'Wit Studio', 'metadata_synced_at' => now()]);
        $community->titles()->createMany([['type' => 'English', 'title' => 'Attack on Titan'], ['type' => 'Synonym', 'title' => 'SnK'], ['type' => 'Japanese', 'title' => '進撃の巨人']]);
        $plan = WatchPlan::create(['user_id' => null, 'anime_id' => $community->id, 'watch_status' => 'assistindo', 'episodes_watched' => 2, 'episodes_per_day' => 1, 'start_date' => today()->subDays(3)]);
        foreach (range(0, 6) as $day) {
            $plan->days()->create(['day_of_week' => $day, 'episodes_planned' => 1, 'is_variable' => false]);
        }
        Anime::create(['mal_id' => 18, 'title' => 'Anime sem dados', 'metadata_sync_error' => 'not_found']);
        $admin = User::factory()->create(['name' => 'Admin Navegador', 'username' => 'browser_admin', 'email' => 'admin@example.test', 'password' => Hash::make('BrowserTest@123')]);
        $admin->forceFill(['role' => 'admin'])->save();
    }
}
