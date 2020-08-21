<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\tests\phpunit\TestCase;


/**
 *
 */
class BaseModelTest extends TestCase {

    /**
     *
     */
    public function testExceptionIfThisNotLoaded() {
        $u = new BaseModelA(self::$app->db);
        $u->save();
        $this->callProtected($u, '_exceptionIfThisNotLoaded', []);
        $u->unload();
        $this->expectException(\atk4\data\Exception::class);
        $this->callProtected($u, '_exceptionIfThisNotLoaded', []);
    }


    /**
     *
     */
    public function testLoadedHasOneRef() {
        $b = new BaseModelB(self::$app->db);
        $b->save();
        $u = new BaseModelA(self::$app->db);
        $u->set('BaseModelB_id', $b->get('id'));
        $u->save();
        $ref = $u->loadedHasOneRef('BaseModelB_id');
        $this->assertEquals($b->get('id'), $ref->get('id'));
        $b->delete();
        $this->expectException(\atk4\data\Exception::class);
        $u->loadedHasOneRef('BaseModelB_id');
    }


    /**
     *
     */
    public function testLoadedHasOneRefFieldEmpty() {
        $u = new BaseModelA(self::$app->db);
        $u->save();
        $this->expectException(\atk4\data\Exception::class);
        $u->loadedHasOneRef('BaseModelB_id');
    }
}
