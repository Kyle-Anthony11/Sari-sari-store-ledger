<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;


class StoreProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_first_name',
        'owner_middle_name',
        'owner_last_name',
        'owner_suffix',
        'store_name',
        'contact_number',
        'store_address',
        'address_notes',
        'store_credentials_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function credentials(): BelongsTo
    {
        return $this->belongsTo(StoreCredential::class, 'store_credentials_id');
    }
    public function settings(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StoreSetting::class);
    }
}
