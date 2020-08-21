<?php declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use atk4\data\Model;

/**
 * Trait CreatedDateAndLastUpdatedTrait
 * @package PMRAtk\Data\Traits
 */
trait CreatedDateAndLastUpdatedTrait {

    /**
     *
     */
    protected function addCreatedDateAndLastUpdateFields() {
        // Adds created_date and created_by field to model
        $this->addFields(
            [
                [
                    'created_date',
                    'type' => 'datetime',
                    'persist_timezone' => 'Europe/Berlin',
                    'system' => true
                ],
                [
                    'last_updated',
                    'type' => 'datetime',
                    'persist_timezone' => 'Europe/Berlin',
                    'system' => true
                ],
            ]
        );
    }


    /**
     *
     */
    protected function addCreatedDateAndLastUpdatedHook() {
        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function ($model, $isUpdate) {
                if (
                    !$isUpdate
                    && !$model->get('created_date')
                ) {
                    $model->set('created_date', new \DateTime());
                } else {
                    $model->set('last_updated', new \DateTime());
                }
            }
        );
    }

}