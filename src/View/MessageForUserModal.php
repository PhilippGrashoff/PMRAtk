<?php

namespace PMRAtk\View;

use PMRAtk\Data\MessageForUser;

/**
 * This modal automatically opens itself if there are any unread messages for the currently logged in user
 */
class MessageForUserModal extends \atk4\ui\View {

    public $messages = [];

    public $modal;

    public $labelMessageRead = 'Nachricht gelesen';

    //if there is more than one message, show them in a row? TODO
    //public $showMultiple = true;


    /**
     *
     */
    public function renderView() {
        if(!$this->app->auth->user->loaded()) {
            throw new atk4\ui\Exception(__CLASS__.' can only be used with a logged in user');
        }
        //if messages were not set, load them here
        $this->_loadMessages();

        //only do something if there are messages to display
        if($this->messages) {
            $this->modal = $this->add(new \atk4\ui\Modal());
            $this->modal->title = $this->messages[0]->get('title');
            $this->modal->addScrolling();
            $this->modal->template->setHTML('Content', $this->messages[0]->get('message'));
            $this->_markMessageAsRead($this->messages[0]);
            $this->modal->show();
        }
        parent::renderView();
    }


    /**
     *
     */
    protected function _markMessageAsRead(MessageForUser $message) {
        $b = new atk4\ui\Button();
        $b->set($this->labelMessageRead)->addClass('green ok');
        $b->setAttribute('data-mfu_id', $message->get('id'));
        $b->on(
            'click',
            function($e, $mfu_id) {
                $mfu = new MessageForUser($this->app->db);
                $mfu->load($mfu_id);
                $mfu->markMessagesAsRead([$mfu]);
                return $this->modal->hide();
            },
            [
                'args' => [
                    (new \atk4\ui\jQuery(new \atk4\ui\jsExpression('this')))->data('mfu_id'),
                ]
            ]
        );
    }


    /**
     *
     */
    protected function _loadMessages() {
        if($this->messages) {
            return;
        }

        $this->messages = (new MessageForUser($this->app->db))->getUnreadMessagesForLoggedInUser();
    }
}