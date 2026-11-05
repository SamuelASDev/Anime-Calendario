<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    /** Calendar order within a year. */
    public const SEASONS = ['winter', 'spring', 'summer', 'fall'];

    public function latestPublicReview()
    {
        return $this->hasOne(UserAnimeMeta::class)->ofMany(['updated_at' => 'max', 'id' => 'max'], fn ($query) => $query
            ->publiclyVisible()->where(fn ($review) => $review->whereNotNull('rating')->orWhereNotNull('comment')));
    }

    public function globalPlan()
    {
        return $this->hasOne(WatchPlan::class)->global();
    }

    public function userMeta()
    {
        return $this->hasMany(UserAnimeMeta::class);
    }

    protected $fillable = [
        'mal_id',
        'title',
        'episodes',
        'synopsis',
        'synopsis_pt',
        'anime_status',
        'image',
        'genres',
        'media_type',
        'source_material',
        'aired_from',
        'aired_to',
        'aired_precision',
        'season',
        'season_year',
        'duration',
        'age_rating',
        'studios',
        'metadata_synced_at',
        'metadata_sync_error',
    ];

    protected function casts(): array
    {
        return ['aired_from' => 'date', 'aired_to' => 'date', 'metadata_synced_at' => 'datetime', 'season_year' => 'integer'];
    }

    public function watchPlans()
    {
        return $this->hasMany(WatchPlan::class);
    }

    public function titles()
    {
        return $this->hasMany(AnimeTitle::class);
    }

    /** Release period for display; partial dates never show a day or month the source did not give. */
    public function airedLabel(): ?string
    {
        if (! $this->aired_from) {
            return null;
        }
        $from = $this->aired_from->format(match ($this->aired_precision) {
            'year' => 'Y',
            'month' => 'm/Y',
            default => 'd/m/Y',
        });

        return $this->aired_to ? $from.' a '.$this->aired_to->format('d/m/Y') : $from;
    }

    public function seasonLabel(): ?string
    {
        $season = ['winter' => 'Inverno', 'spring' => 'Primavera', 'summer' => 'Verão', 'fall' => 'Outono'][$this->season] ?? null;

        return $season && $this->season_year ? $season.' de '.$this->season_year : null;
    }

    public static function sourceLabelFor(string $source): string
    {
        return [
            'Manga' => 'Mangá', 'Web manga' => 'Mangá web', '4-koma manga' => 'Mangá 4-koma', 'Novel' => 'Romance',
            'Game' => 'Jogo', 'Card game' => 'Jogo de cartas', 'Book' => 'Livro', 'Picture book' => 'Livro ilustrado',
            'Music' => 'Música', 'Mixed media' => 'Mídia mista', 'Other' => 'Outra', 'Unknown' => 'Desconhecida',
        ][$source] ?? $source;
    }

    public function mediaTypeLabel(): ?string
    {
        return ['Movie' => 'Filme', 'Special' => 'Especial', 'Music' => 'Música'][$this->media_type] ?? $this->media_type;
    }
}
