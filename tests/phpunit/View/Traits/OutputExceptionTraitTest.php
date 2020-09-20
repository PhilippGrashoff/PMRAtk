<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View\Traits;


use atk4\data\ValidationException;
use atk4\ui\View;
use PMRAtk\Data\UserException;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\App;
use PMRAtk\View\Traits\NotifyTrait;
use PMRAtk\View\Traits\OutputExceptionTrait;

/**
 * Class TestViewForOutputException
 * @package PMRAtk\tests\phpunit\View\Traits
 */
class TestViewForOutputException extends View {
    use OutputExceptionTrait;
    use NotifyTrait;
}


/**
 * Class OutputExceptionTraitTest
 * @package PMRAtk\tests\phpunit\View\Traits
 */
class OutputExceptionTraitTest extends TestCase {
    /*
     *
     */
    public function testOutputExceptionTraitDataException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new \atk4\data\Exception('Some Error');
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Ein technischer Fehler ist aufgetreten') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitUserException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new UserException('Some Error Duggu');
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Some Error Duggu') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitSingleValidationException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new ValidationException(['Some Error']);
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Some Error') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitMultipleValidationException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new ValidationException(['Some Error1', 'Some Error2']);
        }
        catch(\Exception $e) {
            $res = $v->outputException($e);
            self::assertTrue(strpos($res[0], 'Some Error1') !== false);
            self::assertTrue(strpos($res[1], 'Some Error2') !== false);
        }
    }


    /*
     *
     */
    public function testOutputExceptionTraitReturnAsNotifyException() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForOutputException());
        try {
            throw new ValidationException(['Some Error1', 'Some Error2']);
        }
        catch(\Exception $e) {
            $res = $v->outputExceptionAsJsNotify($e);
            self::assertEquals(2, count($res));
        }
    }
}
