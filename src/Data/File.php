<?php declare(strict_types=1);

namespace PMRAtk\Data;

use atk4\data\Exception;
use PMRAtk\Data\Traits\CryptIdTrait;
use PMRAtk\Data\SafeFileName;
use atk4\data\Model;


/**
 *
 */
class File extends SecondaryBaseModel
{
    use CryptIdTrait;

    public $table = 'file';


    /**
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        $this->addFields(
            [
                //filename is stored in value
                //the relative path to the file project main dir, e.g. output/images/
                [
                    'path',
                    'type' => 'string'
                ],
                //currently BC, INVOICE or TOURLIST for auto-generated pdfs, MAP for meeting point maps, TEMPORARY for files which get deleted by cronjob later
                [
                    'type',
                    'type' => 'string'
                ],
                //pdf, jpg etc
                [
                    'filetype',
                    'type' => 'string'
                ],
                //indicates if the file was generated by Script (true) or uploaded by user (false)
                [
                    'auto_generated',
                    'type' => 'boolean',
                    'default' => false
                ],
                //crypt_id, used when file should is made available for download
                [
                    'crypt_id',
                    'type' => 'string',
                    'system' => true
                ],
                [
                    'sort',
                    'type' => 'string'
                ]
            ]
        );


        $this->onHook(
            Model::HOOK_BEFORE_SAVE,
            function ($model, $isUpdate) {
                //add / to path
                if (
                    $model->get('path')
                    && substr($model->get('path'), -1) !== DIRECTORY_SEPARATOR
                ) {
                    $model->set('path', $model->get('path') . DIRECTORY_SEPARATOR);
                }

                //If file does not exist, dont save this in DB
                if (!$model->checkFileExists()) {
                    throw new Exception('The file to be saved does not exist: ' . $this->getFullFilePath());
                }

                //add filetype if not there
                if(
                    !$model->get('filetype')
                    && $model->get('value')
                ) {
                    $model->set('filetype', pathinfo($model->get('value'), PATHINFO_EXTENSION));
                }

                //file needs Crypt ID
                $model->setCryptId('crypt_id');
            }
        );

        //after successful delete, delete file as well
        $this->onHook(
            Model::HOOK_AFTER_DELETE,
            function ($m) {
                $m->deleteFile();
            }
        );

        //set path to standard file
        if (
            empty($this->get('path'))
            && defined('SAVE_FILES_IN')
        ) {
            $this->set('path', SAVE_FILES_IN);
        }
    }


    /**
     * For File, use a 21 char long cryptic ID
     */
    protected function _generateCryptId(): string
    {
        $return = '';
        for ($i = 0; $i < 21; $i++) {
            $return .= $this->getRandomChar();
        }

        return $return;
    }


    /**
     * tries to delete the file set in path
     * returns bool
     */
    public function deleteFile(): bool
    {
        if (file_exists($this->getFullFilePath())) {
            return unlink($this->getFullFilePath());
        }
        return false;
    }


    /**
     *
     */
    public function createFileName(string $name, bool $uniqueName = true)
    {
        $this->set('value', SafeFileName::createSafeFileName($name));
        $this->set('filetype', pathinfo($name, PATHINFO_EXTENSION));

        //can only check for existing file if path is set
        if ($uniqueName) {
            $old_name = $this->get('value');
            $i = 1;
            while (file_exists($this->getFullFilePath())) {
                $this->set(
                    'value',
                    pathinfo($old_name, PATHINFO_FILENAME) . '_' . $i . ($this->get('filetype') ? '.' . $this->get('filetype') : '')
                );
                $i++;
            }
        }
    }


    /**
     * Uses $_FILES array content to call move_uploaded_file
     */
    public function uploadFile($f)
    {
        $this->createFileName($f['name']);

        //try move the uploaded file, quit on error
        return move_uploaded_file($f['tmp_name'], $this->getFullFilePath());
    }


    /**
     * Returns the full path to the file from the file system base dir
     */
    public function getFullFilePath(): string
    {
        return FILE_BASE_PATH . $this->get('path') . $this->get('value');
    }


    /**
     * checks if the file really exists
     */
    public function checkFileExists(): bool
    {
        return (
            file_exists($this->getFullFilePath())
            && is_file($this->getFullFilePath())
        );
    }


    /**
     * saves the content passed as string to filename
     */
    public function saveStringToFile(string $string): bool
    {
        $res = null;
        if (!$this->get('value')) {
            $this->createFileName('UnnamedFile');
        }
        return (bool) file_put_contents($this->getFullFilePath(), $string);
    }
}
