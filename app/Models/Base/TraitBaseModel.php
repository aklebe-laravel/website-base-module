<?php

namespace Modules\WebsiteBase\app\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @todo: dissolve - move to WebsiteBase?
 */
trait TraitBaseModel
{
    use \Modules\SystemBase\app\Models\Base\TraitBaseModel;

    /**
     * @return Model
     */
    public function replicateWithRelations(): Model
    {
        /** @var Model $newItem */
        $newItem = $this->replicate();
        $newItem->created_at = Carbon::now();

        // call afterReplicated()
        $methodAfterReplicate = 'afterReplicated';
        if (method_exists($newItem, $methodAfterReplicate)) {
            $newItem->$methodAfterReplicate($this);
        }

        // assign extra attributes which will be automatically saved by $newItem->save()
        if (app('system_base')->hasInstanceClassOrTrait($this, TraitAttributeAssignment::class)) {
            $newItem->setExtraAttributes($this->getExtraAttributes());
        }

        // save it
        $newItem->save();

        // loop through all relation should also be replicated
        foreach ($this->getReplicateRelations() as $relationName) {
            $newItem->$relationName()->sync($this->$relationName);
        }

        return $newItem;
    }

}
