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
    public function testGetErrorMessageWithJSON() {
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
    public function testGetErrorMessageResponseAlreadyDecodedToObject() {
        $o = new \StdClass();
        $o->error = 'SomeErrorMessage';
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, $o);
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }


    /*
     *
     */
    public function testGetErrorMessageWithJSONNoValidKey() {
        $arr = [
            'somekey' => 'SomeErrorMessage',
            'code' => 404
        ];
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, json_encode($arr));
        $this->assertEquals('SomeMessage', $e->getErrorMessage());
    }


    /*
     *
     */
    public function testGetErrorMessageWithNestedJSON() {
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
    public function testGetErrorMessageWithString() {
        $e = new \PMRAtk\Data\Connector\CurlException('SomeMessage', 404, 'SomeErrorMessage');
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }
}
