<?php declare(strict_types=1);

namespace PMRAtk\View;

use atk4\ui\Button;
use atk4\ui\jQuery;
use atk4\ui\jsExpression;
use atk4\ui\Modal;
use atk4\ui\Exception;
use DateTimeInterface;
use PMRAtk\Data\MessageForUser;

/**
 * This modal automatically opens itself if there are any unread messages for the currently logged in user
 */
class MessageForUserModal extends Modal
{
    public $labelMessageRead = 'Benachrichtigung gelesen';

    //can the modal only be closed by the "Read it" button?
    public $forceApproveRead;

    //if there is more than one message, show them in a "row"? Currently not implemented!
    public $showMultiple = false;

    public function renderView(): void
    {
        if (!$this->app->auth->user->loaded()) {
            throw new Exception(__CLASS__ . ' can only be used with a logged in user');
        }

        $i = 0;
        foreach((new MessageForUser($this->app->db))->getUnreadMessagesForLoggedInUser() as $message) {
            $i++;
            if($i > 1 && !$this->showMultiple) {
                break;
            }
            $this->_addMessage($message);
        }

        parent::renderView();
    }

    protected function _addMessage(MessageForUser $message) {
        $this->title = $message->get('created_date') instanceof DateTimeInterface ? $message->get('created_date')->format('d.m.Y').' ' : '';
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

    protected function _addMessageReadButton(MessageForUser $message) {
        $b = new Button();
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
                    (new jQuery(new jsExpression('this')))->data('mfu_id'),
                ]
            ]
        );
    }
}