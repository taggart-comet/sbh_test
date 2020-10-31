<?php

namespace PhpMyOrm\test;

use PhpMyOrm\sql\DoesNotExist;
use PhpMyOrm\Model;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../models.php';

// to run all: cd test && ../modules/vendor/bin/phpunit --testsuite main

// testing with various possible filters on
class PhpMyOrmUnitTestWithFilters extends TestCase
{

    /** @var Model $testing_model */
    public string $testing_model = 'PhpMyOrm\test\TestModel';

    /** @test */
    // ../modules/vendor/bin/phpunit --filter getOne tests/PhpMyOrmUnitTestWithFilters
    public function getOne()
    {

        // lets first insert a specific row
        /** @var TestModel $model_obj */
        $model_obj = new $this->testing_model();

        $model_obj->date_added = time();
        $model_obj->content    = 'Test content at ' . date('h:i d M Y');

        $insert_id = $model_obj->save();
        self::assertIsNumeric($insert_id);

        // now checking that it can be retrieved using multiple filters
        try {
            $obj = $this->testing_model::objects()
                  ->filter_less('status', 1000)
                  ->filter_more('date_added', 0)
                  ->filter_in('id', [1, 2, 3, 4, 5, 6, 7, 8, 10, $insert_id])
                  ->get();
        } catch (DoesNotExist $e) {
            $this->addWarning("You should run `insertNew` first!");
        }
        self::assertInstanceOf($this->testing_model, $obj);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter getMany tests/PhpMyOrmUnitTestWithFilters
    public function getMany()
    {

        // may not work after very first runs, after 5 should start giving OK
        $limit  = mt_rand(2, 9);
        $offset = mt_rand(2, 5);

        $list = $this->testing_model::objects()
              ->filter_less('status', 1000)
              ->filter_between('date_added', 0, time())
              ->get($limit, $offset);

        $this->assertTrue(count($list) > 1);
    }

    // ../modules/vendor/bin/phpunit --filter updateMany tests/PhpMyOrmUnitTestWithFilters

    /** @test */
    public function updateMany()
    {

        $status = mt_rand(0, 9);

        // counting limit
        $count = $this->testing_model::objects()->filter_less('status', 10)->count();
        self::assertTrue(is_numeric($count));

        $this->testing_model::objects()->filter_less('status', 10)->updateMany([
              'status' => $status,
        ], $count);

        // counting what was updated
        $count_new = $this->testing_model::objects()->filter_equal('status', $status)->count();

        //
        self::assertEquals($count, $count_new);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter delete tests/PhpMyOrmUnitTestWithFilters
    public function delete()
    {

        // first let's insert some
        /** @var TestModel $model_obj */
        $model_obj = new $this->testing_model();

        $model_obj->date_added = time();
        $model_obj->content    = 'Test content at ' . date('h:i d M Y');

        $insert_id = $model_obj->save();

        self::assertIsNumeric($insert_id);

        // then let's count how many are there
        $count = $this->testing_model::objects()->filter_equal('status', 0)->count();

        self::assertIsNumeric($count);

        // now let's delete a couple
        $this->testing_model::objects()->filter_equal('status', 0)->delete(2);

        // should be deleted
        $new_count = $this->testing_model::objects()->filter_equal('status', 0)->count();

        self::assertIsNumeric($new_count);
        self::assertTrue($new_count < $count);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter orderBy tests/PhpMyOrmUnitTestWithFilters
    public function orderBy()
    {

        /** @var Model $model_instance */
        $model_instance = new $this->testing_model();
        $pk             = is_array($model_instance->pk()) ? 'comment_id' : 'id';

        // asc
        try {
            $asc_obj = $this->testing_model::objects()
                  ->filter_less('status', 100)
                  ->filter_more('date_added', 0)
                  ->order_asc($pk)
                  ->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
            $this->addWarning("You should run `insertNew` first!");
            return;
        }
        self::assertInstanceOf($this->testing_model, $asc_obj);

        // desc
        try {
            $desc_obj = $this->testing_model::objects()
                  ->filter_less('status', 100)
                  ->filter_more('date_added', 0)
                  ->order_desc($pk)
                  ->order_asc('date_added')
                  ->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
            $this->addWarning("You should run `insertNew` first!");
            return;
        }
        self::assertInstanceOf($this->testing_model, $desc_obj);

        // compare that desc is actually more than asc
        self::assertTrue($asc_obj->{$pk} < $desc_obj->{$pk});
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter incrementUpdate tests/PhpMyOrmUnitTestWithFilters
    public function incrementUpdate()
    {

        // getting random one
        /** @var TestModel $random_obj */
        $random_obj = $this->testing_model::objects()->get();
        self::assertInstanceOf($this->testing_model, $random_obj);

        $diff = mt_rand(1, 10000);

        // incrementing
        $this->testing_model::objects()
              ->filter_primary($random_obj->id)
              ->updateMany([
                    'date_added' => 'date_added + ' . $diff,
              ], 1);

        /** @var TestModel $inc_obj */
        $inc_obj = $this->testing_model::objects()->filter_primary($random_obj->id)->get();

        //
        self::assertEquals(($inc_obj->date_added - $random_obj->date_added), $diff);

        // decrementing
        $this->testing_model::objects()
              ->filter_primary($random_obj->id)
              ->updateMany([
                    'date_added' => 'date_added - ' . $diff,
              ], 1);

        /** @var TestModel $decr_obj */
        $decr_obj = $this->testing_model::objects()->filter_primary($random_obj->id)->get();

        //
        self::assertEquals($random_obj->date_added, $decr_obj->date_added);
    }
}
