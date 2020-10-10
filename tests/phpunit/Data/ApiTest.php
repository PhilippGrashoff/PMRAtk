<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;


use atk4\data\Model;
use atk4\data\Persistence;
use auditforatk\Audit;
use Exception;
use Laminas\Diactoros\Exception\InvalidArgumentException;
use Laminas\HttpHandlerRunner\Exception\EmitterException;
use PMRAtk\App\App;
use PMRAtk\Data\Api;
use PMRAtk\Data\Token;
use PMRAtk\Data\User;
use PMRAtk\tests\TestClasses\BaseModelClasses\ModelWithDateAndTimeFields;
use traitsforatkdata\TestCase;


/**
 * TODO: ALL THESE TESTS ARE KIND OF CRAP; REFACTOR AT SOME POINT TOGETHER WITH API CLASS
 */
class ApiTest extends TestCase
{

    private $app;

    protected $sqlitePersistenceModels = [
        Token::class,
        User::class,
        Audit::class
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->app = new App(['nologin'], ['always_run' => false]);
        $_REQUEST['token'] = null;
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $_REQUEST['token'] = null;
    }

    public function testApiNoTokenInRequest()
    {
        self::expectException(EmitterException::class);
        $api = new Api($this->app);
    }

    public function testApiNoTokenMatch()
    {
        self::expectException(InvalidArgumentException::class);
        $_REQUEST['token'] = '123456';
        $api = new Api($this->app);
    }

    public function testTokenLogin()
    {
        $persistence = $this->getSqliteTestPersistence();
        $user = $this->setUserAndToken($persistence);

        $this->app->db = $persistence;
        $api = new Api($this->app);

        self::assertSame(
            $user->get('id'),
            $this->app->auth->user->get('id')
        );
    }

    public function testExportModel()
    {
        $persistence = $this->getSqliteTestPersistence([ModelWithDateAndTimeFields::class]);
        $user = $this->setUserAndToken($persistence);
        $this->app->db = $persistence;

        $api = new Api($this->app);

        $record1 = new ModelWithDateAndTimeFields($persistence);
        $record1->set('date', '2020-05-01');
        $record1->set('time', '11:11:11');
        $record1->set('datetime', '2020-05-01 11:11:11');
        $record1->save();

        $record2 = new ModelWithDateAndTimeFields($persistence);
        $record2->set('date', '2020-05-12');
        $record2->save();


        $export = $this->callProtected($api, 'exportModel', $record1);

        //date and time fields should been converted for export
        self::assertEquals($export[0]['date'], '2020-05-01');
        self::assertEquals($export[0]['time'], '11:11:11');
        self::assertEquals($export[0]['datetime'], (new \DateTime('2020-05-01T11:11:11'))->format(DATE_ATOM));
        self::assertEquals($export[1]['date'], '2020-05-12');
        self::assertEquals($export[1]['time'], '');
    }

    public function testPathURLParamsRemoved()
    {
        $persistence = $this->getSqliteTestPersistence();
        $user = $this->setUserAndToken($persistence);
        $this->app->db = $persistence;

        $api = new Api($this->app);
        $api->path = 'somepath/?token=Duggu';
        $this->callProtected($api, '_removeURLParamsFromPath');
        self::assertEquals($api->path, 'somepath/');
    }

    protected function setUserAndToken(Persistence $persistence): User {
        $user = new User($persistence);
        $user->set('username', 'SOMENAME');
        $user->save();
        $token = new Token($persistence, ['parentObject' => $user]);
        $token->save();

        $_REQUEST['token'] = $token->get('value');

        return $user;
    }
}
