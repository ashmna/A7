<?php
namespace A7\Commands;

require_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";

use A7\Utils\AutoTestGen;

class AutoTest
{

    public static function genUnit()
    {
        AutoTestGen::generate(
            '/Users/amnatsakanyan/github/A7/Tests/Auto/a7-test-record.php',
            '/Users/amnatsakanyan/github/A7/Tests/Auto/Unit/',
            'A7\Test\Auto\Unit',
            'Unit'
        );
    }

    public static function genIntegration()
    {
        AutoTestGen::generate(
            '/Users/amnatsakanyan/github/A7/Tests/Auto/a7-test-record.php',
            '/Users/amnatsakanyan/github/A7/Tests/Auto/Integration/',
            'A7\Test\Auto\Integration',
            'Integration'
        );
    }

}

AutoTest::genUnit();
AutoTest::genIntegration();
