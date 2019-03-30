<?php

namespace PMRAtk\Data;

class File extends SecondaryBaseModel {

    public $table = 'file';

    function init() {
        parent::init();
        $this->addFields([
            //filename is stored in value

            //the relative path to the file project main dir, e.g. output/images/
            ['path',            'type' => 'string'],
            //currently BC, INVOICE or TOURLIST for auto-generated pdfs, MAP for meeting point maps, TEMPORARY for files which get deleted by cronjob later
            ['type',            'type' => 'string'],
            //pdf, jpg etc
            ['filetype',        'type' => 'string'],
            //indicates if the file was generated by App (1) or uploaded by user (0)
            ['auto_generated',  'type' => 'boolean', 'default' => false],
        ]);

        //before save, check if file exists
        $this->addHook('beforeSave', function($m) {
            if(!$m->checkFileExists()) {
                throw new \atk4\data\Exception('The file to be saved does not exist: '.$this->getFullFilePath());
            }
        });

        //after successful delete, delete file as well
        $this->addHook('afterDelete', function($m) {
            $m->deleteFile();
        });
    }


    /*
     * tries to delete the file set in path
     * returns bool
     */
    public function deleteFile() {
        if(file_exists(FILE_BASE_PATH.$this->get('path').$this->get('value'))) {
            return unlink(FILE_BASE_PATH.$this->get('path').$this->get('value'));
        }
        return false;
    }


    /**
     * Converts to ASCII.
     * @param  string  UTF-8 encoding
     * @return string  ASCII
     */
    protected function _toAscii(string $s):string {
        $transliterator = NULL;
        if ($transliterator === NULL && class_exists('Transliterator', FALSE)) {
            $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII');
        }

        $s = preg_replace('#[^\x09\x0A\x0D\x20-\x7E\xA0-\x{2FF}\x{370}-\x{10FFFF}]#u', '', $s);
        $s = strtr($s, '`\'"^~?', "\x01\x02\x03\x04\x05\x06");
        $s = str_replace(
            array("\xE2\x80\x9E", "\xE2\x80\x9C", "\xE2\x80\x9D", "\xE2\x80\x9A", "\xE2\x80\x98", "\xE2\x80\x99", "\xC2\xB0"),
            array("\x03", "\x03", "\x03", "\x02", "\x02", "\x02", "\x04"), $s
        );
        if ($transliterator !== NULL) {
            $s = $transliterator->transliterate($s);
        }
        if (ICONV_IMPL === 'glibc') {
            $s = str_replace(
                array("\xC2\xBB", "\xC2\xAB", "\xE2\x80\xA6", "\xE2\x84\xA2", "\xC2\xA9", "\xC2\xAE"),
                array('>>', '<<', '...', 'TM', '(c)', '(R)'), $s
            );
            $s = @iconv('UTF-8', 'WINDOWS-1250//TRANSLIT//IGNORE', $s); // intentionally @
            $s = strtr($s, "\xa5\xa3\xbc\x8c\xa7\x8a\xaa\x8d\x8f\x8e\xaf\xb9\xb3\xbe\x9c\x9a\xba\x9d\x9f\x9e"
                . "\xbf\xc0\xc1\xc2\xc3\xc4\xc5\xc6\xc7\xc8\xc9\xca\xcb\xcc\xcd\xce\xcf\xd0\xd1\xd2\xd3"
                . "\xd4\xd5\xd6\xd7\xd8\xd9\xda\xdb\xdc\xdd\xde\xdf\xe0\xe1\xe2\xe3\xe4\xe5\xe6\xe7\xe8"
                . "\xe9\xea\xeb\xec\xed\xee\xef\xf0\xf1\xf2\xf3\xf4\xf5\xf6\xf8\xf9\xfa\xfb\xfc\xfd\xfe"
                . "\x96\xa0\x8b\x97\x9b\xa6\xad\xb7",
                'ALLSSSSTZZZallssstzzzRAAAALCCCEEEEIIDDNNOOOOxRUUUUYTsraaaalccceeeeiiddnnooooruuuuyt- <->|-.');
            $s = preg_replace('#[^\x00-\x7F]++#', '', $s);
        } else {
            $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s); // intentionally @
        }
        $s = str_replace(array('`', "'", '"', '^', '~', '?'), '', $s);
        return strtr($s, "\x01\x02\x03\x04\x05\x06", '`\'"^~?');
    }


    /**
     * Converts to web safe characters [a-z0-9-] text.
     * @param  string  UTF-8 encoding
     * @param  string  allowed characters
     * @return string
     */
    public function webalize(string $s, string $charlist = '.'):string {
        //replace common german Umlauts
        $search = array("ä", "ö", "ü", "ß", "Ä", "Ö", "Ü");
        $replace = array("ae", "oe", "ue", "ss", "Ae", "Oe", "Ue",);
        $s = str_replace($search, $replace, $s);

        $s = $this->_toAscii($s);
        $s = preg_replace('#[^a-z0-9' . preg_quote($charlist, '#') . ']+#i', '-', $s);
        $s = trim($s, '-');
        return $s;
    }


    /*
     * sets properties name, filename and filetype.
     * if $unique_name = true, it creates a filename that does not exist yet.
     *
     * @param string
     * @param bool
     *
     * @return void
     */
    public function createFileName(string $name, bool $unique_name = true) {
        $this->set('value', $this->webalize($name));
        $this->set('path', $this->app->getSetting('FILE_PATH'));

        //can only check for existing file if path is set
        if($unique_name) {
            $old_name = $this->get('value');
            $i = 1;
            while(file_exists(FILE_BASE_PATH.$this->get('path').$this->get('value'))) {
                $this->set('value', pathinfo($old_name, PATHINFO_FILENAME).'_'.$i.($this->get('filetype') ? '.'.$this->get('filetype') : ''));
                $i++;
            }
        }
    }

    /*
     * This function uses the standard $_FILES['userfile'] array to set
     * properties and tries to move the file to a proper dir.
     *
     * @param array
     *
     * @return bool
     */
    public function uploadFile($f) {
        $this->createFileName($f['name']);

        //try move the uploaded file, quit on error
        return move_uploaded_file($f['tmp_name'], $this->getFullFilePath());
    }

    /*
     * Returns the full path to the file from the file system base dir
     *
     * @return string
     */
    public function getFullFilePath():string {
        return FILE_BASE_PATH.$this->get('path').$this->get('value');
    }

    /*
     * checks if the file really exists
     *
     * @return bool
     */
    public function checkFileExists():bool {
        if(file_exists($this->getFullFilePath()) && is_file($this->getFullFilePath())) {
            return true;
        }
        return false;
    }


    /*
     * saves the content passed as string to filename
     *
     * @param string
     *
     * @return bool
     */
    public function saveStringToFile(string $string):bool {
        $res = null;
        if(!$this->get('filename')) {
            $this->createFileName('UnnamedFile');
        }
        if($f = fopen($this->getFullFilePath(), 'w')) {
            $res = fwrite($f, $string);
            fclose($f);
        }
        if($res) {
            return true;
        }
        return false;
    }


    /*
     * returns an URL under which the file can be downloaded
     *
     * @return string
     */
    public function getLink() {
        return $this->app->getSetting('URL_BASE_PATH').$this->get('path').$this->get('filename');
    }
}
