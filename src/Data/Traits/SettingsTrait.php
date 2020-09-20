<?php

declare(strict_types=1);

namespace PMRAtk\Data\Traits;

use PMRAtk\Data\Setting;


/**
 * Use this trait to load all Settings records into an array. You can define how long a setting is valid before
 * it is recalculated. Typically this trait is added to App.
 */
trait SettingsTrait {

    protected $_settings = [];
    protected $_settingsLoaded = false;

    public function getSetting(string $ident): ?Setting
    {
        //load settings once
        $this->_loadSettings();

        if (isset($this->_settings[$ident])) {
            return $this->_settings[$ident];
        }

        return null;
    }

    protected function _loadSettings()
    {
        if ($this->_settingsLoaded) {
            return;
        }
        foreach (new Setting($this->db) as $m) {
            $this->_settings[$m->get('ident')] = $m->get('value');
        }
        $this->_settingsLoaded = true;
    }

    public function unloadSettings()
    {
        $this->_settings = [];
        $this->_settingsLoaded = false;
    }

    /**
     * returns all STD_ settings, which are typically used in templates
     */
    public function getAllSTDSettings(): array
    {
        $return = [];
        $this->_loadSettings();

        foreach ($this->_settings as $key => $value) {
            if (substr($key, 0, 4) == 'STD_') {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    public function settingExists(string $ident): bool
    {
        $this->_loadSettings();
        return array_key_exists($ident, $this->_settings);
    }

    /**
     * For "installers": Add a setting if it does not exist yet
     */
    public function addSetting(Setting $s)
    {
        $this->_loadSettings();
        if (!array_key_exists($s->get('ident'), $this->_settings)) {
            $s->save();
            $this->_settingsLoaded = false;
        }
    }

    /**
     * Can be used to overwrite a setting, mostly for tests
     */
    public function setSetting(Setting $s)
    {
        $s->save();
        $this->_settingsLoaded = false;
        $this->_loadSettings();
    }
}