<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View;

use PMRAtk\Data\File;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\FileDownload;
use PMRAtk\View\FileDownloadInline;

class FileDownloadTest extends TestCase
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
        $fd = new FileDownload(self::$app);
        $_REQUEST[$fd->paramNameForCryptID] = 'Duggu';
        $fd->sendFile();
        self::assertEquals('', ob_get_contents());
        ob_end_clean();
        unset($_REQUEST[$fd->paramNameForCryptID]);
        self::assertEquals(http_response_code(), 404);
    }


    /**
     * @runInSeparateProcess
     */
    public function testSendFileByCryptId()
    {
        $file = new File(self::$app->db);
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
        $file = new File(self::$app->db);
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
}