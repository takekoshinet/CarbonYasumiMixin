<?php
namespace CarbonYasumi;

use Carbon\CarbonInterface;
use Yasumi\Yasumi;

trait CarbonYasumiTrait
{
    protected static $yasumi_list = [];

    protected function getYasumi()
    {

        if (! isset(static::$yasumi_list[$this->year])) {
            static::$yasumi_list[$this->year] = Yasumi::create('Japan', $this->year, 'ja_JP');
        }

        return static::$yasumi_list[$this->year];
    }

    /**
     * 営業日ならtrue
     *
     * @return bool
     */
    public function isBusinessday() : bool
    {
        return ! $this->isWeekend() && ! $this->getYasumi()->isHoliday($this);
    }

    /**
     * 次営業日
     *
     * @return static
     */
    public function nextBusinessday() : self
    {
        return $this->addBusinessday();
    }

    /**
     * 営業日を1日足す
     *
     * @return static
     */
    public function addBusinessday() : self
    {
        $ret = $this;
        do {
            $ret = $ret->addDay();
        } while (! $ret->isBusinessday());

        return $ret;
    }

    /**
     * 営業日を指定日数分足す
     *
     * @param  int  $num
     * @return static
     */
    public function addBusinessdays(int $num) : self
    {
        if ($num < 0) {
            return $this->subBusinessdays($num * -1);
        }

        $ret = $this;
        for ($i = 0; $i < $num; $i++) {
            $ret = $ret->addBusinessday();
        }
        return $ret;
    }

    /**
     * 前営業日
     *
     * @return static
     */
    public function previousBusinessday() : self
    {
        return $this->subBusinessday();
    }

    /**
     * 営業日を1日引く
     *
     * @return static
     */
    public function subBusinessday() : self
    {
        $ret = $this;
        do {
            $ret = $ret->subDay();
        } while (! $ret->isBusinessday());

        return $ret;
    }

    /**
     * 営業日を指定日数分引く
     *
     * @param  int  $num
     * @return static
     */
    public function subBusinessdays(int $num) : self
    {
        if ($num < 0) {
            return $this->addBusinessdays($num * -1);
        }

        $ret = $this;
        for ($i = 0; $i < $num; $i++) {
            $ret = $ret->subBusinessday();
        }
        return $ret;
    }

    /**
     * 営業日の日数差分を取得する
     *
     * @param \Carbon\CarbonInterface|\DateTimeInterface|string|null $date
     * @param bool                                                   $absolute Get the absolute of the difference
     *
     * @return int
     */
    public function diffInBusinessdays($date = null, $absolute = true) : int
    {
        return $this->diffInDaysFiltered(function (CarbonInterface $date) {
            return static::instance($date)->isBusinessday();
        }, $date, $absolute);
    }
}
