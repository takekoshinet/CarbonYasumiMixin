<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use CarbonYasumiMixin\CarbonYasumiMixin;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class CarbonYasumiMixinTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        Carbon::mixin(new CarbonYasumiMixin);
        CarbonImmutable::mixin(new CarbonYasumiMixin);
    }

    public function test_isBusinessday()
    {
        $this->assertFalse(Carbon::parse('2019-08-10')->isBusinessday());
        $this->assertFalse(Carbon::parse('2019-08-11')->isBusinessday());
        $this->assertFalse(Carbon::parse('2019-08-12')->isBusinessday());
        $this->assertTrue(Carbon::parse('2019-08-13')->isBusinessday());

        $this->assertFalse(CarbonImmutable::parse('2019-08-10')->isBusinessday());
        $this->assertFalse(CarbonImmutable::parse('2019-08-11')->isBusinessday());
        $this->assertFalse(CarbonImmutable::parse('2019-08-12')->isBusinessday());
        $this->assertTrue(CarbonImmutable::parse('2019-08-13')->isBusinessday());
    }

    public function test_addBusinessday()
    {
        $this->assertEquals(Carbon::parse('2019-08-13'), Carbon::parse('2019-08-09')->addBusinessday());  // 金->(月祝)->火
        $this->assertEquals(Carbon::parse('2019-08-13'), Carbon::parse('2019-08-10')->addBusinessday());  // 土->(月祝)->火
        $this->assertEquals(Carbon::parse('2019-08-13'), Carbon::parse('2019-08-11')->addBusinessday());  // 日->(月祝)->火
        $this->assertEquals(Carbon::parse('2019-08-13'), Carbon::parse('2019-08-12')->addBusinessday());  // 月祝->火
        $this->assertEquals(Carbon::parse('2019-08-14'), Carbon::parse('2019-08-13')->addBusinessday());  // 火->水
    }

    public function test_addBusinessdays()
    {
        $this->assertEquals(Carbon::parse('2019-08-15'), Carbon::parse('2019-08-09')->addBusinessdays(3));  // 金->(月祝)->木
        $this->assertEquals(Carbon::parse('2019-08-15'), Carbon::parse('2019-08-10')->addBusinessdays(3));  // 土->(月祝)->木
        $this->assertEquals(Carbon::parse('2019-08-15'), Carbon::parse('2019-08-11')->addBusinessdays(3));  // 日->(月祝)->木
        $this->assertEquals(Carbon::parse('2019-08-15'), Carbon::parse('2019-08-12')->addBusinessdays(3));  // 月祝->火
        $this->assertEquals(Carbon::parse('2019-08-16'), Carbon::parse('2019-08-13')->addBusinessdays(3));  // 火->金
        $this->assertEquals(Carbon::parse('2019-08-07'), Carbon::parse('2019-08-13')->addBusinessdays(-3));  // 火<-(月祝)<-水
    }

    public function test_subBusinessday()
    {
        $this->assertEquals(Carbon::parse('2019-08-09'), Carbon::parse('2019-08-13')->subBusinessday());  // 火->(月祝)->金
        $this->assertEquals(Carbon::parse('2019-08-09'), Carbon::parse('2019-08-12')->subBusinessday());  // 月祝->金
        $this->assertEquals(Carbon::parse('2019-08-09'), Carbon::parse('2019-08-11')->subBusinessday());  // 日->金
        $this->assertEquals(Carbon::parse('2019-08-09'), Carbon::parse('2019-08-10')->subBusinessday());  // 土->金
        $this->assertEquals(Carbon::parse('2019-08-08'), Carbon::parse('2019-08-09')->subBusinessday());  // 金->木
    }

    public function test_subBusinessdays()
    {
        $this->assertEquals(Carbon::parse('2019-08-07'), Carbon::parse('2019-08-13')->subBusinessdays(3));  // 火->(月祝)->水
        $this->assertEquals(Carbon::parse('2019-08-07'), Carbon::parse('2019-08-12')->subBusinessdays(3));  // 月祝->水
        $this->assertEquals(Carbon::parse('2019-08-07'), Carbon::parse('2019-08-11')->subBusinessdays(3));  // 日->水
        $this->assertEquals(Carbon::parse('2019-08-07'), Carbon::parse('2019-08-10')->subBusinessdays(3));  // 土->水
        $this->assertEquals(Carbon::parse('2019-08-06'), Carbon::parse('2019-08-09')->subBusinessdays(3));  // 金->火
        $this->assertEquals(Carbon::parse('2019-08-15'), Carbon::parse('2019-08-09')->subBusinessdays(-3));  // 金<-(月祝)<-木
    }

    public function test_diffInBusinessdays()
    {
        $this->assertEquals(21, Carbon::parse('2019-08-01')->diffInBusinessdays(Carbon::parse('2019-09-01')));   // 月末が土曜
        $this->assertEquals(19, Carbon::parse('2019-09-01')->diffInBusinessdays(Carbon::parse('2019-10-01')));   // 月初が日曜
        $this->assertEquals(21, Carbon::parse('2019-10-01')->diffInBusinessdays(Carbon::parse('2019-11-01')));   // 月初月末が平日

        $this->assertEquals(1, Carbon::parse('2019-08-09')->diffInBusinessdays(Carbon::parse('2019-08-12'))); // 金->月祝
        $this->assertEquals(0, Carbon::parse('2019-08-10')->diffInBusinessdays(Carbon::parse('2019-08-12'))); // 土->月祝
        $this->assertEquals(0, Carbon::parse('2019-08-11')->diffInBusinessdays(Carbon::parse('2019-08-12'))); // 日->月祝
        $this->assertEquals(0, Carbon::parse('2019-08-12')->diffInBusinessdays(Carbon::parse('2019-08-12'))); // 月祝=月祝
        $this->assertEquals(0, Carbon::parse('2019-08-13')->diffInBusinessdays(Carbon::parse('2019-08-12'))); // 火<-月祝

        $this->assertEquals(1, Carbon::parse('2019-08-09')->diffInBusinessdays(Carbon::parse('2019-08-13'))); // 金->(月祝)->火
        $this->assertEquals(0, Carbon::parse('2019-08-10')->diffInBusinessdays(Carbon::parse('2019-08-13'))); // 土->(月祝)->火
        $this->assertEquals(0, Carbon::parse('2019-08-11')->diffInBusinessdays(Carbon::parse('2019-08-13'))); // 日->(月祝)->火
        $this->assertEquals(0, Carbon::parse('2019-08-12')->diffInBusinessdays(Carbon::parse('2019-08-13'))); // 月祝->火
        $this->assertEquals(0, Carbon::parse('2019-08-13')->diffInBusinessdays(Carbon::parse('2019-08-13'))); // 火=火

        $this->assertEquals(2, Carbon::parse('2019-08-09')->diffInBusinessdays(Carbon::parse('2019-08-14'))); // 金->(月祝)->水
        $this->assertEquals(1, Carbon::parse('2019-08-10')->diffInBusinessdays(Carbon::parse('2019-08-14'))); // 土->(月祝)->水
        $this->assertEquals(1, Carbon::parse('2019-08-11')->diffInBusinessdays(Carbon::parse('2019-08-14'))); // 日->(月祝)->水
        $this->assertEquals(1, Carbon::parse('2019-08-12')->diffInBusinessdays(Carbon::parse('2019-08-14'))); // 月祝->水
        $this->assertEquals(1, Carbon::parse('2019-08-13')->diffInBusinessdays(Carbon::parse('2019-08-14'))); // 火->水

        $this->assertEquals(0, Carbon::parse('2019-08-09')->diffInBusinessdays(Carbon::parse('2019-08-09'))); // 金=金
        $this->assertEquals(1, Carbon::parse('2019-08-10')->diffInBusinessdays(Carbon::parse('2019-08-09'))); // 土<-金
        $this->assertEquals(1, Carbon::parse('2019-08-11')->diffInBusinessdays(Carbon::parse('2019-08-09'))); // 日<-金
        $this->assertEquals(1, Carbon::parse('2019-08-12')->diffInBusinessdays(Carbon::parse('2019-08-09'))); // 月祝<-金
        $this->assertEquals(1, Carbon::parse('2019-08-13')->diffInBusinessdays(Carbon::parse('2019-08-09'))); // 火<-金

        $this->assertEquals(0, Carbon::parse('2019-08-09')->diffInBusinessdays(Carbon::parse('2019-08-09'), false)); // 金=金
        $this->assertEquals(-1, Carbon::parse('2019-08-10')->diffInBusinessdays(Carbon::parse('2019-08-09'), false)); // 土<-金
        $this->assertEquals(-1, Carbon::parse('2019-08-11')->diffInBusinessdays(Carbon::parse('2019-08-09'), false)); // 日<-金
        $this->assertEquals(-1, Carbon::parse('2019-08-12')->diffInBusinessdays(Carbon::parse('2019-08-09'), false)); // 月祝<-金
        $this->assertEquals(-1, Carbon::parse('2019-08-13')->diffInBusinessdays(Carbon::parse('2019-08-09'), false)); // 火<-金
    }
}
