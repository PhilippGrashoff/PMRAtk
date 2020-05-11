<?php

namespace PMRAtk\View;

use atk4\core\AppScopeTrait;
use PMRAtk\Data\File;

class FileDownload
{
    use AppScopeTrait;

    public $paramNameForCryptID = 'fileid';

    public $currentFileName = '';

    public $currentFilePath = '';


    /**
     * FileDownload constructor.
     */
    public function __construct(\atk4\ui\App $app)
    {
        $this->app = $app;
    }


    /**
     *
     */
    public function sendFile()
    {
        if (isset($_REQUEST[$this->paramNameForCryptID])) {
            $file = new File($this->app->db);
            $file->tryLoadBy('crypt_id', $_REQUEST[$this->paramNameForCryptID]);
            if (!$file->loaded()) {
                $this->_failure();
                return;
            }
            $this->currentFilePath = $file->getFullFilePath();
            $this->currentFileName = $file->get('value');
            $this->_sendFile();
        } else {
            $this->_failure();
        }
    }


    /**
     *
     */
    protected function _failure(int $errorCode = 404): void
    {
        http_response_code($errorCode);
        return;
    }


    /**
     *
     */
    protected function _sendFile()
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"" . $this->currentFileName . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($this->currentFilePath));

        @readfile($this->currentFilePath);
    }
}