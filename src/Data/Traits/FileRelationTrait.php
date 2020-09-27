<?php

declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use PMRAtk\Data\File;
use traitsforatkdata\UserException;

trait FileRelationTrait
{

    protected function _addFileRef()
    {
        $this->hasMany(
            'File',
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
                'afterSave',
                function ($m) use ($temp_file, $type) {
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
        $file = $this->ref('File')->newInstance(null, ['parentObject' => $this]);
        if (!$file->uploadFile($temp_file)) {
            $this->app->addUserMessage('Die Datei konnte nicht hochgeladen werden, bitte versuche es erneut', 'error');
            return null;
        }
        if ($type) {
            $file->set('type', $type);
        }
        $file->save();
        $this->app->addUserMessage(
            'Die Datei wurde erfolgreich hochgeladen nach ' . $file->get('path') . $file->get('value'),
            'success'
        );
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
        $file = $this->ref('File');
        $file->tryLoad($fileId);
        if (!$file->loaded()) {
            throw new UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
        }

        $cfile = clone $file;
        $file->delete();
        $this->app->addUserMessage(
            'Die Datei ' . $file->get('path') . $file->get('value') . 'wurde erfolgreich gelöscht.',
            'success'
        );
        if (method_exists($this, 'addAdditionalAudit')) {
            $this->addAdditionalAudit('REMOVE_FILE', ['filename' => $cfile->get('value')]);
        }
    }
}
