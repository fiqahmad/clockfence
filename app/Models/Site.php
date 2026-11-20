<?php

namespace App\Models;

use App\Enums\StaffStatus;
use Database\Factories\SiteFactory;
use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * One physical workplace with a geofence, a start time and its own timezone.
 *
 * @property int $id
 * @property int|null $organization_id
 * @property string $name
 * @property string|null $address
 * @property float $geofence_lat
 * @property float $geofence_lng
 * @property int $geofence_radius_m
 * @property string $start_time
 * @property string $timezone
 * @property list<int> $working_days ISO weekdays, 1 = Monday to 7 = Sunday
 * @property Carbon|null $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'address', 'geofence_lat', 'geofence_lng', 'geofence_radius_m', 'start_time', 'timezone', 'working_days'])]
class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory;

    /**
     * Monday to Friday, as ISO weekdays.
     *
     * @var list<int>
     */
    public const DEFAULT_WORKING_DAYS = [1, 2, 3, 4, 5];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'geofence_lat' => 'float',
            'geofence_lng' => 'float',
            'geofence_radius_m' => 'integer',
            'working_days' => 'array',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Staff members whose home Site this is.
     *
     * @return HasMany<User, $this>
     */
    public function staffMembers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Sites that still accept Clocks.
     *
     * @param  Builder<self>  $query
     */
    public function scopeNotArchived(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * A Site can be Archived only when it is no Active Staff member's home Site.
     */
    public function canBeArchived(): bool
    {
        return ! $this->staffMembers()->where('status', StaffStatus::Active)->exists();
    }

    /**
     * Stop accepting Clocks while keeping the history. Sites are never deleted.
     */
    public function archive(): void
    {
        if (! $this->canBeArchived()) {
            throw new DomainException('A Site with Active Staff members cannot be Archived.');
        }

        $this->forceFill(['archived_at' => now()])->save();
    }

    public function unarchive(): void
    {
        $this->forceFill(['archived_at' => null])->save();
    }

    /**
     * Whether the given ISO weekday (1 = Monday) is a Working day here.
     */
    public function isWorkingWeekday(int $isoWeekday): bool
    {
        return in_array($isoWeekday, $this->working_days, true);
    }
}
