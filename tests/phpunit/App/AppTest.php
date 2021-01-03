<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\App;

use atk4\data\Exception;
use atk4\ui\Layout\Admin;
use auditforatk\Audit;
use PMRAtk\App\App;
use PMRAtk\Data\Email\BaseEmail;
use PMRAtk\Data\Email\EmailAccount;
use PMRAtk\Data\Email\EmailTemplate;
use PMRAtk\Data\Token;
use PMRAtk\Data\User;
use PMRAtk\tests\phpunit\TestCase;
use PMRAtk\tests\TestClasses\BaseModelClasses\JustABaseModel;
use PMRAtk\tests\TestClasses\BaseModelClasses\ModelWithEPA;
use PMRAtk\View\Template;
use settingsforatk\Setting;
use settingsforatk\SettingGroup;


class AppTest extends TestCase
{

    protected $sqlitePersistenceModels = [
        User::class,
        Token::class,
        Audit::class,
        EmailTemplate::class,
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
        $user->set('username', 'LALA');
        $user->save();
        $token = new Token($persistence, ['parentObject' => $user]);
        $token->save();
        $app->loadUserByToken($token->get('value'));

        self::assertTrue($app->auth->user->loaded());
    }

    public function testTokenLoginTokenNotFoundException()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
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
        $app->initLayout([Admin::class]);
        $app->addSummernote();
        //TODO: ADD Sensible check here!
        self::assertTrue($app->auth->user instanceof \PMRAtk\Data\User);
    }

    public function testDeviceWidth()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $_SESSION['device_width'] = 500;
        $app->setDeviceWithFromRequest();
        self::assertEquals(500, $app->deviceWidth);
        $_POST['device_width'] = 800;
        $app->setDeviceWithFromRequest();
        self::assertEquals(800, $app->deviceWidth);
    }

    public function testgetEmailTemplateExceptionIfTemplateNotFound()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        self::expectException(Exception::class);
        $app->loadEmailTemplate('DDFUSFsfdfse');
    }

    public function testgetEmailTemplateFromSavedEmailTemplate()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        $app->saveEmailTemplate('DUDU', '<div>Miau</div>');
        $t = $app->loadEmailTemplate('DUDU');
        self::assertEquals('<div>Miau</div>', $t->render());
    }

    public function testgetEmailTemplateRawString()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        $app->saveEmailTemplate('DUDU', '<div>Miau{$somevar}</div>');
        $t = $app->loadEmailTemplate('DUDU', true);
        self::assertEquals('<div>Miau{$somevar}</div>', $t);
    }

    public function testSaveEmailTemplate()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        $initial_count = (new EmailTemplate($app->db))->action('count')->getOne();
        //should create a new one
        $app->saveEmailTemplate('SOME', 'AndSomeValie');
        self::assertEquals(
            $initial_count + 1,
            (new EmailTemplate($app->db))->action('count')->getOne()
        );

        //shouldnt create a new one
        $app->saveEmailTemplate('SOME', 'AndSomeOtherValue');
        self::assertEquals(
            $initial_count + 1,
            (new EmailTemplate($app->db))->action('count')->getOne()
        );
        //see if value is stored
        $et = new EmailTemplate($app->db);
        $et->loadBy('ident', 'SOME');

        self::assertEquals(
            'AndSomeOtherValue',
            $et->get('value')
        );

        //should create a new one
        $app->saveEmailTemplate('SOMEOTHERIDENT', 'AndSomeOtherValue');
        self::assertEquals(
            $initial_count + 2,
            (new EmailTemplate($app->db))->action('count')->getOne()
        );
    }

    public function testLoadTemplateException()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        self::expectException(\atk4\ui\Exception::class);
        $app->loadTemplate('SomeNonExistantModel');
    }

    public function testloadEmailTemplateRawFromFile()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        $t = $app->loadEmailTemplate('testemailtemplate.html', true);
        self::assertTrue(strpos($t, '{$testtag}') !== false);
    }

    public function testloadTemplateWithFilePath()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $t = $app->loadTemplate(FILE_BASE_PATH . '/template/email/default_footer.html');
        self::assertEquals('</div>' . PHP_EOL . '</body>' . PHP_EOL . '</html>', $t->render());
    }

    public function testsaveAndLoadEmailTemplateFromModel()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence([JustABaseModel::class, ModelWithEPA::class]);

        //initial state should be same as file, as file should be loaded
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //now save a custom template
        $app->saveEmailTemplate('testemailtemplate.html', 'DugguWuggu');
        self::assertEquals(
            'DugguWuggu',
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //now save a custom template for a model. When loaded without these params,  it should still return the general one
        $model = new JustABaseModel($app->db);
        $model->save();
        $app->saveEmailTemplate('testemailtemplate.html', 'Migasalasa', get_class($model), $model->get('id'));
        self::assertEquals(
            'DugguWuggu',
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //when loading with the model_class and model_id params it should find the one saved for the record
        self::assertEquals(
            'Migasalasa',
            $app->loadEmailTemplate('testemailtemplate.html', true, [$model])
        );

        //when loading an invalid class or id, fall back to general one
        $otherModel = new ModelWithEPA($app->db);
        $otherModel->save();
        self::assertEquals(
            'DugguWuggu',
            $app->loadEmailTemplate('testemailtemplate.html', true, [$otherModel])
        );
    }

    public function testLoadEmailTemplateExceptionModelNotLoaded()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence();
        $bb = new JustABaseModel($app->db);
        self::expectException(Exception::class);
        self::assertEquals('DugguWuggu', $app->loadEmailTemplate('testemailtemplate.html', true, [$bb]));
    }

    public function testLoadEmailTemplateLoadFromFileIfInDBOnlyPerModel()
    {
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $this->getSqliteTestPersistence([JustABaseModel::class]);
        $model = new JustABaseModel($app->db);
        $model->save();
        $otherModel = new JustABaseModel($app->db);
        $otherModel->save();

        //initial state should be same as file, as file should be loaded
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //now save a custom template for a model. When loaded without these params,  it should still return the general one
        $app->saveEmailTemplate('testemailtemplate.html', 'Migasalasa', get_class($model), $model->get('id'));
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true)
        );

        //also for any other model id
        self::assertEquals(
            file_get_contents(FILE_BASE_PATH . '/template/email/testemailtemplate.html'),
            $app->loadEmailTemplate('testemailtemplate.html', true, [$otherModel])
        );

        //but for that special ID return that custom one
        self::assertEquals(
            'Migasalasa',
            $app->loadEmailTemplate('testemailtemplate.html', true, [$model])
        );
    }

    public function testSendEmailToAdmin()
    {
        $persistence = $this->getSqliteTestPersistence(
            [
                EmailAccount::class,
                Setting::class,
                SettingGroup::class,
                BaseEmail::class
            ]
        );
        $this->_addStandardEmailAccount($persistence);
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $persistence;
        $persistence->app = $app;
        $app->addSetting('STD_EMAIL', 'test2@easyoutdooroffice.com');
        $app->addSetting('STD_EMAIL_NAME', 'HANSI PETER');
        $b = new JustABaseModel($app->db);
        $b->set('name', 'Laduggu');
        $e = $app->sendEmailToAdmin(
            'Test:LALA',
            'Hans {$he} ist Super {$te} {$justabasemodel_name}',
            ['he' => '22', 'te' => '33'],
            [$b]
        );
        self::assertStringContainsString(
            'Hans 22 ist Super 33 Laduggu',
            $e->phpMailer->getSentMIMEMessage()
        );
    }

    public function testSendErrorEmailToAdmin()
    {
        $persistence = $this->getSqliteTestPersistence(
            [
                EmailAccount::class,
                Setting::class,
                SettingGroup::class,
                BaseEmail::class
            ]
        );
        $this->_addStandardEmailAccount($persistence);
        $app = new App(['nologin'], ['always_run' => false]);
        $app->db = $persistence;
        $persistence->app = $app;
        $s = new Setting($app->db);
        $app->addSetting('STD_EMAIL', 'test2@easyoutdooroffice.com');
        $app->addSetting('STD_EMAIL_NAME', 'HANSI PETER');

        $e = new Exception('SomeErrorMessage');
        self::assertTrue($app->sendErrorEmailToAdmin($e, 'Fehler', ['test3@easyoutdooroffice.com']));
        self::assertStringContainsString(
            'SomeErrorMessage',
            $app->phpMailer->getSentMIMEMessage()
        );
    }
}
