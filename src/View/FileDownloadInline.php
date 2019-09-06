<?php

namespace PMRAtk\View;

class FileDownloadInline extends FileDownload {

    /**
     * @codeCoverageIgnore
     */
    protected function _sendHeaders() {
        header('Content-Type: '.mime_content_type($this->file->getFullFilePath()));
        header('Content-Length: '.filesize($this->file->getFullFilePath()));
        header('Content-Disposition: inline; filename="'.$this->file->get('value').'"');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: 0');
    }
}