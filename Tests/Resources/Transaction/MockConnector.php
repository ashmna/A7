<?php


namespace A7\Tests\Resources\Transaction;


class MockConnector
{
    public static $isCalled = [];


    public static function clean()
    {
        self::$isCalled = [
            "init"     => 0,
            "begin"    => 0,
            "commit"   => 0,
            "rollback" => 0,
        ];
    }

    /**
     * @Init
     */
    private function init()
    {
        ++self::$isCalled["init"];
    }

    public function myBeginTransaction()
    {
        ++self::$isCalled["begin"];
    }

    public function myCommit()
    {
        ++self::$isCalled["commit"];
    }

    public function myRollback()
    {
        ++self::$isCalled["rollback"];
    }

}
