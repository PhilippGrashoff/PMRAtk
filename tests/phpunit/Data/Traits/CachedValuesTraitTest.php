<?php

class CVTestApp extends \PMRAtk\View\App {
    use \PMRAtk\Data\Traits\CachedValuesTrait;
}


class CachedValuesTraitTest extends \PMRAtk\tests\phpunit\TestCase {

    /*
     *
     */
    public function testgetCachedValue() {
        $app = new CVTestApp(['nologin'], ['always_run' => false]);
        $app->setCachedValue('LALA', 'hamma');
        $this->assertEquals('hamma', $app->getCachedValue('LALA', function() {return 'Duggu';}));
    }

    /*
     *
     */
    public function testgetCachedValueWithTimeout() {
        $app = new CVTestApp(['nologin'], ['always_run' => false]);
        $app->setCachedValue('LALA', 'hamma');
        usleep(2000000);
        $this->assertEquals('Duggu', $app->getCachedValue('LALA', function() {return 'Duggu';}, 1));
    }
}