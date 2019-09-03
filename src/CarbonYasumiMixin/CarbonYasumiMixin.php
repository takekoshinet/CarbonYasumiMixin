<?php
namespace CarbonYasumiMixin;

use Carbon\CarbonInterface;
use Yasumi\Yasumi;

class CarbonYasumiMixin
{
    protected $yasumi_list = [];
    protected $yasumi_class;
    protected $yasumi_locale;

    /**
     * コンストラクタ. Yasumi::create()の$year以外の引数を取る。
     *
     * @param string $yasumi_class  holiday provider name
     * @param string $yasumi_locale The locale to use. If empty we'll use the default locale (en_US)
     */
    public function __construct(string $yasumi_class, string $yasumi_locale = Yasumi::DEFAULT_LOCALE)
    {
        $this->yasumi_class  = $yasumi_class;
        $this->yasumi_locale = $yasumi_locale;
    }

    /**
     * Yasumiインスタンスを取得する。
     *
     * @return \Yasumi\ProviderInterface
     */
    public function getYasumi()
    {
        $yasumi_list   = &$this->yasumi_list;
        $yasumi_class  = &$this->yasumi_class;
        $yasumi_locale = &$this->yasumi_locale;

        return function () use (&$yasumi_list, &$yasumi_class, &$yasumi_locale) {
            if (! isset($yasumi_list[$yasumi_class][$this->year][$yasumi_locale])) {
                $yasumi_list[$yasumi_class][$this->year][$yasumi_locale] = Yasumi::create($yasumi_class, $this->year, $yasumi_locale);
            }

            return $yasumi_list[$yasumi_class][$this->year][$yasumi_locale];
        };
    }

    /**
     * 営業日ならtrue
     *
     * @return bool
     */
    public function isBusinessday()
    {
        return function () {
            return ! $this->isWeekend() && ! $this->getYasumi()->isHoliday($this);
        };
    }

    /**
     * 次営業日
     *
     * @return static
     */
    public function nextBusinessday()
    {
        return function () {
            return $this->addBusinessday();
        };
    }

    /**
     * 営業日を1日足す
     *
     * @return static
     */
    public function addBusinessday()
    {
        return function () {
            $ret = $this;
            do {
                $ret = $ret->addDay();
            } while (! $ret->isBusinessday());

            return $ret;
        };
    }

    /**
     * 営業日を指定日数分足す
     *
     * @param  int  $num
     * @return static
     */
    public function addBusinessdays()
    {
        return function (int $num) {
            if ($num < 0) {
                return $this->subBusinessdays($num * -1);
            }

            $ret = $this;
            for ($i = 0; $i < $num; $i++) {
                $ret = $ret->addBusinessday();
            }
            return $ret;
        };
    }

    /**
     * 前営業日
     *
     * @return static
     */
    public function previousBusinessday()
    {
        return function () {
            return $this->subBusinessday();
        };
    }

    /**
     * 営業日を1日引く
     *
     * @return static
     */
    public function subBusinessday()
    {
        return function () {
            $ret = $this;
            do {
                $ret = $ret->subDay();
            } while (! $ret->isBusinessday());

            return $ret;
        };
    }

    /**
     * 営業日を指定日数分引く
     *
     * @param  int  $num
     * @return static
     */
    public function subBusinessdays()
    {
        return function (int $num) {
            if ($num < 0) {
                return $this->addBusinessdays($num * -1);
            }

            $ret = $this;
            for ($i = 0; $i < $num; $i++) {
                $ret = $ret->subBusinessday();
            }
            return $ret;
        };
    }

    /**
     * 営業日の日数差分を取得する
     *
     * @param \Carbon\CarbonInterface|\DateTimeInterface|string|null $date
     * @param bool                                                   $absolute Get the absolute of the difference
     * @return int
     */
    public function diffInBusinessdays()
    {
        return function ($date = null, $absolute = true) {
            return $this->diffInDaysFiltered(function (CarbonInterface $date) {
                return static::instance($date)->isBusinessday();
            }, $date, $absolute);
        };
    }
}
