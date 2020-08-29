<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Model;


/**
 * Class Audit
 * @package PMRAtk\Data
 */
class Audit extends SecondaryBaseModel {

    public $table = 'audit';


    /**
     *
     */
    public function init(): void {
        parent::init();

        $this->addFields(
            [
                [
                    'data',
                    'type'      => 'array',
                    'serialize' => 'serialize'
                ],
                //store the name of the creator. Might be needed to re-render rendered_output
                [
                    'created_by_name',
                    'type'      => 'string'
                ],
                [
                    'rendered_output',
                    'type'      => 'string'
                ],
            ]
        );


        $this->setOrder('created_date desc');

        // add Name of currently logged in user to "created_by_name" field
        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function($model, $isUpdate) {
                if($isUpdate) {
                    return;
                }
                if(
                    isset($model->app->auth->user)
                    && $model->app->auth->user->loaded()
                ) {
                    $model->set('created_by_name', $model->app->auth->user->get('name'));
                }
            }
        );
    }
}
