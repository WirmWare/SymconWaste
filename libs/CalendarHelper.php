<?php

/**
 * CalendarHelper.php
 *
 * Part of the Trait-Libraray for IP-Symcon Modules.
 *
 * @package       traits
 * @author        Heiko Wilknitz <heiko@wilkware.de>
 * @copyright     2021 Heiko Wilknitz
 * @link          https://wilkware.de
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 */

declare(strict_types=1);

/**
 * Helper class for the debug output.
 */
trait CalendarHelper
{
    /**
     * Generic start days of seasons!
     */
    public static $SEASON = [
        'Spring' => [3, 20, 8],
        'Summer' => [6, 21, 0],
        'Fall'   => [9, 22, 14],
        'Winter' => [12, 21, 12],
    ];

    /**
     * Calculate the number of days in a month.
     *
     * @param int $year  Year (YYYY).
     * @param int $month Month (1-12).
     * @return int Number of days (28-31)
     */
    protected function DaysInMonth($year, $month)
    {
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }

    /**
     * Calculate an date for season starts (spring, summer, fall, winter).
     *
     * @param int $year  Year (YYYY).
     * @param int $month Month (1-12).
     * @param int $day   Day (1-31).
     * @param int $shift Shift (0,8,12,14).
     * @return string Date in the format d.m.Y (e.g. 21.3.2021)
     */
    protected function DateForSeason($year, $month, $day, $shift)
    {
        return $this->DateOf($year, $month, $day + ($shift >> $year % 4 & 1));
    }

    /**
     * Calculate an date with a reference to absolute day number in the year.
     *
     * @param int $year   Year (YYYY).
     * @param int $day    Day (1-356).
     * @param int $offset Offset (0..).
     * @param int $wd     Weekday (0..6, 0 = sunday).
     * @return string Date in the format d.m.Y (e.g. 1.1.1970)
     */
    protected function DateWithReference($year, $day, $offset, $wd)
    {
        $day += $offset - ($day - ceil(floor($year / 100) * 7 / 4) + floor($year % 100 * 5 / 4) + 2 - $wd) % 7 + $this->DaysInMonth($year, 2);
        $month = 2;
        while ($day > $this->DaysInMonth($year, $month)) {
            $day -= $this->DaysInMonth($year, $month++);
        }
        return $this->DateOf($year, $month, $day);
    }

    /**
     * Calculate an date based on an positiv or negativ offset to easter.
     *
     * @param int $year  Year (YYYY).
     * @param int $offset Offset (+/-days).
     * @return string Date in the format d.m.Y (e.g. 1.1.1970)
     */
    protected function DateToEaster($year, $offset)
    {
        $yr = $year % 19 + 1;
        return $this->DateWithReference($year, 57 - ($yr * 11 - 6) % 30 - ($yr * 11 % 30 == 6 + ($yr > 11 ? 1 : 0) ? 1 : 0), $offset, 0);
    }

    /**
     * Builds an date.
     *
     * @param int $year  Year (YYYY).
     * @param int $month Month (1-12).
     * @param int $day   Day (1-31).
     * @return string Date in the format yyyyddmm (e.g. 19700101)
     */
    protected function DateOf($year, $month, $day)
    {
        //$this->SendDebug("DateOf:", $year . '.' . $month . '.' . $day);
        return sprintf('%d%02d%02d', $year, $month, $day);
    }

    /**
     * Adds functionality to serialize arrays and objects.
     *
     * @param int $ts Timestamp.
     * @return string Season name (Spring, Summer, Fall, winter).
     */
    protected function Season($ts)
    {
        $date = date('Ymd', $ts);
        $year = date('Y', $ts);

        $season = 'Winter';
        foreach (self::$SEASON as $key => $value) {
            if ($date < $this->DateForSeason($year, $value[0], $value[1], $value[2])) {
                return $season;
            }
            $season = $key;
        }
        return $season;
    }
}
