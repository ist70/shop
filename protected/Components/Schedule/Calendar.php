<?php

namespace App\Components\Schedule;

class Calendar
{
    protected $date;
    protected $weekDays = ["ПН" => [], "ВТ" => [], "СР" => [], "ЧТ" => [], "ПТ" => [], "СБ" => [], "ВС" => []];

    const MONTHS = [1=>'январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];
    const MONTHS_GEN = [1=>'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];

    public function __construct(\DateTime $dateObj = null)
    {
        if (null === $dateObj) {
            $this->date = new \DateTime();
        } else {
            $this->date = $dateObj;
        }
    }

    public function getMonthNoun($monthNum)
    {
        return self::MONTHS[$monthNum];
    }

    public function getMonthGen($monthNum)
    {
        return self::MONTHS_GEN[$monthNum];
    }

    protected function getMonthCalendar($month, $year)
    {
        $schedule = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfMonth = new \DateTime($year . '-' . $month . '-01');
        $firstDayOfMonthWeekDayNum = $firstDayOfMonth->format('N');

        if (28 == $daysInMonth && 1 == $firstDayOfMonthWeekDayNum) {
            $numOfCols = 4;
        } else {
            if (31 == $daysInMonth && (6 == $firstDayOfMonthWeekDayNum || 7 == $firstDayOfMonthWeekDayNum)) {
                $numOfCols = 6;
            } elseif (30 == $daysInMonth && 7 == $firstDayOfMonthWeekDayNum) {
                $numOfCols = 6;
            } else {
                $numOfCols = 5;
            }
        }

        $monthNum = $this->date->format('m');
        //Если первый день месяца выпадает на понедельник,значит всё хорошо
        //а иначе
        //получаем последний понедельник из предыдущего месяца
        if ($firstDayOfMonthWeekDayNum != 1) {
            $firstDayOfMonth->modify('last monday of ' . $firstDayOfMonth->sub(new \DateInterval('P1M'))->format('M Y'));
        }

        $firstDay = clone $firstDayOfMonth;
        foreach ($this->weekDays as $dayOfWeek => $v) {
            $currDt = clone $firstDay;
            //заполняем числа,которые соответствуют дню недели в текущей итерации
            for ($i = 0; $i < $numOfCols; $i++) {
                if ($monthNum == $currDt->format('m')) {
                    $schedule[$dayOfWeek][] = $currDt->format('Y-m-d');
                } else {
                    $schedule[$dayOfWeek][] = null;
                }
                $currDt->add(new \DateInterval('P7D'));
            }
            $firstDay->add(new \DateInterval('P1D'));
        }

        return $schedule;
    }

    public function getCurrentMonthCalendar()
    {
        $currMonth = $this->date->format('m');
        $currYear = $this->date->format('Y');
        return $this->getMonthCalendar($currMonth, $currYear);
    }

    public function getNextMonthCalendar()
    {
        $next = $this->date->add(new \DateInterval('P1M'));
        $nextMonth = $next->format('m');
        $nextYear = $next->format('Y');
        return $this->getMonthCalendar($nextMonth, $nextYear);
    }

    public function getPrevMonthCalendar()
    {
        $prev = $this->date->sub(new \DateInterval('P1M'));
        $prevMonth = $prev->format('m');
        $prevYear = $prev->format('Y');
        return $this->getMonthCalendar($prevMonth, $prevYear);
    }

}