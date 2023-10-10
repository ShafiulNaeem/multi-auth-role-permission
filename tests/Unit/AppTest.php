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
     * @dataProvider
     */
    public function dataProvider()
    {
        return [
            [false],
            [true]
        ];
    }
}