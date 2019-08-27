<?php

namespace PMRAtk\View\Traits;

/*
 * usually added to app. Data layer can add messages to app which ui or other
 * can pick up and display
 */

trait UserMessageTrait {

    public $userMessages = [];


    /*
     * This works as message storage. Data level can add messages here as well as
     * Ui. Ui can pick these messages and display to user
     */
    public function addUserMessage(string $message, string $class = '') {
        $this->userMessages[] = ['message' => $message, 'class' => $class];
    }


    /*
     * renders messages as HTML.
     * Default is that FUI's .ui.message is used. If $inline is set to true,
     * it will use inline styling, e.g. for an Email where FUI CSS is not
     * available.
     */
    public function getUserMessagesAsHTML(bool $inline = false):string {
        $return = '';
        foreach($this->userMessages as $message) {
            if($inline) {
                $return .= '<div style="color:#'.$this->_getColorForUserMessageClass($message['class']).'">'.$message['message'].'</div>';
            }
            else {
                $return .= '<div class="ui message '.$message['class'].'">'.$message['message'].'</div>';
            }
        }

        return $return;
    }


    /*
     * returns the messages as an array of jsExpressions opening a toast for
     * each message.
     * Usable e.g. in Form onSubmit returns
     */
    public function getUserMessagesAsJsToast():array {
        $return = [];
        foreach($this->userMessages as $message) {
            $return[] = new \atk4\ui\jsToast([
                'message' => $message['message'],
                'position' => 'bottom right',
                'class' => $message['class'],
                'showProgress' => 'bottom',
                'displayTime' => ($message['class'] == 'success' ? 3000 : 8000)]);
        }

        return $return;
    }


    /*
     * returns html color codes for different message classes for inline styling
     */
    protected function _getColorForUserMessageClass(string $class) {
        if($class == 'success') {
            return '005723';
        }
        elseif($class == 'warning') {
            return 'ff9900';
        }
        elseif($class == 'error') {
            return 'dd0000';
        }

        //default black
        return '000000';
    }
}