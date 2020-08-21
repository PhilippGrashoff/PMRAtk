<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Traits;


use PMRAtk\tests\TestClasses\CachedValuesApp;
use PMRAtk\tests\phpunit\TestCase;


/**
 *
 */
class CachedValuesTraitTest extends TestCase {

    /*
     *
     */
    public function testgetCachedValue() {
        $app = new CachedValuesApp(['nologin'], ['always_run' => false]);
        $app->setCachedValue('LALA', 'hamma');
        $this->assertEquals(
            'hamma',
            $app->getCachedValue('LALA', function() {return 'Duggu';})
        );
    }


    /*
     *
     */
    public function testgetCachedValueWithTimeout() {
        $app = new CachedValuesApp(['nologin'], ['always_run' => false]);
        $app->setCachedValue('DADA', 'hamma');
        $this->assertEquals(
            'hamma',
            $app->getCachedValue('DADA', function() {return 'Duggu';}, 1)
        );
        usleep(1500000);
        $this->assertEquals(
            'Duggu',
            $app->getCachedValue('DADA', function() {return 'Duggu';}, 1)
        );
    }


    /*
     *
     */
    public function testgetNonExistantCachedValue() {
        $app = new CachedValuesApp(['nologin'], ['always_run' => false]);
        $this->assertEquals(
            'hamma',
            $app->getCachedValue('HAKIRILI', function() {return 'hamma';})
        );
    }


    /*
     *
     */
    public function testSetCachedValueTwiceDoesNotCauseException() {
        $app = new CachedValuesApp(['nologin'], ['always_run' => false]);
        $this->assertEquals(
            'hamma',
            $app->getCachedValue('HAKIRILI', function() {return 'hamma';})
        );
        $app->setCachedValue('HAKIRILI', 'Mausi');
        self::assertEquals(
            'Mausi',
            $app->getCachedValue('HAKIRILI', function() {return 'hamma';})
        );
    }
    /**/
}