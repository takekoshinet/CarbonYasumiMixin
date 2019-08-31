<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use CarbonYasumi\CarbonYasumi;

class CarbonYasumiTest extends TestCase
{
    public function test_isBusinessday()
    {
        $this->assertFalse(CarbonYasumi::parse('2019-08-10')->isBusinessday());
        $this->assertFalse(CarbonYasumi::parse('2019-08-11')->isBusinessday());
        $this->assertFalse(CarbonYasumi::parse('2019-08-12')->isBusinessday());
        $this->assertTrue(CarbonYasumi::parse('2019-08-13')->isBusinessday());
    }
}
