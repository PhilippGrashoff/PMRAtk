<?php

class FileDownLoadMock extends \PMRAtk\View\FileDownload {

    protected function _sendHeaders()
    {

    }
}


class FileDownloadTest extends \PMRAtk\tests\phpunit\TestCase
{

    /*
     *
     */
    public function testExitOnNoId()
    {
        ob_start();
        $fd = new \PMRAtk\View\FileDownload(self::$app);
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
    }


    /*
     *
     */
    public function testExitOnFileNotFound()
    {
        ob_start();
        $fd = new \PMRAtk\View\FileDownload(self::$app);
        $_REQUEST[$fd->paramName] = 'Duggu';
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
        unset($_REQUEST[$fd->paramName]);
    }


    /*
     *
     */
    public function testSendFile()
    {
        $file = new \PMRAtk\Data\File(self::$app->db);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownLoadMock(self::$app);
        $_REQUEST[$fd->paramName] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertTrue(strpos(ob_get_contents(), file_get_contents($file->getFullFilePath())) !== false);
        ob_end_clean();
        unset($_REQUEST[$fd->paramName]);
    }
}