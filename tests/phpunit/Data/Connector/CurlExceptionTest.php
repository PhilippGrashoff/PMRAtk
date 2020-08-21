<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Connector;


use PMRAtk\Data\Connector\CurlException;
use PMRAtk\tests\phpunit\TestCase;

/**
 *
 */
class CurlExceptionTest extends TestCase {

    /**
     *
     */
    public function testConstruct() {
        $e = new CurlException('SomeMessage', 404, '{error:"SomeError"}');
        $this->assertEquals($e->getParams(), ['response' => '{error:"SomeError"}']);
    }


    /**
     *
     */
    public function testConstructWithDataSent() {
        $e = new CurlException('SomeMessage', 404, '{error:"SomeError"}', 'SomeDataSent');
        $this->assertEquals($e->getParams()['data sent in body'], 'SomeDataSent');
    }


    /**
     *
     */
    public function testGetErrorMessageWithJSON() {
        $arr = [
            'error' => 'SomeErrorMessage',
            'code' => 404
        ];
        $e = new CurlException('SomeMessage', 404, json_encode($arr));
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }


    /**
     *
     */
    public function testGetErrorMessageResponseAlreadyDecodedToObject() {
        $o = new \StdClass();
        $o->error = 'SomeErrorMessage';
        $e = new CurlException('SomeMessage', 404, $o);
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }


    /**
     *
     */
    public function testGetErrorMessageWithJSONNoValidKey() {
        $arr = [
            'somekey' => 'SomeErrorMessage',
            'code' => 404
        ];
        $e = new CurlException('SomeMessage', 404, json_encode($arr));
        $this->assertEquals('SomeMessage', $e->getErrorMessage());
    }


    /**
     *
     */
    public function testGetErrorMessageWithNestedJSON() {
        $arr = [
            'error' => [
                'message' => 'SomeErrorMessage',
                'code' => 404,
            ],
        ];
        $e = new CurlException('SomeMessage', 404, json_encode($arr));
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }


    /**
     *
     */
    public function testGetErrorMessageWithString() {
        $e = new CurlException('SomeMessage', 404, 'SomeErrorMessage');
        $this->assertEquals('SomeErrorMessage', $e->getErrorMessage());
    }
}
