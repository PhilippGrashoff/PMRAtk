<?php

class SomeConnector {

    use \PMRAtk\Data\Connector\CurlTrait;

    public $curl;

    public function __construct() {
        $this->curl = new \Curl\Curl();
    }
}


class CurlTraitTest extends \PMRAtk\tests\phpunit\TestCase {


    /*
     *
     */
    public function testNormalResponse() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $this->assertEquals('LALA', $sc->returnCurlResponse());
    }


    /*
     *
     */
    public function testExceptionOnNetworkError() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $sc->curl->curlError = 'SomeNetworkFail';
        $this->expectException(\PMRAtk\Data\Connector\CurlException::class);
        $sc->returnCurlResponse();
    }


    /*
     *
     */
    public function testExceptionOnErrorResponse() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $sc->curl->error = 'SomeNot200Response';
        $this->expectException(\PMRAtk\Data\Connector\CurlException::class);
        $sc->returnCurlResponse();
    }
}
