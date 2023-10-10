<?php

namespace Shafiulnaeem\MultiAuthRolePermission\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Shafiulnaeem\MultiAuthRolePermission\Tests\TestCase;

class AppTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     * @param $data
     * @return void
     */
    public function test_app( $data)
    {
        $this->assertSame(true, $data);
    }


    /**
     * @dataProvider sumData
     */
    public function test_sum($a, $b, $expected)
    {
        $this->assertSame($expected, $a + $b);
    }

    /**
     * @dataProvider
     */
    public static function dataProvider()
    {
        return [
            [false],
            [true]
        ];
    }


    public static function sumData()
    {
        return [
            [2, 3, 5],
            [2, 5, 7],
            [2, 4, 5],
        ];
    }
}