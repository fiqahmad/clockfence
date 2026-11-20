<?php

namespace App\Models;

use App\Enums\ClockFailure;
use Database\Factories\GeoCheckFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * One Geo-check of a Staff member against their home Site. A passing one carries
 * the hashed Clock token. Nothing here is mass assignable.
 *
 * @property int $id
 * @property int $user_id
 * @property int $site_id
 * @property bool $passed
 * @property ClockFailure|null $failure
 * @property float|null $distance_m
 * @property float $accuracy_m
 * @property int $radius_m
 * @property float|null $lat
 * @property float|null $lng
 * @property string|null $token_hash
 * @property Carbon|null $expires_at
 * @property Carbon|null $consumed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Hidden(['lat', 'lng', 'token_hash', 'distance_m'])]
class GeoCheck extends Model
{
    /** @use HasFactory<GeoCheckFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $guarded = ['*'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'failure' => ClockFailure::class,
            'distance_m' => 'float',
            'accuracy_m' => 'float',
            'radius_m' => 'integer',
            'lat' => 'float',
            'lng' => 'float',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function hasExpired(): bool
    {
        return $this->expires_at === null || $this->expires_at->isPast();
    }
}
