<?php

namespace PMRAtk\Data\Email;

class BaseEmail extends \atk4\data\Model {

    public $table = 'base_email';

    //A title: what is this email for
    public $title = '';

    //like title, but more descriptive: What is this email for
    public $description = '';

    //usually an Email is per Model record, e.g. per Group. Save in here to make work easier
    public $model;

    //the template to load to get initial subject and message
    public $template;

    //PHPMailer instance which takes care of the actual sending
    public $phpMailer;

    //HTML header
    public $header = '';

    //HTML footer
    public $footer = '';

    //callable to alter subject per recipient, gets recipient and subject template as param
    //function($recipient, $template) {}
    public $processSubjectPerRecipient;

    //callable to alter message per recipient, gets recipient and subject template as param
    //function($recipient, $template) {}
    public $processMessagePerRecipient;

    //callable to alter message template when loaded from template, gets template and model as param
    //function($template, $model) {}
    public $processMessageTemplate;

    //callable to alter subject template when loaded from template, gets template and model as param
    //function($template, $model) {}
    public $processSubjectTemplate;

    //callable which gets called when at least one send was successful, gets model as param
    //function($model) {}
    public $onSuccess;

    //record_id is a common param passed to emails
    public $recordId;

    //param1 is the second common param passed to emails
    public $param1;

    //if true, a message that the email was send is added to app's user messages.
    public $addUserMessageOnSend = true;

    //process things like model loading in init() or not
    public $process = true;



    /*
     * define fields and references
     */
    public function init() {
        parent::init();
        $this->addFields([
            ['subject',         'type' => 'string'],
            ['message',         'type' => 'text'],
            ['attachments',     'type' => 'array', 'serialize' => 'json'],
        ]);

        $this->hasMany('EmailRecipient', [EmailRecipient::class, 'their_field' => 'base_email_id']);

        //on delete, delete all recipients as well
        $this->addHook('beforeDelete', function($m) {
            $m->ref('EmailRecipient')->each('delete');
        });

        //try load default header and footer
        if(empty($this->header)) {
            $this->header = $this->app->loadEmailTemplate('default_header.html', true);
        }
        if(empty($this->footer)) {
            $this->footer = $this->app->loadEmailTemplate('default_footer.html', true);
        }

        $this->phpMailer = new PHPMailer($this->app);
    }


    /*
     * loads initial recipients, subject, message and attachments
     */
    public function loadInitialValues() {
        $this->loadInitialRecipients();
        $this->loadInitialAttachments();
        $this->loadInitialTemplate();
    }


    /*
     * overload in child classes
     */
    public function loadInitialRecipients() {}


    /*
     * overload in child classes
     */
    public function loadInitialAttachments() {}


    /*
     *
     */
    public function loadInitialTemplate() {
        if(!$this->template)  {
            return;
        }

        try {
            $template = $this->app->loadEmailTemplate($this->template);
        }
        catch(\Exception $e) {
            $template = new \PMRAtk\View\Template();
            $template->app = $this->app;
            $template->loadTemplateFromString($this->template);
        }

        $template->trySet('recipient_firstname', '{$recipient_firstname}');
        $template->trySet('recipient_lastname',  '{$recipient_lastname}');
        $template->trySet('recipient_email',     '{recipient_email}');

        if(is_callable($this->processMessageTemplate)) {
            call_user_func($this->processMessageTemplate, $template, $this->model);
        }

        $template->setSTDValues();

        //get subject from Template if available
        if($template->hasTag('Subject')) {
            $t_subject = $template->cloneRegion('Subject');
            $template->del('Subject');
            if(is_callable($this->processSubjectTemplate)) {
                call_user_func($this->processSubjectTemplate, $t_subject, $this->model);
            }
            $this->set('subject', $t_subject->render());
        }

        //add Custom signature per user
        $this->_loadUserSignature($template);

        $this->set('message', $template->render());
    }


    /*
     * replace signature from template with custom one from logged in user
     */
    protected function _loadUserSignature(\atk4\ui\Template $template) {
        if(!$template->hasTag('Signature')) {
            return;
        }

        //use EOOUser signature if available
        if(!empty($this->app->auth->user->getSignature())) {
            $template->del('Signature');
            $template->append('Signature', $this->app->auth->user->getSignature());
        }

        //if not, use standard signature if set
        elseif($this->app->getSetting('STD_EMAIL_SIGNATURE')) {
            $template->del('Signature');
            $template->append('Signature', $this->app->getSetting('STD_EMAIL_SIGNATURE'));
        }
    }


    /*
     * adds an object to recipients array.
     *
     * @param mixed class      Either a class, a classname or an email address to add
     * @param int   email_id   Try to load the email with this id if set
     *
     * @return bool            True if something was added, false otherwise
     */
    public function addRecipient($class, $email_id = null) {
        $r = null;

        //object passed: get Email from Email Ref
        if($class instanceOf \atk4\data\Model && $class->loaded()) {
            if($email_id === null) {
                $r = $this->_addRecipientObject($class);
            }
            elseif($email_id) {
                $r = $this->_addRecipientObject($class, $email_id);
            }
        }

        //id passed: ID of Email Address, load from there
        elseif(is_numeric($class)) {
            $r = $this->_addRecipientByEmailId(intval($class));
        }

        //else assume its email as string, not belonging to a stored model
        elseif(is_string($class) && filter_var($class, FILTER_VALIDATE_EMAIL)) {
            $r = new EmailRecipient($this->persistence);
            $r->set('email', $class);
        }

        if(!$r instanceOf EmailRecipient) {
            return false;
        }

        //if $this is not saved yet do so, so we can use $this->id for recipient
        if(!$this->get('id')) {
            $this->save();
        }

        //Make sure recipient for existing object is only added once
        foreach($this->ref('EmailRecipient') as $rec) {
            if($r->get('model_class') && $r->get('model_id') && $rec->get('model_class') == $r->get('model_class') && $rec->get('model_id') == $r->get('model_id')) {
                return false;
            }
        }

        $r->set('base_email_id', $this->get('id'));
        $r->save();

        return true;
    }


    /*
     * loads model_class, model_id, firstname and lastname from a passed object
     * returns an EmailRecipient object
     */
    protected function _addRecipientObject(\PMRAtk\Data\BaseModel $object, $email_id = null):?EmailRecipient {
        $r = new EmailRecipient($this->persistence);
        //set firstname and lastname if available
        $r->set('firstname', $object->hasField('firstname') ? $object->get('firstname') : '');
        $r->set('lastname',  $object->hasField('lastname') ? $object->get('lastname') : '');
        $r->set('model_class',  get_class($object));
        $r->set('model_id',  $object->get($object->id_field));

        //go for first email if no email_id was specified
        if($email_id == null && $e = filter_var($object->getFirstEmail(), FILTER_VALIDATE_EMAIL)) {
            $r->set('email', $e);
            return clone $r;
        }
        //else go for specified email id
        elseif($email_id && $e = filter_var($object->getEmailById($email_id), FILTER_VALIDATE_EMAIL)) {
            $r->set('email', $e);
            return clone $r;
        }

        return null;
    }


    /*
     * add a recipient by a specified Email id
     */
    protected function _addRecipientByEmailId(int $id):?EmailRecipient {
        $e = new \PMRAtk\Data\Email($this->persistence);
        $e->tryLoad($id);
        if(!$e->loaded()) {
            return null;
        }

        if($parent = $e->getParentObject()) {
            return $this->_addRecipientObject($parent);
        }

        return null;
    }


    /*
     * Removes an object from recipient array
     */
    public function removeRecipient($id):bool {
        foreach($this->ref('EmailRecipient') as $r) {
            if($r->get('id') == $id) {
                $r->delete();
                return true;
            }
        }

        return false;
    }


    /*
     *  adds a file object to the attachment array.
     *
     * @param object
     */
    public function addAttachment($id) {
        $a = $this->get('attachments');
        $a[] = $id;
        $this->set('attachments', $a);
    }


    /*
     * removes an attachment from the attachment array
     *
     * @param int
     */
    public function removeAttachment(int $id) {
        $a = $this->get('attachments');
        if(in_array($id, $a)) {
            unset($a[array_search($id, $a)]);
        }

        $this->set('attachments', $a);
    }


    /*
     * sends the message to each recipient in the list
     *
     * @return bool   true if at least one send was successful, false otherwise
     */
     public function send():bool {
        //superimportant, due to awful behaviour of ref() function we need to make
        //sure $this is loaded
        if(!$this->loaded()) {
            $this->save();
        }

        //create a template from message so tags set in message like
        //{$firstname} can be filled
        $mt = new \PMRAtk\View\Template();
        $mt->loadTemplateFromString($this->get('message'));

        $st = new \PMRAtk\View\Template();
        $st->loadTemplateFromString($this->get('subject'));

        //add Attachments
        if($this->get('attachments')) {
            $a_files = new \PMRAtk\Data\File($this->persistence);
            $a_files->addCondition('id', 'in', $this->get('attachments'));
            foreach($a_files as $a) {
                $this->phpMailer->addAttachment($a->getFullFilePath());
            }
        }

        //if email is sent to several recipients, keep SMTP connection open
        if(intval($this->ref('EmailRecipient')->action('count')->getOne()) > 1) {
            $this->phpMailer->SMTPKeepAlive = true;
        }

        $successful_send = false;
        //single send for each recipient
        foreach($this->ref('EmailRecipient') as $r) {
            //clone message and subject so changes per recipient wont affect
            //other recipients
            $message_template = clone $mt;
            $subject_template = clone $st;

            //try to put the emailrecipient fields in template
            $message_template->trySet('recipient_firstname', $r->get('firstname'));
            $message_template->trySet('recipient_lastname',  $r->get('lastname'));
            $message_template->trySet('recipient_email',     $r->get('email'));

            $subject_template->trySet('recipient_firstname', $r->get('firstname'));
            $subject_template->trySet('recipient_lastname',  $r->get('lastname'));
            $subject_template->trySet('recipient_email',     $r->get('email'));

            //add ability to further alter subject and message per Recipient
            if(is_callable($this->processSubjectPerRecipient)) {
                call_user_func($this->processSubjectPerRecipient, $r, $subject_template);
            }
            if(is_callable($this->processMessagePerRecipient)) {
                call_user_func($this->processMessagePerRecipient, $r, $message_template);
            }

            $this->phpMailer->Subject = $subject_template->render();
            $this->phpMailer->Body    = $this->header.$message_template->render().$this->footer;
            $this->phpMailer->AltBody = $this->phpMailer->html2text($this->phpMailer->Body);
            $this->phpMailer->addAddress($r->get('email'), $r->get('firstname').' '.$r->get('lastname'));

            //Send Email
            if(!$this->phpMailer->send()) {
                if($this->addUserMessageOnSend) {
                    $this->app->addUserMessage('Die Email '.$this->phpMailer->Subject.' konnte nicht an  '.$r->get('email').' gesendet werden.', 'error');
                }
            }
            else {
                $successful_send = true;
                if($this->addUserMessageOnSend) {
                    $this->app->addUserMessage('Die Email '.$this->phpMailer->Subject.' wurde erfolgreich an '.$r->get('email').' versendet.', 'success');
                }
                //add Email to IMAP Sent Folder
                $this->_addToIMAP($this->phpMailer->getSentMIMEMessage());
            }

            //clear recipient after each Email
            $this->phpMailer->clearAddresses();
        }

        if($successful_send && is_callable($this->onSuccess)) {
            call_user_func($this->onSuccess, $this->model);
        }

        //delete all recipients and then outbox email itself
        foreach($this->ref('EmailRecipient') as $r) {
            $r->delete();
        }
        $this->delete();

        return $successful_send;
    }


    /*
     * add Email to IMAP if set
     * TODO: Find some nice Lib for this
     * TODO: See if IMAP is configured, only then do so
     */
    protected function _addToIMAP(string $sent_message) {
        $imapStream = imap_open(
            $this->app->getSetting('IMAP_PATH_SENT_MAIL'),
            $this->app->getSetting('EMAIL_USERNAME'),
            $this->app->getSetting('EMAIL_PASSWORD'));
        $result = imap_append($imapStream, $this->app->getSetting('IMAP_PATH_SENT_MAIL'), $sent_message);
        imap_close($imapStream);
    }


    /*
     * used for email template editing. Returns an array of all fields available for the Model:
     * [
     *     'field_name' => 'field_caption'
     * ]
     */
    public function getModelVars(\atk4\data\Model $m, string $prefix = ''):array {
        $fields = [];
        if(method_exists($m, 'getFieldsForEmailTemplate')) {
            $field_names = $m->getFieldsForEmailTemplate();
            foreach($field_names as $field_name) {
                $fields[$prefix.$field_name] = $m->getField($field_name)->getCaption();
            }

            return $fields;
        }

        foreach($m->getFields() as $field_name => $field) {
            if(in_array($field->type, ['string', 'text', 'integer', 'float', 'date', 'time'])) {
                $fields[$prefix.$field_name] = $field->getCaption();
            }
        }

        return  $fields;
    }


    /*
     * Used by template editing modal
     */
    public function getTemplateEditVars():array {
        return [$this->model->getModelCaption() => $this->getModelVars($this->model, strtolower((new \ReflectionClass($this->model))->getShortName()).'_')];
    }
}
