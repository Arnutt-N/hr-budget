<?php

namespace App\Helpers;

class DateHelper
{
    /**
     * Thai month names
     */
    private static array $thaiMonths = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม'
    ];

    /**
     * Get Thai month name from month number
     */
    public static function thaiMonth(int $month): string
    {
        return self::$thaiMonths[$month] ?? '';
    }

    /**
     * Format date to Thai format (d เดือน พ.ศ.)
     */
    public static function thaiDate(?string $date): string
    {
        if (!$date) return '-';
        
        $timestamp = strtotime($date);
        if (!$timestamp) return $date;
        
        $day = date('j', $timestamp);
        $month = (int) date('n', $timestamp);
        $year = (int) date('Y', $timestamp) + 543; // Convert to Buddhist Era
        
        return "{$day} " . self::thaiMonth($month) . " {$year}";
    }

    /**
     * Format date to short Thai format (d ม.ค. พ.ศ.)
     */
    public static function thaiDateShort(?string $date): string
    {
        if (!$date) return '-';
        
        $shortMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        
        $timestamp = strtotime($date);
        if (!$timestamp) return $date;
        
        $day = date('j', $timestamp);
        $month = (int) date('n', $timestamp);
        $year = (int) date('Y', $timestamp) + 543;
        
        return "{$day} " . ($shortMonths[$month] ?? '') . " {$year}";
    }
}
