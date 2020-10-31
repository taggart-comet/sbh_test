<?php

namespace PhpMyOrm\test;

use PhpMyOrm\Model;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../models.php';

// to run all: cd test && ../modules/vendor/bin/phpunit --testsuite main

// this test-class for checking core functionality by primary keys,
// for testing filters there will be a different class
class PhpMyOrmUnitTestPrimary extends TestCase
{

    /** @var TestModel $testing_model */
    public string $testing_model = 'PhpMyOrm\test\TestModel';
    //	public $testing_model = 'PhpMyOrm\test\TestModelAuto';
    //		public $testing_model = 'PhpMyOrm\test\TestModelMultiPrimary';

    /** @test */
    // ../modules/vendor/bin/phpunit --filter insertNew tests/PhpMyOrmUnitTestPrimary
    public function insertNew()
    {

        // -------------------------------------------------------
        // ONE
        // -------------------------------------------------------

        /** @var TestModel $model_obj */
        $model_obj = new $this->testing_model();

        // for multi key model
        if (is_array($model_obj->pk())) {
            $model_obj->comment_id = mt_rand(1, time());
            $model_obj->article_id = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
        }

        $model_obj->date_added = time();
        $model_obj->content    = 'Test content at ' . date('h:i d M Y');

        $insert_id = $model_obj->save();

        self::assertIsNumeric($insert_id);

        // -------------------------------------------------------
        // MANY
        // -------------------------------------------------------

        $count_before = $this->testing_model::objects()->count();
        $diff         = mt_rand(5, 10);

        // making a few more insert for other methods to work
        $insert_ar = [];
        for ($i = 0; $i < $diff; $i++) {

            // for multi key model
            if (is_array($model_obj->pk())) {
                $insert_ar[] = [
                      'comment_id' => mt_rand(1, time()),
                      'article_id' => str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'),
                      'date_added' => time(),
                      'content'    => 'Test content at ' . date('h:i d M Y'),
                ];
                continue;
            }
            $insert_ar[] = [
                  'date_added' => time(),
                  'content'    => 'Test content at ' . date('h:i d M Y'),
            ];
        }

        $this->testing_model::insertMany($insert_ar);

        $count_after = $this->testing_model::objects()->count();

        self::assertEquals($diff, $count_after - $count_before);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter getOne tests/PhpMyOrmUnitTestPrimary
    public function getOne()
    {

        try {
            $obj = $this->testing_model::objects()->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
            $this->addWarning("You should run `insertNew` first!");
        }
        self::assertInstanceOf($this->testing_model, $obj);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter getPrimary tests/PhpMyOrmUnitTestPrimary
    public function getPrimary()
    {

        // getting random one
        $random_obj = $this->testing_model::objects()->get();
        self::assertInstanceOf($this->testing_model, $random_obj);

        $fetch = $this->testing_model::objects();

        // getting it by primary key
        if (count($random_obj->primary_keys) > 1) {

            $fetch->filter_primary($random_obj->comment_id, $random_obj->article_id);
        } else {
            $fetch->filter_primary($random_obj->pk());
        }

        $primary_obj = $fetch->get();

        //
        self::assertEquals($random_obj, $primary_obj);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter getMany tests/PhpMyOrmUnitTestPrimary
    public function getMany()
    {

        $limit  = mt_rand(2, 9);
        $offset = mt_rand(2, 5);

        $list = $this->testing_model::objects()->get($limit, $offset);

        self::assertTrue(count($list) > 1);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter queryCount tests/PhpMyOrmUnitTestPrimary
    public function queryCount()
    {

        $count = $this->testing_model::objects()->count();

        self::assertIsNumeric($count);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter update tests/PhpMyOrmUnitTestPrimary
    public function update()
    {

        // getting random one
        /** @var TestModel $random_obj */
        $random_obj = $this->testing_model::objects()->get();
        self::assertInstanceOf($this->testing_model, $random_obj);

        // incrementing date_added
        $random_obj->date_added = $random_obj->date_added + 1;

        // updating
        $random_obj->save();

        // checking if date_added was changed
        $fetch = $this->testing_model::objects();

        // getting it by primary key
        if (count($random_obj->primary_keys) > 1) {

            $fetch->filter_primary($random_obj->comment_id, $random_obj->article_id);
        } else {
            $fetch->filter_primary($random_obj->pk());
        }

        /** @var TestModel $check_obj */
        $check_obj = $fetch->get();

        self::assertEquals($random_obj->date_added, $check_obj->date_added);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter delete tests/PhpMyOrmUnitTestPrimary
    public function delete()
    {

        // getting any first row
        /** @var Model $obj */
        try {
            $obj = $this->testing_model::objects()->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
            $this->addWarning("Test cannot proceed without a row in the table");
            return;
        }

        // deleting the object
        $this->testing_model::objects()->filter_primary($obj->pk())->delete();

        // should be deleted
        $this->expectException(\PhpMyOrm\sql\DoesNotExist::class);
        $this->testing_model::objects()->filter_primary($obj->pk())->get();
    }

    // on duplicated key update
    // ../modules/vendor/bin/phpunit --filter insertOrUpdate tests/PhpMyOrmUnitTestPrimary
    /** @test */
    public function insertOrUpdate()
    {

        $id     = mt_rand(1, 10000000);
        $status = mt_rand(0, 9);

        /** @var Model $model_instance */
        $model_instance = new $this->testing_model();

        $this->expectException(\PhpMyOrm\sql\DoesNotExist::class);
        if (is_array($model_instance->pk())) {
            $id2 = mt_rand(1, 10000000);
            $this->testing_model::objects()->filter_primary($id, $id2)->get();
        } else {
            $this->testing_model::objects()->filter_primary($id)->get();
        }

        // -------------------------------------------------------
        // first inserting the row
        // -------------------------------------------------------
        if (isset($id2)) {
            $insert_set = [
                  'comment_id' => $id,
                  'article_id' => $id2,
                  'status'     => $status,
            ];
        } else {
            $insert_set = [
                  'id'     => $id,
                  'status' => $status,
            ];
        }

        /** @var TestModel $new_obj */
        $new_obj = new $this->testing_model($insert_set);
        $new_obj->saveOrUpdate([
              'status' => $status,
        ]);

        try {
            if (isset($id2)) {
                $model_obj = $this->testing_model::objects()->filter_primary($id, $id2)->get();
            } else {
                $model_obj = $this->testing_model::objects()->filter_primary($id)->get();
            }
        } catch (\PhpMyOrm\sql\DoesNotExist | \PhpMyOrm\sql\IncorrectFilterParams $e) {
            $this->addWarning("insertOrUpdate did not make the insertion..");
            return;
        }

        self::assertEquals($model_obj->status, $status);

        $new_status = mt_rand(0, 9);

        // -------------------------------------------------------
        // then updating it with the same request
        // -------------------------------------------------------
        $new_obj->saveOrUpdate($insert_set, [
              'status' => $new_status,
        ]);

        try {
            if (isset($id2)) {
                $model_obj = $this->testing_model::objects()->filter_primary($id, $id2)->get();
            } else {
                $model_obj = $this->testing_model::objects()->filter_primary($id)->get();
            }
        } catch (\PhpMyOrm\sql\DoesNotExist | \PhpMyOrm\sql\IncorrectFilterParams $e) {
            $this->addWarning("insertOrUpdate did not update..");
            return;
        }

        self::assertEquals($model_obj->status, $new_status);
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter transaction tests/PhpMyOrmUnitTestPrimary
    public function transaction()
    {

        // testing it simply, just that PDO does not fire any errors
        $this->testing_model::startTransaction();

        // lets do get and commit
        try {
            $this->testing_model::objects()->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
        }

        $this->testing_model::commitTransaction();

        // let's roll back another one testing that an insert would not happen
        $this->testing_model::startTransaction();

        $model_obj          = new $this->testing_model();
        $model_obj->content = 'Test content at ' . date('h:i d M Y');
        $insert_id          = $model_obj->save();
        self::assertIsNumeric($insert_id);

        //
        $this->testing_model::rollBackTransaction();

        // not testing further if it's multi primary
        if (is_array($model_obj->pk())) {
            return;
        }

        // now let's check that $insert_id is not in the table
        $this->expectException(\PhpMyOrm\sql\DoesNotExist::class);
        $this->testing_model::objects()->filter_primary($model_obj->pk())->get();
    }

    /** @test */
    // ../modules/vendor/bin/phpunit --filter orderBy tests/PhpMyOrmUnitTestPrimary
    public function orderBy()
    {

        /** @var Model $model_instance */
        $model_instance = new $this->testing_model();
        $pk             = is_array($model_instance->pk()) ? 'comment_id' : 'id';

        // asc
        try {
            $asc_obj = $this->testing_model::objects()->order_asc($pk)->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
            $this->addWarning("You should run `insertNew` first!");
        }
        self::assertInstanceOf($this->testing_model, $asc_obj);

        // desc
        try {
            $desc_obj = $this->testing_model::objects()->order_desc($pk)->get();
        } catch (\PhpMyOrm\sql\DoesNotExist $e) {
            $this->addWarning("You should run `insertNew` first!");
        }
        self::assertInstanceOf($this->testing_model, $desc_obj);

        // compare that desc is actually more than asc
        self::assertTrue($asc_obj->{$pk} < $desc_obj->{$pk});
    }

}
