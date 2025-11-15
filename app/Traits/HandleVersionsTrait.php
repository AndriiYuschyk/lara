<?php

namespace App\Traits;

use App\Observers\HandleVersionsObserver;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HandleVersionsTrait
{
    public static function bootHandleVersionsTrait(): void
    {
        static::observe(HandleVersionsObserver::class);
    }

    abstract public function getVersionableAttributes(): array;

    public function getVersionModel(): string
    {
        $modelClass = class_basename($this);

        return "App\\Models\\{$modelClass}Version";
    }
    public function getNextVersionNumber(): int
    {
        $latestVersion = $this->versions()->max('version');
        return $latestVersion ? $latestVersion + 1 : 1;
    }

    public function latestVersion()
    {
        return $this->versions()->orderBy('version', 'desc')->first();
    }

    abstract public function versions(): HasMany;
}
