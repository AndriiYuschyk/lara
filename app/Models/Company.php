<?php

namespace App\Models;

use App\Traits\HandleVersionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;
    use HandleVersionsTrait;

    protected $fillable = [
        'name',
        'edrpou',
        'address',
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(CompanyVersion::class);
    }

    public function getVersionableAttributes(): array
    {
        return ['name', 'address'];
    }

    public function getVersionModel(): string
    {
        return CompanyVersion::class;
    }
}
