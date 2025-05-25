<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'avatar',
        'date_of_birth',
        'gender',
        'phone',
        'address',
        'city',
        'district',
        'ward',
        'note'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->ward,
            $this->district,
            $this->city
        ]);
        return implode(', ', $parts);
    }
}
