<?php

namespace PMRAtk\tests\phpunit\View;

use PMRAtk\View\FileDownload;
use PMRAtk\View\FileDownloadInline;

class FileDownloadTest extends \PMRAtk\tests\phpunit\TestCase
{

    /**
     *
     */
    public function testExitOnNoId()
    {
        ob_start();
        $fd = new FileDownload(self::$app);
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
    }


    /**
     *
     */
    public function testExitOnFileNotFound()
    {
        ob_start();
        $fd = new \PMRAtk\View\FileDownload(self::$app);
        $_REQUEST[$fd->paramNameForCryptID] = 'Duggu';
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
        self::assertEquals(http_response_code(), 404);
    }


    /**
     *
     */
    public function testExitOnFileNotFoundByPath()
    {
        ob_start();
        $fd = new \PMRAtk\View\FileDownload(self::$app);
        $_REQUEST[$fd->paramNameForFileURL] = 'Duggu';
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForFileURL]);
        self::assertEquals(http_response_code(), 404);
    }


    /**
     * @runInSeparateProcess
     */
    public function testSendFileByCryptId()
    {
        $file = new \PMRAtk\Data\File(self::$app->db);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownLoad(self::$app);
        $_REQUEST[$fd->paramNameForCryptID] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
    }


    /**
     * @runInSeparateProcess
     */
    public function testSendInlineFileByCryptId()
    {
        $file = new \PMRAtk\Data\File(self::$app->db);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownloadInline(self::$app);
        $_REQUEST[$fd->paramNameForCryptID] = $file->get('crypt_id');
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
    }


    /**
     * @runInSeparateProcess
     */
    public function testSendFileByFilePath()
    {
        $file = new \PMRAtk\Data\File(self::$app->db);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownLoad(self::$app);
        $_REQUEST[$fd->paramNameForFileURL] = 'tests/demo_file.txt';
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForFileURL]);
    }


    /**
     * @runInSeparateProcess
     */
    public function testSendFileByFilePathWithFullPath()
    {
        $file = new \PMRAtk\Data\File(self::$app->db);
        $file->set('value', 'demo_file.txt');
        $file->set('path', 'tests/');
        $file->save();

        ob_start();
        $fd = new FileDownLoad(self::$app);
        $_REQUEST[$fd->paramNameForFileURL] = urlencode(self::$app->getSetting('URL_BASE_PATH')) . 'tests/demo_file.txt';
        @$fd->sendFile();
        self::assertNotFalse(
            strpos(
                ob_get_contents(),
                file_get_contents($file->getFullFilePath())
            )
        );
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForFileURL]);
    }
}