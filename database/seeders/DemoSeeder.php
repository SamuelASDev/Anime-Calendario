<?php

namespace Database\Seeders;

use App\Models\Anime;
use App\Models\User;
use App\Models\UserAnimeMeta;
use App\Models\WatchLog;
use App\Models\WatchPlan;
use App\Models\WatchPlanDay;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Os dados de demonstracao so podem ser criados em local/testing.');
        }

        DB::transaction(function () {
            // Preserve credentials and profile data belonging to the first real user.
            $first = User::orderBy('id')->first();
            if ($first) {
                $first->forceFill(['role' => 'admin'])->save();
            }

            $names = ['Admin Teste', 'Ana Sakura', 'Bruno Senpai', 'Clara Hoshi', 'Diego Ryu',
                'Elisa Yuki', 'Felipe Sora', 'Giovana Nami', 'Henrique Ken', 'Isabela Rin',
                'Joao Akira', 'Julia Mei', 'Lucas Kaito', 'Marina Hana', 'Nicolas Rei',
                'Olivia Aoi', 'Pedro Haru', 'Rafaela Mika', 'Thiago Ren', 'Vitoria Emi'];
            $password = Hash::make('AnimeTeste@123');
            $users = collect();
            foreach ($names as $i => $name) {
                $email = $i === 0 ? 'admin@anime-calendar.test' : sprintf('usuario%02d@anime-calendar.test', $i);
                $user = User::firstOrNew(['email' => $email]);
                if (! $user->exists) {
                    $user->password = $password;
                }
                $user->forceFill([
                    'name' => $name,
                    'username' => $i === 0 ? 'demo_admin' : sprintf('demo_user_%02d', $i),
                    'role' => $i === 0 ? 'admin' : 'user',
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'bio' => 'Perfil de teste: gosto de aventuras, boas trilhas sonoras e de organizar minha lista de animes.',
                    'is_public' => $i % 5 !== 4,
                    'show_watching_public' => true,
                    'show_completed_public' => true,
                    'show_favorites_public' => $i % 4 !== 3,
                    'show_top10_public' => true,
                    'show_reviews_public' => $i % 3 !== 2,
                    'profile_photo' => $this->art('profiles/photos/demo-'.$i.'.svg', $name, $i, false),
                    'profile_banner' => $this->art('profiles/banners/demo-'.$i.'.svg', 'ANIME CALENDAR', $i, true),
                ])->save();
                $users->push($user);
            }

            // Fictional catalog: stable identifiers reserved for these local fixtures.
            $titles = ['Guardioes da Lua', 'Cafe das Estrelas', 'Samurai do Amanhecer', 'Academia dos Ventos',
                'O Ultimo Trem Espacial', 'Diario de uma Alquimista', 'Mar de Cristal', 'Clube do Volei',
                'Cidade dos Robos', 'Jardim dos Espiritos', 'A Melodia do Verao', 'Cavaleiros do Eclipse',
                'Receitas de Outro Mundo', 'Detetive das Sombras', 'Corrida para o Futuro', 'Cartas de Inverno'];
            $genres = ['Action, Adventure, Fantasy', 'Slice of Life, Comedy', 'Action, Drama', 'Fantasy, School',
                'Sci-Fi, Adventure', 'Fantasy, Drama', 'Adventure, Mystery', 'Sports, School',
                'Sci-Fi, Action', 'Supernatural, Fantasy', 'Romance, Music', 'Action, Fantasy',
                'Gourmet, Comedy', 'Mystery, Suspense', 'Sports, Sci-Fi', 'Romance, Drama'];
            $animes = collect();
            foreach ($titles as $i => $title) {
                $cover = $this->art('demo/covers/anime-'.$i.'.svg', $title, $i, false);
                $anime = Anime::firstOrCreate(['mal_id' => 900000001 + $i], [
                    'title' => $title,
                    'episodes' => $i === 15 ? null : [12, 24, 26, 48][$i % 4],
                    'synopsis' => 'Fictional anime created for local application testing.',
                    'synopsis_pt' => 'Obra ficticia para testes. Uma jornada de amizade e descobertas muda a vida dos protagonistas, que precisam superar desafios e decidir o proprio destino.',
                    'genres' => $genres[$i],
                    'anime_status' => $i % 4 === 0 ? 'Currently Airing' : 'Finished Airing',
                    'image' => '/storage/'.$cover,
                ]);
                $animes->push($anime);
            }

            // Include the original account in test activity without replacing its existing plans.
            if ($first && ! $users->contains('id', $first->id)) {
                $users->push($first);
            }
            foreach ($users as $u => $user) {
                for ($j = 0; $j < 10; $j++) {
                    $anime = $animes[($u + $j) % $animes->count()];
                    $status = ['assistindo', 'concluido', 'pausado'][$j % 3];
                    $this->plan($user->id, $anime, $status);
                    UserAnimeMeta::firstOrCreate(['user_id' => $user->id, 'anime_id' => $anime->id], [
                        'rating' => 6 + (($u + $j) % 5),
                        'comment' => ['Gostei muito dos personagens e da trilha sonora.', 'Uma historia envolvente, com momentos surpreendentes.', 'O ritmo e tranquilo, ideal para assistir no fim do dia.'][$j % 3],
                        'is_favorite' => $j < 4,
                        'top_position' => $j + 1,
                    ]);
                }
            }
            foreach ($animes->take(6) as $i => $anime) {
                $this->plan(null, $anime, $i < 4 ? 'assistindo' : 'concluido');
            }
        });
    }

    private function plan(?int $userId, Anime $anime, string $status): void
    {
        $plan = WatchPlan::firstOrCreate(['user_id' => $userId, 'anime_id' => $anime->id], [
            'episodes_watched' => $status === 'concluido' ? ($anime->episodes ?? 12) : 2,
            'episodes_per_day' => 1,
            'start_date' => today()->subDays(3)->toDateString(),
            'watch_status' => $status,
        ]);
        if (! $plan->wasRecentlyCreated) {
            return;
        }
        if ($status === 'pausado') {
            $plan->pauses()->create(['started_on' => today()]);
        } elseif ($status === 'concluido') {
            $plan->forceFill(['completed_at' => now()])->save();
        }
        foreach (range(0, 6) as $day) {
            WatchPlanDay::create([
                'watch_plan_id' => $plan->id, 'day_of_week' => $day,
                'episodes_planned' => $status === 'assistindo' ? 1 : 0,
                'is_variable' => $day === 6,
            ]);
        }
        if ($status !== 'pausado') {
            foreach ([2, 1] as $daysAgo) {
                WatchLog::create([
                    'watch_plan_id' => $plan->id,
                    'watched_date' => today()->subDays($daysAgo)->toDateString(),
                    'episodes_watched_today' => $status === 'concluido' ? 0 : 1,
                    'notes' => 'Registro de demonstracao para testar o historico.',
                ]);
            }
        }
    }

    private function art(string $path, string $label, int $index, bool $banner): string
    {
        $colors = ['#7c3aed', '#0369a1', '#be123c', '#047857', '#b45309', '#4338ca'];
        $color = $colors[$index % count($colors)];
        $height = $banner ? 240 : 480;
        $label = htmlspecialchars($label, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="'.$height.'" viewBox="0 0 640 '.$height.'">'
            .'<rect width="640" height="'.$height.'" fill="#18181b"/>'
            .'<circle cx="470" cy="80" r="230" fill="'.$color.'"/>'
            .'<circle cx="70" cy="'.$height.'" r="180" fill="'.$color.'" opacity=".45"/>'
            .'<text x="32" y="'.($height - 60).'" font-family="sans-serif" font-size="27" fill="white">'.$label.'</text>'
            .'<text x="32" y="'.($height - 24).'" font-family="sans-serif" font-size="16" fill="#d4d4d8">DEMO / ANIME CALENDAR</text></svg>';
        Storage::disk('public')->put($path, $svg);

        return $path;
    }
}
