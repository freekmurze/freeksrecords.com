<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $instance_id
 * @property int $discogs_release_id
 * @property CarbonImmutable $added_at
 * @property array<string, mixed> $payload
 */
class CollectionRecord extends Model
{
    public const CREATED_AT = null;

    public $incrementing = false;

    protected $primaryKey = 'instance_id';

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'added_at' => 'immutable_datetime',
        ];
    }
}
