<?php

namespace PMRAtk\Data\Traits;


trait FileRelationTrait {


    /*
     *
     */
    protected function _addFileRef() {
        $this->hasMany('File', [(new \PMRAtk\Data\File($this->persistence, ['parentObject' => $this]))->addCondition('model_class', get_class($this)), 'their_field' => 'model_id']);
    }


    /*
     * Used to map ATK ui file input to data level
     */
    public function addUploadFileFromAtkUi($temp_file, string $file_class = '\\PMRAtk\\Data\\File'):?\PMRAtk\Data\File {
        if($temp_file === 'error') {
            return null;
        }

        //if $this was never saved (no id yet), use afterSave hook
        if(!$this->loaded()) {
            $this->addHook('afterSave', function($m) use ($temp_file, $file_class) {
                $this->_addUploadFile($temp_file, $file_class);
            });
            return null;
        }
        //if id is available, do at once
        else {
            return $this->_addUploadFile($temp_file, $file_class);
        }
    }


    /*
     * helper for addUploadFileFromAtkUi
     */
    protected function _addUploadFile(array $temp_file, string $file_class):?\PMRAtk\Data\File {
        $file = new $file_class($this->persistence, ['parentObject' => $this]);
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
        if($file->loaded()) {
            $cfile = clone $file;
            $file->delete();
            $this->app->addUserMessage('Die Datei '.$file->get('path').$file->get('value'). 'wurde erfolgreich gelöscht.', 'success');
            if(method_exists($this, 'addAdditionalAudit')) {
                $this->addAdditionalAudit('REMOVE_FILE', ['filename' => $cfile->get('value')]);
            }
            return true;
        }

        throw new \PMRAtk\Data\UserException('Die Datei die gelöscht werden soll kann nicht gefunden werden.');
    }
}
