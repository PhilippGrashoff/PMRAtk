<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\View\Traits;


use atk4\ui\jsToast;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\View\Traits\UserMessageTrait;

/**
 * Class TestClassForUserMessageTrait
 * @package PMRAtk\tests\phpunit\View\Traits
 */
class TestClassForUserMessageTrait {
    use UserMessageTrait;
}


/**
 * Class UserMessageTraitTest
 * @package PMRAtk\tests\phpunit\View\Traits
 */
class UserMessageTraitTest extends TestCase {

    /*
     *
     */
    public function testAddMessage() {
        $v = new TestClassForUserMessageTrait();
        $v->addUserMessage('TestMessage1', 'success');
        self::assertEquals($v->userMessages[0]['message'], 'TestMessage1');
        $v->addUserMessage('TestMessage2');
        $v->addUserMessage('TestMessage3', 'error');
        $v->addUserMessage('TestMessage4', 'warning');
        self::assertEquals(count($v->getUserMessagesAsJsToast()), 4);
        self::assertTrue($v->getUserMessagesAsJsToast()[0] instanceOf jsToast);
        $htmlstring = $v->getUserMessagesAsHTML();
        self::assertTrue(strpos($htmlstring, 'class="ui message') !== false);
        $inlinehtml = $v->getUserMessagesAsHTML(true);
        self::assertTrue(strpos($inlinehtml, 'style="color:') !== false);
    }


    /*
     *
     */
    public function testSetDuration() {
        $v = new TestClassForUserMessageTrait();
        $v->addUserMessage('TestMessage1', 'success', 2000);
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(2000, $res[0]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'success', 0);
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(0, $res[1]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'success');
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(3000, $res[2]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'warning');
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(8000, $res[3]->settings['displayTime']);

        $v->addUserMessage('TestMessage1', 'error');
        $res = $v->getUserMessagesAsJsToast();
        self::assertEquals(8000, $res[4]->settings['displayTime']);
    }
}
