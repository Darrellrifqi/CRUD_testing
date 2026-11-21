<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Courier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'vehicle_type',
        'vehicle_plate',
        'level',
        'is_active',
        'nik',
        'address',
        'city',
        'rating',
        'total_deliveries',
        'registered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'level'            => 'integer',
        'is_active'        => 'boolean',
        'rating'           => 'decimal:2',
        'total_deliveries' => 'integer',
        'registered_at'    => 'date',
    ];

    /**
     * Allowed vehicle types.
     */
    public const VEHICLE_TYPES = ['motorcycle', 'bicycle', 'car', 'truck', 'van'];

    /**
     * Valid courier levels.
     */
    public const LEVELS = [1, 2, 3, 4, 5];

    /**
     * Scope: filter by one or more levels.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array<int>  $levels
     */
    public function scopeByLevels($query, array $levels): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('level', $levels);
    }

    /**
     * Scope: full-text style search across name tokens.
     * Supports matching partial words in any order (e.g. "budi agung"
     * will match "Budiono Hadi Agung").
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     */
    public function scopeSearch($query, string $search): \Illuminate\Database\Eloquent\Builder
    {
        $tokens = preg_split('/[\s+]+/', trim($search), -1, PREG_SPLIT_NO_EMPTY);

        return $query->where(function ($q) use ($tokens) {
            foreach ($tokens as $token) {
                $q->where('name', 'like', '%' . $token . '%');
            }
        });
    }
}
