<?php

namespace PMRAtk\Data\Traits;


trait FileRelationTrait {


    /*
     *
     */
    protected function _addFileRef() {
        $this->hasMany('File', [
            function() {
                return (new \PMRAtk\Data\File($this->persistence, ['parentObject' => $this]))->addCondition('model_class', get_class($this));
            },
            'their_field' => 'model_id']);
    }


    /*
     * Used to map ATK ui file input to data level
     */
    public function addUploadFileFromAtkUi($temp_file):?\PMRAtk\Data\File {
        if($temp_file === 'error') {
            return null;
        }

        //if $this was never saved (no id yet), use afterSave hook
        if(!$this->loaded()) {
            $this->addHook('afterSave', function($m) use ($temp_file) {
                $this->_addUploadFile($temp_file);
            });
            return null;
        }
        //if id is available, do at once
        else {
            return $this->_addUploadFile($temp_file);
        }
    }


    /*
     * helper for addUploadFileFromAtkUi
     */
    protected function _addUploadFile(array $temp_file):?\PMRAtk\Data\File {
        $file = $this->ref('File')->newInstance(null, ['parentObject' => $this]);
        if(!$file->uploadFile($temp_file)) {
            $this->app->addUserMessage('Die Datei konnte nicht hochgeladen werden, bitte versuche es erneut', 'error');
            return null;
        }
        $file->save();
        $this->app->addUserMessage('Die Datei wurde erfolgreich hochgeladen nach '.$file->get('path').$file->get('value'), 'success');
        //add audit if model has audit, too
        if(method_exists($this, 'addAdditionalAudit')) {
            $this->addAdditionalAudit('ADD_FILE', ['filename' => $file->get('value'), 'auto_generated' => $file->get('auto_generated')]);
        }

        return clone $file;
    }


    /*
     * removes a file reference
     *
     * @param int $file_id   The id of the file reference to delete
     */
    public function removeFile(int $file_id) {
        $file = $this->ref('File');
        $file->tryLoad($file_id);
        if(!$file->loaded()) {
            throw new \PMRAtk\Data\UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
        }

        $cfile = clone $file;
        $file->delete();
        $this->app->addUserMessage('Die Datei ' . $file->get('path') . $file->get('value') . 'wurde erfolgreich gelöscht.', 'success');
        if (method_exists($this, 'addAdditionalAudit')) {
            $this->addAdditionalAudit('REMOVE_FILE', ['filename' => $cfile->get('value')]);
        }
    }
}
