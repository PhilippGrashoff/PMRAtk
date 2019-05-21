<?php

class CurlExceptionTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testConstruct() {
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, '{error:"SomeError"}');
        $this->assertEquals($e->getParams(), ['response' => '{error:"SomeError"}']);
    }


    /*
     *
     */
    public function testGetErrorMessageWithJSONArray() {
        $arr = [
            'error' => 'SomeErrorMessage',
            'code' => 404
        ];
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, json_encode($arr));
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }


    /*
     *
     */
    public function testGetErrorMessageWithNestedJSONArray() {
        $arr = [
            'error' => [
                'message' => 'SomeErrorMessage',
                'code' => 404,
            ],
        ];
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, json_encode($arr));
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }



    /*
     *
     */
    public function testGetErrorMessageWithNestedJSONObject() {
        $o = new \StdClass();
        $x = new \StdClass();
        $x->message = 'SomeErrorMessage';
        $o->error = $x;
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, json_encode($o));
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }
}
