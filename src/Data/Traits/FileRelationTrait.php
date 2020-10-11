<?php

declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use atk4\data\Model;
use atk4\data\Reference\HasMany;
use PMRAtk\Data\File;
use traitsforatkdata\UserException;

trait FileRelationTrait
{

    protected function addFileReferenceAndDeleteHook(bool $addDelete = true): HasMany
    {
        $ref = $this->hasMany(
            File::class,
            [
                function () {
                    return (new File($this->persistence, ['parentObject' => $this]))->addCondition(
                        'model_class',
                        get_class($this)
                    );
                },
                'their_field' => 'model_id'
            ]
        );

        if ($addDelete) {
            $this->onHook(
                Model::HOOK_AFTER_DELETE,
                function (self $model) {
                    foreach ($model->ref(File::class) as $file) {
                        $file->delete();
                    }
                }
            );
        }

        return $ref;
    }

    /**
     * Used to map ATK ui file input to data level
     */
    public function addUploadFileFromAtkUi($temp_file, string $type = ''): ?File
    {
        if ($temp_file === 'error') {
            return null;
        }

        //if $this was never saved (no id yet), use afterSave hook
        if (!$this->loaded()) {
            $this->onHook(
                Model::HOOK_AFTER_SAVE,
                function (self $model) use ($temp_file, $type) {
                    $this->_addUploadFile($temp_file, $type);
                }
            );
            return null;
        } //if id is available, do at once
        else {
            return $this->_addUploadFile($temp_file, $type);
        }
    }

    protected function _addUploadFile(array $temp_file, string $type): ?File
    {
        $file = $this->ref(File::class)->newInstance(null, ['parentObject' => $this]);
        if (!$file->uploadFile($temp_file)) {
            if ($this->app) {
                $this->app->addUserMessage(
                    'Die Datei konnte nicht hochgeladen werden, bitte versuche es erneut',
                    'error'
                );
            }
            return null;
        }
        if ($type) {
            $file->set('type', $type);
        }
        $file->save();
        if ($this->app) {
            $this->app->addUserMessage(
                'Die Datei wurde erfolgreich hochgeladen nach ' . $file->get('path') . $file->get('value'),
                'success'
            );
        }
        //add audit if model has audit, too
        if (method_exists($this, 'addAdditionalAudit')) {
            $this->addAdditionalAudit(
                'ADD_FILE',
                [
                    'filename' => $file->get('value'),
                    'auto_generated' => $file->get('auto_generated')
                ]
            );
        }

        return $file;
    }

    /**
     * removes a file reference. Benefit of this function is that it adds Audit
     */
    public function removeFile($fileId)
    {
        $file = $this->ref(File::class);
        $file->tryLoad($fileId);
        if (!$file->loaded()) {
            throw new UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
        }

        $cfile = clone $file;
        $file->delete();

        if ($this->app) {
            $this->app->addUserMessage(
                'Die Datei ' . $file->get('path') . $file->get('value') . 'wurde erfolgreich gelöscht.',
                'success'
            );
        }

        if (method_exists($this, 'addAdditionalAudit')) {
            $this->addAdditionalAudit('REMOVE_FILE', ['filename' => $cfile->get('value')]);
        }
    }
}
