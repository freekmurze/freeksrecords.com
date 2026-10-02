<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $discogs_release_id
 * @property ?string $original_release_date
 * @property ?string $original_date_source
 * @property ?string $pressing_release_date
 * @property ?string $musicbrainz_id
 * @property ?CarbonImmutable $created_at
 */
class RecordReleaseDate extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'discogs_release_id';

    protected $guarded = [];
}
