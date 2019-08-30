<?php

namespace PMRAtk\View;

class FileDownload {

    public $paramName = 'fileid';

    public $app;


    public function __construct(\atk4\ui\App $app) {
        $this->app = $app;
    }

    /*
     *
     */
    public function sendFile() {
        if(!isset($_REQUEST[$this->paramName])) {
            return $this->_failure();
        }
        $file = new \PMRAtk\Data\File($this->app->db);
        $file->tryLoadBy('crypt_id', $_REQUEST[$this->paramName]);
        if(!$file->loaded()) {
            return $this->_failure();
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-type: application/octet-stream");
        header("Content-Disposition: attachment; filename=\"".$file->get('value')."\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".filesize($file->getFullFilePath()));
        @readfile($file->getFullFilePath());
    }


    /*
     *
     */
    protected function _failure() {

    }
}