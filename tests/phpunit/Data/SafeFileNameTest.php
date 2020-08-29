<?php

declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data;

use PMRAtk\Data\SafeFileName;
use PMRAtk\tests\phpunit\TestCase;


class SafeFileNameTest extends TestCase {

    public function testReplaceSpecialChars() {
        $res = SafeFileName::replaceSpecialChars('äöüÄÖÜß-:');
        self::assertSame(
            'aeoeueAeOeUess__',
            $res
        );
    }

    public function testRemoveDisallowedChars() {
        $res = SafeFileName::removeDisallowedChars(';,! alla.jpg,?=)(');
        self::assertSame(
            'alla.jpg',
            $res
        );
    }

    public function testcreateSafeFileName() {
        $res = SafeFileName::replaceSpecialChars('Änderung-02.jpg');
        self::assertSame(
            'Aenderung_02.jpg',
            $res
        );
    }
}
