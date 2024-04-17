<?php declare(strict_types=1);

namespace PMRAtk\Data\Email;

use atk4\core\AppScopeTrait;
use atk4\core\DIContainerTrait;
use atk4\core\Exception;
use atk4\ui\App;
use Throwable;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Connection\Protocols\ImapProtocol;
use Webklex\PHPIMAP\IMAP;
use Webklex\PHPIMAP\Message;

class PHPMailer extends \PHPMailer\PHPMailer\PHPMailer
{

    use DIContainerTrait;
    use AppScopeTrait;

    //the PMRAtk\Data\Email\EmailAccount to send from. If not set, use first one
    public $emailAccount;

    //header and footer which will be added to email before send
    public $header;
    public $footer;
    public $headerTemplate = 'default_header.html';
    public $footerTemplate = 'default_footer.html';

    public $addImapDebugInfo = false;
    public $imapErrors = [];
    public $appendedByIMAP = false;


    public function __construct(App $app, array $defaults = [])
    {
        $this->app = $app;
        $this->setDefaults($defaults);
        $this->CharSet = 'utf-8';
        //set SMTP sending
        $this->isSMTP();
        $this->SMTPDebug = 0;
        $this->SMTPAuth = true;

        parent::__construct();

        $this->header = $this->app->loadEmailTemplate($this->headerTemplate);
        $this->header->setSTDValues();
        $this->footer = $this->app->loadEmailTemplate($this->footerTemplate);
        $this->footer->setSTDValues();
    }

    public function setBody(string $body): void
    {
        $this->Body = $this->header->render() . $body . $this->footer->render();
        $this->AltBody = $this->html2text($this->Body);
    }

    public function send(): bool
    {
        $this->_setEmailAccount();
        return parent::send();
    }

    protected function _setEmailAccount(): void
    {
        if ($this->emailAccount instanceof EmailAccount
            && $this->emailAccount->loaded()) {
            $this->_copySettingsFromEmailAccount();
            return;
        } //maybe just the ID of the emailaccount was passed?
        elseif (is_scalar($this->emailAccount)) {
            $val = $this->emailAccount;
            $this->emailAccount = new EmailAccount($this->app->db, ['enableInternalAccounts' => true]);
            if ($val) {
                $this->emailAccount->tryLoad($val);
                if ($this->emailAccount->loaded()) {
                    $this->_copySettingsFromEmailAccount();
                    return;
                }
            }
        }

        //none found? load default
        $this->emailAccount = new EmailAccount($this->app->db);
        $this->emailAccount->tryLoadAny();

        if (!$this->emailAccount->loaded()) {
            throw new Exception('No EmailAccount to send from found!');
        }
        $this->_copySettingsFromEmailAccount();
    }

    protected function _copySettingsFromEmailAccount(): void
    {
        $this->Host = $this->emailAccount->get('smtp_host');
        $this->Port = $this->emailAccount->get('smtp_port');
        $this->Username = $this->emailAccount->get('user');
        $this->Password = $this->emailAccount->get('password');
        $this->setFrom($this->emailAccount->get('name'), $this->emailAccount->get('sender_name'));
        if ($this->emailAccount->get('allow_self_signed_ssl')) {
            $this->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
        }
    }

    /**
     * add Email to IMAP if set
     */
    public function addSentEmailByIMAP(): bool
    {
        $this->_setEmailAccount();
        if (!$this->checkImapSet()) {
            return false;
        }

        try {
            $client = $this->createImapClient();
            $this->appendedByIMAP =  $client->connection->appendMessage(
                $this->emailAccount->get('imap_sent_folder'),
                $this->getSentMIMEMessage()
            );
        } catch (Throwable $e) {
            $this->appendedByIMAP = false;
            $this->app->sendErrorEmailToEooTechAdmin($e, 'Eim IMAP-Fehler ist aufgetreten');
        }

        return $this->appendedByIMAP;
    }

    protected function checkImapSet(): bool
    {
        if (
            !$this->emailAccount->get('imap_host')
            || !$this->emailAccount->get('imap_port')
        ) {
            return false;
        }

        return true;
    }

    protected function createImapClient(): Client
    {
        $cm = new ClientManager();
        $client = $cm->make(
            [
                'host' => $this->emailAccount->get('imap_host'),
                'port' => $this->emailAccount->get('imap_port'),
                'encryption' => $this->emailAccount->get('imap_port') == 993 ? 'ssl' : 'starttls',
                'validate_cert' => true,
                'username' => $this->emailAccount->get('user'),
                'password' => $this->emailAccount->get('password'),
                'protocol' => 'imap'
            ]
        );

        $client->connect();

        return $client;
    }
}