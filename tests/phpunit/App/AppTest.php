<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\App;

use auditforatk\Audit;
use PMRAtk\Data\Email\EmailTemplate;
use settingsforatk\Setting;
use PMRAtk\Data\Token;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelA;
use PMRAtk\tests\TestClasses\BaseModelClasses\BaseModelB;
use PMRAtk\App\App;
use PMRAtk\View\Template;
use PMRAtk\Data\User;
use atk4\data\Exception;
use traitsforatkdata\TestCase;


class AppTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        User::class,
        Token::class,
        Audit::class
    ];

    public function testAppConstruct()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        self::assertTrue($app->auth->user instanceof User);
    }

    public function testPMRAtkTemplateClassIsReturnedInLoadTemplate()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $t = $app->loadTemplate(FILE_BASE_PATH . '/template/email/default_footer.html');
        self::assertInstanceOf(Template::class, $t);
    }

    public function testExceptionEmptyRoleArray()
    {
        self::expectException(Exception::class);
        $app = new App([], ['always_run' => false]);
    }

    public function testTokenLogin()
    {
        $persistence = $this->getSqliteTestPersistence();
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $persistence;
        $user = new User($persistence);
        $user->save();
        $token = new Token($persistence, ['parentObject' => $user]);
        $token->save();
        $app->loadUserByToken($token->get('value'));

        self::assertTrue($app->auth->user->loaded());
    }

    public function testTokenLoginTokenNotFoundException()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        self::expectException(Exception::class);
        $app->loadUserByToken('sfsdfssdfeg');
    }

    public function testTokenLoginUserForTokenNotFoundException()
    {
        $persistence = $this->getSqliteTestPersistence();
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $persistence;
        $token = new Token($persistence);
        $token->save();
        self::expectException(Exception::class);
        $app->loadUserByToken($token->get('value'));
    }

    public function testaddSummerNote()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->addSummernote();
        //TODO: ADD Sensible check here!
        self::assertTrue($app->auth->user instanceof \PMRAtk\Data\User);
    }


    /*
     *
     */
    public function testDeviceWidth()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $_SESSION['device_width'] = 500;
        $app->getDeviceWidth();
        self::assertEquals(500, $app->deviceWidth);
        $_POST['device_width'] = 800;
        $app->getDeviceWidth();
        self::assertEquals(800, $app->deviceWidth);
    }


    /*
     *
     */
    public function testgetEmailTemplateExceptionIfTemplateNotFound()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        self::expectException(Exception::class);
        $app->loadEmailTemplate('DDFUSFsfdfse');
    }


    /*
     *
     */
    public function testgetEmailTemplateFromSavedEmailTemplate()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        $app->saveEmailTemplate('DUDU', '<div>Miau</div>');
        $t = $app->loadEmailTemplate('DUDU');
        self::assertEquals('<div>Miau</div>', $t->render());
    }


    /*
     *
     */
    public function testgetEmailTemplateRawString()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $app->saveEmailTemplate('DUDU', '<div>Miau{$somevar}</div>');
        $t = $app->loadEmailTemplate('DUDU', true);
        self::assertEquals('<div>Miau{$somevar}</div>', $t);
    }



    /*
     *
     */
    public function testSaveEmailTemplate()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $initial_count = $this->countModelRecords('\\PMRAtk\\Data\\Email\\EmailTemplate');
        //should create a new one
        $app->saveEmailTemplate('SOME', 'AndSomeValie');
        self::assertEquals($initial_count + 1, $this->countModelRecords('\\PMRAtk\\Data\\Email\\EmailTemplate'));
        //shouldnt create a new one
        $app->saveEmailTemplate('SOME', 'AndSomeOtherValue');
        self::assertEquals($initial_count + 1, $this->countModelRecords('\\PMRAtk\\Data\\Email\\EmailTemplate'));
        //see if value is stored
        $et = new EmailTemplate(self::$app->db);
        $et->loadBy('ident', 'SOME');
        self::assertEquals('AndSomeOtherValue', $et->get('value'));

        //should create a new one
        $app->saveEmailTemplate('SOMEOTHERIDENT', 'AndSomeOtherValue');
        self::assertEquals($initial_count + 2, $this->countModelRecords('\\PMRAtk\\Data\\Email\\EmailTemplate'));
    }


    /*
     *
     */
    public function testLoadTemplateException()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        self::expectException(\atk4\ui\Exception::class);
        $app->loadTemplate('SomeNonExistantModel');
    }


    /*
     *
     */
    public function testloadEmailTemplateRawFromFile()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $t = $app->loadEmailTemplate('testemailtemplate.html', true);
        self::assertTrue(strpos($t, '{$testtag}') !== false);
    }


    /*
     *
     */
    public function testloadTemplateWithFilePath()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $t = $app->loadTemplate(FILE_BASE_PATH . '/template/email/default_footer.html');
        self::assertEquals('</div>' . PHP_EOL . '</body>' . PHP_EOL . '</html>', $t->render());
    }


    /*
     *
     */
    public function testsaveAndLoadEmailTemplateFromModel()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;

        //initial state should be same as file, as file should be loaded
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //now save a custom template
        $app->saveEmailTemplate('testemailtemplate.html', 'DugguWuggu');
        self::assertEquals('DugguWuggu', $app->loadEmailTemplate('testemailtemplate.html', true));

        //now save a custom template for a model. When loaded without these params,  it should still return the general one
        $ba = new BaseModelA(self::$app->db);
        $ba->save();
        $app->saveEmailTemplate('testemailtemplate.html', 'Migasalasa', get_class($ba), $ba->get('id'));
        self::assertEquals('DugguWuggu', $app->loadEmailTemplate('testemailtemplate.html', true));

        //when loading with the model_class and model_id params it should find the one saved for the record
        self::assertEquals('Migasalasa', $app->loadEmailTemplate('testemailtemplate.html', true, [$ba]));

        //when loading an invalid class or id, fall back to general one
        $bb = new BaseModelB(self::$app->db);
        $bb->save();
        self::assertEquals('DugguWuggu', $app->loadEmailTemplate('testemailtemplate.html', true, [$bb]));
    }


    /*
     *
     */
    public function testLoadEmailTemplateExceptionModelNotLoaded()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $bb = new BaseModelB(self::$app->db);
        self::expectException(Exception::class);
        self::assertEquals('DugguWuggu', $app->loadEmailTemplate('testemailtemplate.html', true, [$bb]));
    }


    /*
     * If only a custom template is set for a specific model_class and model_id, see if this is not accidently loaded
     * for another model_id
     */
    public function testLoadEmailTemplateLoadFromFileIfInDBOnlyPerModel()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = self::$app->db;
        $ba1 = new BaseModelA(self::$app->db);
        $ba1->save();
        $ba2 = new BaseModelA(self::$app->db);
        $ba2->save();
        //initial state should be same as file, as file should be loaded
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //now save a custom template for a model. When loaded without these params,  it should still return the general one
        $app->saveEmailTemplate('testemailtemplate.html', 'Migasalasa', get_class($ba1), $ba1->get('id'));
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //also for any other model id
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true, [$ba2])
        );

        //but for that special ID return that custom one
        self::assertEquals('Migasalasa', $app->loadEmailTemplate('testemailtemplate.html', true, [$ba1]));
    }


    /*
     *
     */
    public function testSendEmailToAdmin()
    {
        $this->_addStandardEmailAccount();
        $s = new Setting(self::$app->db);
        $s->set('ident', 'STD_EMAIL');
        $s->set('value', 'test2@easyoutdooroffice.com');
        self::$app->addSetting($s);
        $s = new Setting(self::$app->db);
        $s->set('ident', 'STD_EMAIL_NAME');
        $s->set('value', 'HANSI PETER');
        self::$app->addSetting($s);
        $b = new BaseModelA(self::$app->db);
        $b->set('name', 'Laduggu');
        $e = self::$app->sendEmailToAdmin(
            'Test:LALA',
            'Hans {$he} ist Super {$te} {$basemodela_name}',
            ['he' => '22', 'te' => '33'],
            [$b]
        );
        self::assertTrue(strpos($e->phpMailer->getSentMIMEMessage(), 'Hans 22 ist Super 33 Laduggu') !== false);
    }
}
