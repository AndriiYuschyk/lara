<?php

namespace App\Observers;

use App\Services\VersionComparator;
use Illuminate\Database\Eloquent\Model;

class HandleVersionsObserver
{
    public function created(Model $model): void
    {
        if (!method_exists($model, 'getVersionableAttributes')) {
            return;
        }

        $this->createVersion($model);
    }

    public function updated(Model $model): void
    {
        if (!method_exists($model, 'getVersionableAttributes')) {
            return;
        }

        $versionableAttributes = $model->getVersionableAttributes();

        $hasChanges = false;
        foreach ($versionableAttributes as $attribute) {
            if ($model->wasChanged($attribute)) {
                $hasChanges = true;
                break;
            }
        }

        if ($hasChanges) {
            $oldValues = $model->getOriginal();

            $newValues = $model->getAttributes();

            if (VersionComparator::hasChanges($oldValues, $newValues, $versionableAttributes)) {
                $this->createVersion($model);
            }
        }
    }

    protected function createVersion(Model $model): void
    {
        $versionModelClass = $model->getVersionModel();

        $versionableAttributes = $model->getVersionableAttributes();

        $versionData = [
            $model->getForeignKey() => $model->id,
            'version' => $model->getNextVersionNumber(),
        ];

        foreach ($versionableAttributes as $attribute) {
            $versionData[$attribute] = $model->getAttribute($attribute);
        }

        $versionModelClass::create($versionData);
    }
}
