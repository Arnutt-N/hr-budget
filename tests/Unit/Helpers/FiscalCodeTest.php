<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use App\Helpers\FiscalCode;

final class FiscalCodeTest extends TestCase
{
    private const VALID_20 = '16002070012004100001';

    public function testIsValid20AcceptsRealChildCode(): void
    {
        $this->assertTrue(FiscalCode::isValid20(self::VALID_20));
        $this->assertTrue(FiscalCode::isValid20('16002070012005000001'));
    }

    public function testIsValid20RejectsWrongLengths(): void
    {
        $this->assertFalse(FiscalCode::isValid20(substr(self::VALID_20, 0, 19)));
        $this->assertFalse(FiscalCode::isValid20(self::VALID_20 . '1'));
    }

    public function testIsValid20RejectsLetters(): void
    {
        $this->assertFalse(FiscalCode::isValid20('1600207001200410000a'));
    }

    public function testIsValid20RejectsEmptyString(): void
    {
        $this->assertFalse(FiscalCode::isValid20(''));
    }

    public function testIsValid20RejectsTrailingNewline(): void
    {
        $this->assertFalse(FiscalCode::isValid20(self::VALID_20 . "\n"));
    }

    public function testParent13ReturnsThirteenCharacters(): void
    {
        $parent = FiscalCode::parent13(self::VALID_20);
        $this->assertSame('1600207001200', $parent);
        $this->assertSame(13, strlen($parent));
    }

    public function testParent13IsPrefixOfChildCode(): void
    {
        $this->assertSame(substr(self::VALID_20, 0, 13), FiscalCode::parent13(self::VALID_20));
    }

    public function testExpenseCategoryReturnsThreeDigits(): void
    {
        $this->assertSame('041', FiscalCode::expenseCategory(self::VALID_20));
        $this->assertSame(3, strlen(FiscalCode::expenseCategory(self::VALID_20)));
    }

    public function testExpenseCategoryAcceptsAllSixCategories(): void
    {
        foreach (array_keys(FiscalCode::EXPENSE_CATEGORIES) as $category) {
            $code = substr(self::VALID_20, 0, 12) . $category . '00001';
            $this->assertTrue(
                FiscalCode::isValid20($code),
                "constructed code {$code} must be 20 digits"
            );
            $this->assertTrue(
                FiscalCode::isValidExpenseCategory($code),
                "category {$category} must be accepted"
            );
        }
    }

    public function testIsValidExpenseCategoryRejectsUnknownCategory(): void
    {
        $this->assertFalse(FiscalCode::isValidExpenseCategory('16002070012009900001'));
    }

    public function testIsValidExpenseCategoryRejectsShortCodeEndingInValidCategory(): void
    {
        $this->assertFalse(FiscalCode::isValidExpenseCategory('160020700120041'));
    }

    public function testExpenseCategoryLabelsMatchExpenseTypes(): void
    {
        $this->assertSame(
            [
                '010' => 'งบบุคลากร',
                '020' => 'งบดำเนินงาน',
                '031' => 'ครุภัณฑ์และค่าจ้างบริการ',
                '032' => 'ค่าก่อสร้างและปรับปรุงอาคาร',
                '041' => 'งบเงินอุดหนุน',
                '050' => 'ค่าใช้จ่ายโครงการ',
            ],
            FiscalCode::EXPENSE_CATEGORIES
        );
    }

    public function testExpenseCategoriesConstantHasExactlyTheSixSeedCodes(): void
    {
        $this->assertSame(
            ['010', '020', '031', '032', '041', '050'],
            array_keys(FiscalCode::EXPENSE_CATEGORIES)
        );
        $this->assertCount(6, FiscalCode::EXPENSE_CATEGORIES);
    }

    public function testIsValidActivityCodeAcceptsSeventeenDigits(): void
    {
        $this->assertTrue(FiscalCode::isValidActivityCode('16002690003800000'));
    }

    public function testIsValidActivityCodeRejectsSixteenDigits(): void
    {
        $this->assertFalse(FiscalCode::isValidActivityCode('1600269000380000'));
    }

    public function testIsValidActivityCodeRejectsEighteenDigits(): void
    {
        $this->assertFalse(FiscalCode::isValidActivityCode('160026900038000001'));
    }

    public function testIsValidActivityCodeRejectsTrailingNewline(): void
    {
        $this->assertFalse(FiscalCode::isValidActivityCode('16002690003800000' . "\n"));
    }

    public function testActivityGroupReturnsTwelveCharacterPrefix(): void
    {
        $group = FiscalCode::activityGroup(self::VALID_20);
        $this->assertSame('160020700120', $group);
        $this->assertSame(12, strlen($group));
    }

    public function testActivityGroupIsPrefixOfParent13(): void
    {
        $this->assertSame(
            substr(FiscalCode::parent13(self::VALID_20), 0, 12),
            FiscalCode::activityGroup(self::VALID_20)
        );
    }
}