<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View\Traits;

use atk4\ui\jsToast;
use atk4\ui\View;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\App;
use PMRAtk\View\Traits\NotifyTrait;

/**
 * Class TestViewForNotify
 */
class TestViewForNotify extends View {
    use NotifyTrait;
}


/*
 *
 */
class NotifyTraitTest extends TestCase {


    /*
     *
     */
    public function testNotifySuccess() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForNotify());
        $res = $v->successNotify('Juhu');
        $this->assertTrue($res instanceOf jsToast);
    }

    /*
     *
     */
    public function testNotifyError() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForNotify());
        $res = $v->failNotify('Juhu');
        $this->assertTrue($res instanceOf jsToast);
    }

    /*
     *
     */
    public function testNotifyWarning() {
        $app = new App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForNotify());
        $res = $v->warningNotify('Juhu');
        $this->assertTrue($res instanceOf jsToast);
    }
}
