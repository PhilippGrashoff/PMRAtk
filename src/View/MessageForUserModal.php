<?php

namespace PMRAtk\View;

use PMRAtk\Data\MessageForUser;

/**
 * This modal automatically opens itself if there are any unread messages for the currently logged in user
 */
class MessageForUserModal extends \atk4\ui\Modal
{

    public $messages = [];

    public $labelMessageRead = 'Nachricht gelesen';

    //can the modal only be closed by the "Read it" button?
    public $forceApproveRead;

    //if there is more than one message, show them in a "row"? Currently not implemented!
    public $showMultiple = false;


    /**
     *
     */
    public function renderView()
    {
        if (!$this->app->auth->user->loaded()) {
            throw new atk4\ui\Exception(__CLASS__ . ' can only be used with a logged in user');
        }
        //if messages were not set, load them here
        $this->_loadMessages();

        $i = 0;
        foreach($this->messages as $message) {
            $i++;
            if($i > 1 && !$this->showMultiple) {
                break;
            }
            $this->_addMessage($message);
        }

        parent::renderView();
    }


    /**
     *
     */
    protected function _addMessage(MessageForUser $message) {
        $this->title = $message->get('created_date') instanceof \DateTimeInterface ? $message->get('created_date')->format('d.m.Y').' ' : '';
        $this->title .= $message->get('title');
        $this->addScrolling();
        if($this->forceApproveRead
        || $message->get('needs_user_confirm')) {
            $this->notClosable();
        }
        $this->addClass('fullHeightModalWithButtons');
        if($message->get('is_html')) {
            $this->template->setHTML('Content', $message->get('text'));
        }
        else {
            $this->template->set('Content', $message->get('text'));
        }

        $this->_addMessageReadButton($message);
        $this->js(true, $this->show());

    }


    /**
     *
     */
    protected function _addMessageReadButton(MessageForUser $message) {
        $b = new \atk4\ui\Button();
        $b->set($this->labelMessageRead)->addClass('green ok');
        $b->setAttr('data-mfu_id', $message->get('id'));
        $this->addButtonAction($b);
        $b->on(
            'click',
            function ($e, $mfu_id) {
                $mfu = new MessageForUser($this->app->db);
                $mfu->load($mfu_id);
                $mfu->markAsRead();
                $_SESSION['MESSAGES_FOR_USER_DISPLAYED'] = 1;
                return $this->hide();
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
        if ($this->messages) {
            return;
        }

        $this->messages = (new MessageForUser($this->app->db))->getUnreadMessagesForLoggedInUser()->tryLoadAny();
    }
}