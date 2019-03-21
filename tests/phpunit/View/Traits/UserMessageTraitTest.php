<?php

class TestClassForUserMessageTrait {
    use \PMRAtk\View\Traits\UserMessageTrait;
}


class UserMessageTraitTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testAddMessage() {
        $v = new \TestClassForUserMessageTrait();
        $v->addUserMessage('TestMessage1', 'success');
        $this->assertEquals($v->userMessages[0]['message'], 'TestMessage1');
        $v->addUserMessage('TestMessage2');
        $v->addUserMessage('TestMessage3', 'error');
        $v->addUserMessage('TestMessage4', 'warning');
        $this->assertEquals(count($v->getUserMessagesAsJsToast()), 4);
        $this->assertTrue($v->getUserMessagesAsJsToast()[0] instanceOf \atk4\ui\jsToast);
        $htmlstring = $v->getUserMessagesAsHTML();
        $this->assertTrue(strpos($htmlstring, 'class="ui message') !== false);
        $inlinehtml = $v->getUserMessagesAsHTML(true);
        $this->assertTrue(strpos($inlinehtml, 'style="color:') !== false);
    }
}
