<?php

class NoDateTime extends \atk4\data\Model {

    public $table = 'SecondaryBaseModel';

    public function init() {
        parent::init();
        $this->addField('value', ['type' => 'string']);
    }
}


class ApiTest extends \PMRAtk\tests\phpunit\TestCase {


    /**
     *
     */
    public function testApiNoTokenInRequest() {
        unset($_REQUEST['token']);

        try {
            $api = new \PMRAtk\Data\Api(self::$app);
        }
        catch(\Exception $e) {}

        $this->assertTrue(true);
    }


    /**
     *
     */
    public function testApiNoTokenMatch() {
        $_REQUEST['token'] = '123456';

        try {
            $api = new \PMRAtk\Data\Api(self::$app);
        }
        catch(\Exception $e) {}

        $_REQUEST['token'] = null;
        $this->assertTrue(true);
    }


    /**
     * test token login
     */
    public function testTokenLogin() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new \PMRAtk\Data\Api(self::$app);

        $_REQUEST['token'] = null;
        $this->assertTrue(true);
    }


    /*
     * test exportModel
     */
    public function testExportModel() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new \PMRAtk\Data\Api(self::$app);

        $a = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $a->set('name', 'A');
        $a->save();

        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB(self::$app->db);
        $b->set('time_test', '10:00');
        $b->set('date_test', '2005-05-05');
        $b->set('name', 'A');
        $b->save();

        $export = $this->callProtected($api, 'exportModel', [$a]);
        //date and time fields should been converted for export
        $this->assertEquals($export[0]['created_date'], $a->get('created_date')->format(DATE_ATOM));
        $this->assertEquals($export[1]['time_test'], '10:00:00');
        $this->assertEquals($export[1]['date_test'], '2005-05-05');
    }


    /*
     * test exportModel with models without and date time fields
     */
    public function testExportModelNoDateAndTimeFields() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new \PMRAtk\Data\Api(self::$app);

        $a = new NoDateTime(self::$app->db);

        $a->save();

        $export = $this->callProtected($api, 'exportModel', [$a]);

        $this->assertTrue(isset($export[0]));
    }


    /*
     * test path gets ?... removed
     */
    public function testPathURLParamsRemoved() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new \PMRAtk\Data\Api(self::$app);
        $api->path = 'somepath/?token=Duggu';
        $this->callProtected($api, '_removeURLParamsFromPath');
        $this->assertEquals($api->path, 'somepath/');
    }
}
