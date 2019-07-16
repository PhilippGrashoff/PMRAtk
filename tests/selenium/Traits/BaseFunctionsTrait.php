<?php
namespace PMRAtk\tests\selenium\Traits;

use RemoteWebDriver;
use WebDriverBy;
use WebDriverKeys;

/*
 * This traits contains test functions which are abstract
 * (usable in any atk selenium test),
 * not only for selenium tests for the current project.
 *
 * In this trait no functions which are project related should be stored.
 */
trait BaseFunctionsTrait {

    public static $windowWidth  = 1560;
    public static $windowHeight = 1080;

    public static $mobileWindowWidth  = 400;
    public static $mobileWindowHeight = 700;

    public $initialPage  = '';

    public $isMobileLayout = false;

    public $waitTimeOut = 15;

    public $waitInterval = 10;

    public static $webDriver;

    //used to store the initial newest audit element for later comparison
    public $auditCount;

    //used to store the initial DD option selected for later rollback in selectDropDown
    public $dropDownInitiallySelected;

    //used to store the current scroll offset to compare later in waitUntilScroll
    public $scrollOffset;


    /*
     *
     */
    public static function getWindowWidth() {
        if(isset(static::$isMobile)) {
            return self::$mobileWindowWidth;
        }
        else {
            return self::$windowWidth;
        }
    }


    /*
     *
     */
    public static function getWindowHeight() {
        if(isset(self::$isMobile)) {
            return self::$mobileWindowHeight;
        }
        else {
            return self::$windowHeight;
        }
    }


    /*
     * start webdriver session
     */
    public static function setUpBeforeClass():void {
        parent::setUpBeforeClass();
        $capabilities = array(\WebDriverCapabilityType::BROWSER_NAME => 'chrome', \WebDriverCapabilityType::BROWSER_NAME => 'chrome');
        self::$webDriver = \RemoteWebDriver::create('http://localhost:4444/wd/hub', ['browserName' => 'chrome', 'chromeOptions' => ['args' => ['--window-size='.self::getWindowWidth().','.self::getWindowHeight()]]]);
    }


    /**
     * Close webdriver session and close browser window.
     */
    public static function tearDownAfterClass():void {
        parent::tearDownAfterClass();
        self::$webDriver->quit();
    }


    /*
     * helper function which returns the value attribute of the
     * input specified by $css_selector
     */
    public function getInputValue(string $css_selector):string {
        $input = $this->findByCSS($css_selector);
        return $input->getAttribute('value');
    }


    /*
     * helper function which empties a text input specified by css_selector
     * @param string css_selector  The css selector for the input tag
     */
    public function emptyInput(string $css_selector, string $string_to_remove = '') {
        $input = $this->findByCSS($css_selector);

        //if no string to remove was specified, remove all content
        if(!empty($string_to_remove)) {
            $string_length = strlen($string_to_remove);
        }
        else {
            $string_length = strlen($input->getAttribute('value'));
        }

        //Go To end of input
        for($i = 0; $i < strlen($input->getAttribute('value')); $i++) {
            $input->sendKeys(WebDriverKeys::ARROW_RIGHT);
        }

        //send backspace key to input
        for($i = 0; $i < $string_length; $i++) {
            $input->sendKeys(chr(8));
        }

        return $input;
    }


    /*
     *
     */
    public function acceptAlert() {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            function() {
                try {
                    self::$webDriver->switchTo()->alert()->accept();
                    return true;
                }
                catch(\Exception $e) {}
            }
        );
    }


    /*
     *
     */
    public function closeToasts() {
        //wait until toast is visible
        $this->toastVisible();
        //count all toasts
        $amount = $this->countByCSS('.ui.toast');
        for($i = 0; $i < $amount; $i++) {
            $this->tryClick('.ui.toast');
        }
    }


    /*
     * sees if toast count matches given value
     */
    public function countToasts(int $amount, string $additional_class) {
        $this->waitUntil(
            function() use($amount, $additional_class) {
                if(empty($additional_class)) {
                    return $this->countByCSS('.ui.toast') === $amount;
                }
                else {
                    return $this->countByCSS('.ui.toast.'.$additional_class) === $amount;
                }
            }
        );
    }


    /*
     * sets the given inputs value to null, useful for date pickers
     */
    public function jsInputValueNull(string $css_selector) {
        //wait until input is visible
        $this->isVisible($css_selector);
        //try in case css selectors " or ' cause trouble
        $script = 'document.querySelector(\''.str_replace("'", '"', $css_selector).'\').value = null';
        try {
            self::$webDriver->executeScript($script);
        }
        catch(\Exception $e) {
            var_dump($script);
            throw $e;
        }
    }


    /*
     * checks if the element for the passed css_selector gets loading
     * class and then also loses it again
     *
     * @param string css_selector The selector for the HTML element
     */
    public function loadingIconVisibleAndGone($css_selector) {
        //short interval here is pretty important as loading icon sometimes
        //only appears for a very short time
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::visibilityOfElementLocated(\WebDriverBy::cssSelector($css_selector.'.loading'))
        );

        //after reload loading icon should be gone
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::invisibilityOfElementLocated(\WebDriverBy::cssSelector($css_selector.'.loading'))
        );
    }


    /*
     * tries to click element
     */
    public function tryClick(string $css_selector) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            function() use ($css_selector) {
                try {
                    $this->findByCSS($css_selector)->click();
                    return true;
                }
                catch(\Exception $e) {}
            }
        );
    }


    /*
     * tries to click a link
     */
    public function tryClickLink(string $link_text) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            function() use ($link_text) {
                try {
                    $e = self::$webDriver->findElement(WebDriverBy::linkText($link_text));
                    $e->click();
                    return true;
                }
                catch(\Exception $e) {}
            }
        );
    }


    /*
     * Selects a dropdown. If $select_option is defined, it will select the item where data-value matches,
     * if not, an option which is not the placeholder "..." and which was not selected before.
     *
     * @param string css_selector         The selector for the main <div>
     * @param string select_option        A specific option to select, can be left null
     * @paran bool   wait_menu_disappear  If true, function waits until Dropdown menu disappeared again
     */
    public function selectDropDown(string $css_selector, string $select_option = null, bool $wait_menu_disappear = false) {
        //find arrow and click
        $this->tryClick($css_selector.' i.dropdown.icon');
        //wait until menu appeared
        $this->isVisible($css_selector.' div.menu.visible');
        //store current value for later rollback
        $this->dropDownInitiallySelected = $this->findByCSS($css_selector.' div.menu div.selected')->getAttribute('data-value');

        //select a non-selected element
        $dropdown_options = self::$webDriver->findElements(\WebDriverBy::cssSelector($css_selector.' div.menu div'));
        $option_selected = false;

        //if no option to select was specified, select some which is not
        //empty and not selected yet
        if($select_option === null)  {
            foreach($dropdown_options as $option) {
                //do not select the show all option (....)
                if($option->getAttribute('data-value') == '') {
                    continue;
                }
                //do not select the option already active
                if(strpos($option->getAttribute('class'), 'selected') !== false) {
                    continue;
                }
                $option_selected = $option->getAttribute('data-value');
                $option->click();
                break;
            }
        }
        //select
        else {
            $option = self::$webDriver->findElement(\WebDriverBy::cssSelector($css_selector.' div.menu div[data-value="'.$select_option.'"]'));
            $option_selected = $option->getAttribute('data-value');
            $option->click();
        }

        //wait for dropdown menu to disappear
        if($wait_menu_disappear) {
            $this->isInvisible($css_selector.' div.menu.visible');
        }

        return $option_selected;
    }


    /*
     * takes date in format DDMMYYYY
     */
    public function fillDateInput(string $css_selector, string $date, bool $submit = false):object {
        //find input
        $input = $this->findByCSS($css_selector);
        $this->tryClick($css_selector);
        //move 2 times left, so youre sure to be on the day part of the input
        $input->sendKeys(WebDriverKeys::ARROW_LEFT);
        $input->sendKeys(WebDriverKeys::ARROW_LEFT);

        //hack for different date format inputs
        if(self::$app->getSetting('TEST_DATEFORMAT') == 'us') {
            $date = substr($date,2,2).substr($date,0,2).substr($date,4,4);
        }
        $input->sendKeys($date);

        //submit if wanted
        if($submit) {
            $input->sendKeys(PHP_EOL);
        }

        return $input;
    }


    /*
     *
     */
    public function fillTimeInput($css_selector, $time) {
        //find input
        $input = $this->findByCSS($css_selector);
        $this->tryClick($css_selector);
        //move left
        $input->sendKeys(WebDriverKeys::ARROW_LEFT);
        $input->sendKeys(WebDriverKeys::ARROW_LEFT);
        //hack for different date format inputs
        if(self::$app->getSetting('TEST_DATEFORMAT') == 'us') {
            $d = (new \Datetime())->createFromFormat('Hi', $time);
            $time = $d->format('hia');
        }
        $input->sendKeys($time);

        return $input;
    }


    /*
     *
     */
    public function fillTextInput(string $css_selector, string $text) {
        $input = $this->findByCSS($css_selector);
        $this->tryClick($css_selector);
        //Go To End of input
        for($i = 0; $i < strlen($input->getAttribute('value')); $i++) {
            $input->sendKeys(WebDriverKeys::ARROW_RIGHT);
        }

        $input->sendKeys($text);

        return $input;
    }


    /*
     *
     */
    public function inputHasValue(string $css_selector, string $value) {
        //this waits until input is there
        $input = $this->findByCSS($css_selector);
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            function() use($input, $value) {
                return $value ==  $input->getAttribute('value');
            }
        );
    }


    /*
     *
     */
    public function toastVisible(string $additional_class = '') {
        if(empty($additional_class)) {
            $this->isVisible('.ui.toast');
        }
        else {
            $this->isVisible('.ui.toast.'.$additional_class);
        }
    }


    /*
     *
     */
    public function containsText(string $css_selector, string $text) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            function() use($css_selector, $text) {
                try {
                    $elem = self::$webDriver->findElement(\WebDriverBy::cssSelector($css_selector));
                    return (strpos($elem->getText(), $text) !== false);
                }
                catch(\Exception $e) {}
            }
        );
    }


    /*
     * file upload field test. As selenium cant work with the Browser dialogue,
     * the hidden file input used by the file upload is filled with the
     * file path.
     *
     * @param string input_selector      The css selector for the whole input field
     * @param string file_list_selector  The css selector for the file list displayed
     */
    public function baseFileUpload($input_selector) {
        $path = self::$app->getSetting('FILE_BASE_PATH').'.circleci/demo-img.jpg';

        $input = $this->findByCSS($input_selector);
        $input->setFileDetector(new \LocalFileDetector());
        $input->sendKeys($path);
    }


    /*
     *
     */
    public function isVisible(string $css_selector) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::visibilityOfElementLocated(\WebDriverBy::cssSelector($css_selector))
        );
    }

    /*
     *
     */
    public function elementPresent(string $css_selector) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::presenceOfElementLocated(\WebDriverBy::cssSelector($css_selector))
        );
    }


    /*
     *
     */
    public function isInvisible(string $css_selector) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::invisibilityOfElementLocated(\WebDriverBy::cssSelector($css_selector))
        );
    }


    /*
     *
     */
    public function modalIsVisible() {
        return $this->isVisible('.atk-modal.visible');
    }


    /*
     *
     */
    public function closeModal() {
        $this->tryClick('.atk-modal.visible i.icon.close');
    }


    /*
     * checks if a passed webElement is a lookup or a normal dropdown
     */
    protected function _isLookup($element) {
        //at the moment, determining by class="noselection" in hidden input seems best option
        if(strpos($element->getAttribute('id'), '-ac') !== false) {
            return true;
        }

        return false;
    }


    /*
    /*
     * function that waits until  a yScroll happened
     * Logic: store current scroll position in $this->scrollOffset.
     * if this stored value is equal to the new scroll position, scrolling is
     * finished.
     * For any scroll to have happened at all, the new position must differ
     * from the old.
     *
     * @param int initial_position    The initial scroll position to compare to
     */
    public function waitForScroll(int $initial_offset) {
        self::$webDriver->wait($this->waitTimeOut, 300)->until(
            function() use ($initial_offset) {
                $old_offset = $this->scrollOffset;
                $this->scrollOffset = (self::$webDriver->executeScript("return window.pageYOffset;"));
                return ($old_offset == $this->scrollOffset && $this->scrollOffset != $initial_offset);
            }
        );
    }


    /*
     *
     */
    public function findByCSS(string $css_selector) {
        //wait until element is found
        //$this->isVisible($css_selector);
        $this->elementPresent($css_selector);
        return self::$webDriver->findElement(\WebDriverBy::cssSelector($css_selector));
    }


    /*
     *
     */
    public function findAllByCSS(string $css_selector, bool $wait_for_at_least_one = false):array {
        if ($wait_for_at_least_one) {
            $this->isVisible($css_selector);
        }
        return self::$webDriver->findElements(\WebDriverBy::cssSelector($css_selector));
    }


    /*
     *
     */
    public function waitForNotify() {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::visibilityOfElementLocated(\WebDriverBy::cssSelector('.atk-notify.visible'))
        );
    }


    /*
     *
     */
    public function waitForNotifyDisappear() {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            \WebDriverExpectedCondition::invisibilityOfElementLocated(\WebDriverBy::cssSelector('.atk-notify'))
        );
    }


    /*
     *
     */
    public function countByCSS($css_selector) {
        return count(self::$webDriver->findElements(\WebDriverBy::cssSelector($css_selector)));
    }


    /*
     *
     */
    public function waitUntil(callable $f) {
        self::$webDriver->wait($this->waitTimeOut, $this->waitInterval)->until(
            function() use ($f) {
                return call_user_func($f);
            }
        );
    }
}
