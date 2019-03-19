<?php

include('config.php');

        $app = new \PMRAtk\View\App(['nologin']);

        $a = new \PMRAtk\tests\phpunit\Data\BaseModelA($app->db);
        $b = new \PMRAtk\tests\phpunit\Data\BaseModelB($app->db);
        $a->save();
        $b->save();

        $mtom_count = (new \PMRAtk\tests\phpunit\Data\MToMModel($app->db))->action('count')->getOne();
        $tc = new \PMRAtk\tests\phpunit\Data\BaseModelTest();
        $tc->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel($app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);

        //adding again shouldnt create a new record
        $tc->callProtected($a, '_addMToMRelation', [$b, new \PMRAtk\tests\phpunit\Data\MToMModel($app->db), '\PMRAtk\tests\phpunit\Data\BaseModelB', 'BaseModelA_id', 'BaseModelB_id']);

