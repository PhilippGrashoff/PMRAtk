<?php

class TestViewForNotify extends \atk4\ui\View {
    use \PMRAtk\View\Traits\NotifyTrait;
}


class NotifyTraitTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testNotifySuccess() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForNotify());
        $res = $v->successNotify('Juhu');
        $this->assertTrue($res instanceOf \atk4\ui\jsToast);
    }

    /*
     *
     */
    public function testNotifyError() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForNotify());
        $res = $v->failNotify('Juhu');
        $this->assertTrue($res instanceOf \atk4\ui\jsToast);
    }

    /*
     *
     */
    public function testNotifyWarning() {
        $app = new \PMRAtk\View\App(['nologin'], ['always_run' => false]);
        $v   = $app->add(new TestViewForNotify());
        $res = $v->warningNotify('Juhu');
        $this->assertTrue($res instanceOf \atk4\ui\jsToast);
    }
}
