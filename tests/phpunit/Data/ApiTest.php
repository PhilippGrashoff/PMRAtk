<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use atk4\data\Model;
use Exception;
use PMRAtk\Data\Api;
use PMRAtk\tests\phpunit\TestCase;

class ApiTest extends TestCase {


    /**
     *
     */
    public function testApiNoTokenInRequest() {
        unset($_REQUEST['token']);

        try {
            $api = new Api(self::$app);
        }
        catch(Exception $e) {}

        self::assertTrue(true);
    }


    /**
     *
     */
    public function testApiNoTokenMatch() {
        $_REQUEST['token'] = '123456';

        try {
            $api = new Api(self::$app);
        }
        catch(Exception $e) {}

        $_REQUEST['token'] = null;
        self::assertTrue(true);
    }


    /**
     * test token login
     */
    public function testTokenLogin() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new Api(self::$app);

        $_REQUEST['token'] = null;
        self::assertTrue(true);
    }


    /*
     * test exportModel
     */
    public function testExportModel() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new Api(self::$app);

        $a = new BaseModelB(self::$app->db);
        $a->set('name', 'A');
        $a->save();

        $b = new BaseModelB(self::$app->db);
        $b->set('time_test', '10:00');
        $b->set('date_test', '2005-05-05');
        $b->set('name', 'A');
        $b->save();

        $export = $this->callProtected($api, 'exportModel', [$a]);
        //date and time fields should been converted for export
        self::assertEquals($export[0]['created_date'], $a->get('created_date')->format(DATE_ATOM));
        self::assertEquals($export[1]['time_test'], '10:00:00');
        self::assertEquals($export[1]['date_test'], '2005-05-05');
    }


    /*
     * test exportModel with models without and date time fields
     */
    public function testExportModelNoDateAndTimeFields() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new Api(self::$app);

        $noDateTimeClass = new class extends Model {
            public $table = 'SecondaryBaseModel';

            public function init(): void {
                parent::init();
                $this->addField('value', ['type' => 'string']);
            }
        };

        $a = new $noDateTimeClass(self::$app->db);
        $a->save();

        $export = $this->callProtected($api, 'exportModel', [$a]);

        self::assertTrue(isset($export[0]));
    }


    /*
     * test path gets ?... removed
     */
    public function testPathURLParamsRemoved() {
        //add token to logged in user
        $token = self::$app->auth->user->setNewToken();
        $_REQUEST['token'] = $token;

        $api = new Api(self::$app);
        $api->path = 'somepath/?token=Duggu';
        $this->callProtected($api, '_removeURLParamsFromPath');
        self::assertEquals($api->path, 'somepath/');
    }
}
