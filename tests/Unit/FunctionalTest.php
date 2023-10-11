<?php


use PHPUnit\Framework\Attributes\DataProvider;

class FunctionalTest extends \Shafiulnaeem\MultiAuthRolePermission\Tests\TestCase
{
    /**
     * @dataProvider stringMatchDataProvider
     * @param $stringOne
     * @param $stringTwo
     * @return void
     */
    public function test_match_two_string_pattern($stringOne, $stringTwo)
    {
       $match = matchTwoStringPattern($stringOne, $stringTwo);

       $this->assertTrue($match);
    }


    public static function stringMatchDataProvider()
    {
        return [
            ['gg?idd={slug}&id={id}', 'gg?idd=in&id=125'],
            ['http://localhost:8000/blog/index?id=123', 'http://localhost:8000/blog/index'],
            ['http://localhost:8000/blog/show/{id}', 'http://localhost:8000/blog/show/23'],
            ['http://localhost:8000/blog/update/{id}', 'http://localhost:8000/blog/update/23'],
            ['http://localhost:8000/blog/put/{blog}/category/{slug}/{id}', 'http://localhost:8000/blog/put/1/category/ola-la-234-lolita/23']
        ];
    }
}