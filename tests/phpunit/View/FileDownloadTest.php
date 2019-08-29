<?php

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
}