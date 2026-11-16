<?php

declare(strict_types=1);

namespace App\Helpers;

final class FiscalCode
{
    public const EXPENSE_CATEGORIES = [
        '010' => 'งบบุคลากร',
        '020' => 'งบดำเนินงาน',
        '031' => 'ครุภัณฑ์และค่าจ้างบริการ',
        '032' => 'ค่าก่อสร้างและปรับปรุงอาคาร',
        '041' => 'งบเงินอุดหนุน',
        '050' => 'ค่าใช้จ่ายโครงการ',
    ];

    public static function isValid20(string $code): bool
    {
        return preg_match('/^\d{20}\z/', $code) === 1;
    }

    public static function parent13(string $code20): string
    {
        return substr($code20, 0, 13);
    }

    public static function expenseCategory(string $code20): string
    {
        return substr($code20, 12, 3);
    }

    public static function isValidExpenseCategory(string $code20): bool
    {
        if (!self::isValid20($code20)) {
            return false;
        }

        return in_array(self::expenseCategory($code20), array_keys(self::EXPENSE_CATEGORIES), true);
    }

    public static function isValidActivityCode(string $code): bool
    {
        return preg_match('/^\d{17}\z/', $code) === 1;
    }

    public static function activityGroup(string $code20): string
    {
        return substr($code20, 0, 12);
    }
}
