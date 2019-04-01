<?php

class FileReferenceTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     * tests the addRecipient and removeRecipient Function passing various params
     */
    public function testAddUploadedFile() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $m->save();
        $m->addUploadFileFromAtkUi('error');
        $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']);
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $this->assertEquals(null, $m->addUploadFileFromAtkUi(['name' => 'ALAL', 'tmp_name' => 'HEHFDF']));
    }


    /*
     *
     */
    public function testRemoveFile() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $m->save();
        $f = $this->createTestFile('Hansi', '', $m);
        $this->assertEquals($m->ref('File')->action('count')->getOne(), 1);
        $m->removeFile($f->get('id'));
        $this->assertEquals($m->ref('File')->action('count')->getOne(), 0);
    }


    /*
     * trying to delete a non-related file using removeFile will throw exception
     */
    public function testExceptionNonExistingFile() {
        $m = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $m->save();
        $this->expectException(\PMRAtk\Data\UserException::class);
        $m->removeFile(23432543635);
    }
}
