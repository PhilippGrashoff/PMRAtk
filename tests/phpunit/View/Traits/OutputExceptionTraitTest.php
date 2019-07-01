<?php

class TestViewForOutputException extends \atk4\ui\View {
    use \PMRAtk\View\Traits\OutputExceptionTrait;
    use \PMRAtk\View\Traits\NotifyTrait;
}


class OutputExceptionTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testOutputExceptionTraitDataException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \atk4\data\Exception('Some Error');
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            $this->assertTrue(strpos($res[0], 'Ein technischer Fehler ist aufgetreten') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitUserException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \PMRAtk\Data\UserException('Some Error Duggu');
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            $this->assertTrue(strpos($res[0], 'Some Error Duggu') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitSingleValidationException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \atk4\data\ValidationException(['Some Error']);
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            $this->assertTrue(strpos($res[0], 'Some Error') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitMultipleValidationException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \atk4\data\ValidationException(['Some Error1', 'Some Error2']);
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            $this->assertTrue(strpos($res[0], 'Some Error1') !== false);
            $this->assertTrue(strpos($res[1], 'Some Error2') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitReturnAsNotifyException() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \atk4\data\ValidationException(['Some Error1', 'Some Error2']);
        }
        catch(\Exception $e) {
            $res = $v->outputExceptionAsJsNotify($e);
            $this->assertEquals(2, count($res));
        }
    }
}
