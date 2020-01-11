<?php

namespace  PMRAtk\Data\Cron;

class CronManager extends \PMRAtk\Data\BaseModel {

    public $table = 'cron';

    public $intervalSettings = [
        'EVERY_MINUTE'           => 'Jede Minute',
        'EVERY_FIFTH_MINUTE'     => 'Alle 5 Minuten',
        'EVERY_FIFTEENTH_MINUTE' => 'Alle 15 Minuten'
    ];

    //the path to the folder where all Cronjob Files are located
    public $cronFilesPath = [
        'src/Data/Cron' => 'PMRAtk\\Data\\Cron',
    ];

    //files that should be ignored trying to load available Cronjobs
    public $ignoreClassNames = [
        __CLASS__
    ];

    //array in which info about all executed crons are stored
    public $executedCrons = [];


    /**
     *
     */
    public function init() {
        parent::init();
        $this->addFields([
            ['name',              'type' => 'string',    'caption' => 'Diesen Cronjob ausführen',                                                             'ui' => ['form' => ['DropDown', 'values' => $this->getAvailableCrons()]]],
            ['description',       'type' => 'text',      'caption' => 'Beschreibung'],
            ['defaults',          'type' => 'array',     'caption' => 'Zusätzliche Optionen für Cronjob',   'serialize' => 'json'],
            ['is_active',         'type' => 'integer',   'caption' => 'Aktiv',                              'values' => [0 => 'Nein', 1 => 'Ja'],             'ui' => ['form' => ['DropDown']]],
            ['execute_daily',     'type' => 'integer',   'caption' => 'Täglich ausführen',                  'values' => [0 => 'Nein', 1 => 'Ja'],             'ui' => ['form' => ['DropDown']]],
            ['execute_hourly',    'type' => 'integer',   'caption' => 'Stündlich ausführen',                'values' => [0 => 'Nein', 1 => 'Ja'],             'ui' => ['form' => ['DropDown']]],
            ['execute_minutely',  'type' => 'integer',   'caption' => 'Minütlich ausführen',                'values' => [0 => 'Nein', 1 => 'Ja'],             'ui' => ['form' => ['DropDown']]],
            ['time_daily',        'type' => 'time',      'caption' => 'Ausführen um',                                                                         'ui' => ['form' => ['\PMRAtk\View\FormField\Time']]],
            ['minute_hourly',     'type' => 'integer',   'caption' => 'Zu dieser Minute ausführen (0-59)',                                                    'ui' => ['form' => ['\PMRAtk\View\FormField\Integer']]],
            ['interval_minutely', 'type' => 'string',    'caption' => 'Intervall',                          'values' => $this->intervalSettings,              'ui' => ['form' => ['DropDown']]],
            ['offset_minutely',   'type' => 'integer',   'caption' => 'Verschiebung in Minuten (0-14)',     'default' => 0,                                   'ui' => ['form' => ['\PMRAtk\View\FormField\Integer']]],
            ['last_executed',     'type' => 'array',     'system' => true,                                  'serialize' => 'json'],
        ]);

        $this->addCalculatedField('schedule_info', [
            function($record) {
                if(!$record->get('is_active')) {
                    return '';
                }
                if($record->get('execute_daily')
                    && $record->get('time_daily')) {
                    return 'Täglich um ' . $record->get('time_daily')->format('H:i');
                }
                if($record->get('execute_hourly')
                    && $record->get('minute_hourly')) {
                    return 'Stündlich zur '.$record->get('minute_hourly').'. Minute';
                }
                if($record->get('execute_minutely')
                    && $record->get('interval_minutely')) {
                    if($record->get('interval_minutely') == 'EVERY_MINUTE') {
                        return 'Zu jeder Minute';
                    }
                    elseif($record->get('interval_minutely') == 'EVERY_FIFTH_MINUTE') {
                        return '5-Minütig um '.(0 + $record->get('offset_minutely')).', '.(5 + $record->get('offset_minutely')).', ...';
                    }
                    elseif($record->get('interval_minutely') == 'EVERY_FIFTEENTH_MINUTE') {
                        return 'Viertelstündlich um '.(0 + $record->get('offset_minutely')).', '.(15 + $record->get('offset_minutely')).', ...';
                    }
                }
            },
            'type' => 'string',
            'caption' => 'wird ausgeführt',
        ]);

        $this->addHook('beforeSave', function($m, $isUpdate) {
            if(!$m->isDirty('name')) {
                return;
            }
            $className = $m->get('name');
            if(!class_exists($className)) {
                return;
            }
            $cronClass = new $className($m->app, is_array($m->get('defaults')) ? $m->get('defaults') : []);
            $m->set('description', $cronClass->description);
        });
    }


    /**
     *
     */
    public function run(\DateTime $dateTime = null) {
        //for testing settings, a dateTime object can be provided. In Normal operation, do not pass anything to use
        //curret time
        if(!$dateTime) {
            $dateTime = new \DateTime();
        }
        foreach($this as $cron) {
            if(!$cron->get('is_active')) {
                continue;
            }
            $currentTime   = $dateTime->format('H:i');
            $currentMinute = $dateTime->format('i');
            //daily execution
            if($cron->get('execute_daily')) {
                if($currentTime !== $cron->get('time_daily')->format('H:i')) {
                    continue;
                }
                $cron->executeCron();
            }
            elseif($cron->get('execute_hourly')) {
                if(intval($currentMinute) !== $cron->get('minute_hourly')) {
                    continue;
                }
                $cron->executeCron();
            }
            elseif($cron->get('execute_minutely')) {
                if($this->get('offset_minutely') > 0) {
                    $currentMinute = (clone $dateTime)->modify('-'.$this->get('offset_minutely').' Minutes')->format('i');
                }
                if($cron->get('interval_minutely') == 'EVERY_MINUTE') {
                    $cron->executeCron();
                }
                elseif($cron->get('interval_minutely') == 'EVERY_FIFTH_MINUTE'
                    && ($currentMinute % 5) === 0) {
                    $cron->executeCron();
                }
                elseif($cron->get('interval_minutely') == 'EVERY_FIFTEENTH_MINUTE'
                    && ($currentMinute % 15) === 0) {
                    $cron->executeCron();
                }
            }
        }
    }


    /**
     *
     */
    public function executeCron():bool {
        $this->_exceptionIfThisNotLoaded();
        $className = $this->get('name');
        if(!class_exists($className)) {
            return false;
        }

        $cronClass = new $className($this->app, is_array($this->get('defaults')) ? $this->get('defaults') : []);
        $info = ['name' => $className];
        $time_start = microtime(true);
        ob_start();
        $cronClass->execute();
        $info['execution_time'] = microtime(true) - $time_start;
        $info['status']         = $cronClass->successful;
        $info['last_executed']  = new \DateTime();
        $info['ouput']          = ob_get_contents();
        ob_end_clean();

        if(!isset($this->executedCrons[$this->get('name')])) {
            $this->executedCrons[$this->get('name')] = [$info];
        }
        else {
            $this->executedCrons[$this->get('name')][] = $info;
        }

        $this->set('last_executed', $info);
        $this->save();

        return $cronClass->successful;
    }


    /**
     * Loads all Cronjob Files and returns them as array:
     * Fully\Qualifiee\ClassName => Name property
     */
    public function getAvailableCrons():array {
        $res = [];
        foreach($this->cronFilesPath as $path => $namespace) {
            $dirName = $this->app->getSetting('FILE_BASE_PATH').$path;
            if(!file_exists($dirName)) {
                continue;
            }

            foreach(new \DirectoryIterator($dirName) as $file) {
                if($file->getExtension() !== 'php') {
                    continue;
                }

                $className = $namespace.'\\'.$file->getBasename('.php');
                if(!class_exists($className)
                    || (new \ReflectionClass($className))->isAbstract()) {
                    continue;
                }

                foreach($this->ignoreClassNames as $name) {
                    if(strpos($className, $name) !== false) {
                        continue 2;
                    }
                }

                $class = new $className($this->app);
                if(!$class instanceof \PMRAtk\Data\Cron\BaseCronJob) {
                    continue;
                }
                $res[get_class($class)] = $class->getName();
            }
        }
        return $res;
    }
}