<?php

namespace PMRAtk\Data;

/**
 * This class represents a message for logged in users. The main concept is to display unread messages on login to
 * inform each individual user about updates, usually in a modal.
 */
class MessageForUser extends BaseModel {

    use \PMRAtk\Data\Traits\MToMTrait;

    public $table = 'message_for_user';

    //hack until all User models are sensibly rebased
    public $roleFieldName = 'role';

    /*
     *
     */
    public function init() {
        parent::init();
        $this->addFields([
            //Message title, e.g. "UI Update"
            ['title',               'type' => 'string',  'caption' => 'Titel'],
            //HTML Content of the message
            ['text',                'type' => 'Text',    'caption' => 'Nachricht'],
            //can be used by UI to force user to click "I have read it!" button instead of just closing the modal
            ['needs_user_confirm',  'type' => 'integer', 'caption' => ''],
            //array containing all user roles this message is meant for and the special value "ALL"
            ['for_user_roles',      'type' => 'array',   'caption' => 'Für Benutzerrollen', 'serialize' => 'json'],
        ]);

        $this->hasMany('MessageForUserToUser', MessageForUserToUser::class);
    }


    /*
     * Load all unread messages for the current logged in user
     */
    public function getUnreadMessagesForLoggedInUser():array {
        $return = [];
        if(!$this->app->auth->user->loaded()) {
            throw new \atk4\data\Exception('A user needs to be loaded in App for '.__FUNCTION__);
        }
        $messages = new self($this->persistence);
        //make sure there is no record for the current user with is set as read
        $messages->addCondition($messages->refLink('MessageForUserToUser')
                                        ->addCondition('user_id', $this->app->auth->user->get('id'))
                                        ->addCondition('is_read', '1')
                                        ->action('count'), '<', 1);
        foreach($messages as $message) {
            if(in_array('ALL', $message->get('for_user_roles'))
            || !$this->app->auth->user->hasField($this->roleFieldName)
            || in_array($this->app->auth->user->get($this->roleFieldName), $message->get('for_user_roles'))) {
                $return[] = clone $message;
            }
        }

        return $return;
    }


    /*
     * mark all unread messages as read. Either pass an array with objects/ids of Messages, or null to use
     * getUnreadMessagesForLoggedInUser
     */
    public function markMessagesAsRead(array $messages = null) {
        if($messages === null) {
            $messages = $this->getUnreadMessagesForLoggedInUser();
        }

        foreach($messages as $message) {
            if(!$message instanceof self) {
                $id = $message;
                $message = new self($this->persistence);
                $message->load($id);
            }


            $message->addUser($this->app->auth->user, ['is_read' => 1]);
        }
    }


    /*
     *
     */
    public function isReadByLoggedInUser():bool {
        $this->_exceptionIfThisNotLoaded();
        $mfutu = new MessageForUserToUser($this->persistence);
        $mfutu->addCondition('user_id', $this->app->auth->user->get('id'));
        $mfutu->addCondition('message_for_user_id', $this->get('id'));
        $mfutu->addCondition('is_read', '1');
        if(intval($mfutu->action('count')->getOne()) > 0) {
            return true;
        }

        return false;
    }


    /*
     *
     */
    public function addUser($user, $additional_fields = []) {
        return $this->_addMToMRelation($user, new MessageForUserToUser($this->persistence), '\PMRAtk\Data\User', 'message_for_user_id', 'user_id', $additional_fields);
    }


    /*
     *
     */
    public function removeUser($user) {
        return $this->_removeMToMRelation($user, new MessageForUserToUser($this->persistence), '\PMRAtk\Data\User', 'message_for_user_id', 'user_id');
    }


    /*
     *
     */
    public function hasUserRelation($user) {
        return $this->_hasMToMRelation($user, new MessageForUserToUser($this->persistence), '\PMRAtk\Data\User', 'message_for_user_id', 'user_id');
    }
}