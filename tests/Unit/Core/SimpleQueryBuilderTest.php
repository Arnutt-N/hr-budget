<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\SimpleQueryBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class SimpleQueryBuilderTest extends TestCase
{
    public function testWhereRejectsInvalidIdentifier(): void
    {
        $qb = new SimpleQueryBuilder('users');
        $this->expectException(InvalidArgumentException::class);
        $qb->where('id = 1 OR 1=1', 1);
    }

    public function testWhereNullRejectsInvalidIdentifier(): void
    {
        $qb = new SimpleQueryBuilder('users');
        $this->expectException(InvalidArgumentException::class);
        $qb->whereNull('deleted_at; DROP TABLE users');
    }

    public function testWhereNotNullRejectsInvalidIdentifier(): void
    {
        $qb = new SimpleQueryBuilder('users');
        $this->expectException(InvalidArgumentException::class);
        $qb->whereNotNull('1bad_col');
    }

    public function testOrderByRejectsInvalidIdentifier(): void
    {
        $qb = new SimpleQueryBuilder('users');
        $this->expectException(InvalidArgumentException::class);
        $qb->orderBy('id DESC; --');
    }

    public function testValidIdentifiersReturnBuilder(): void
    {
        $qb = new SimpleQueryBuilder('users');
        $result = $qb
            ->where('user_id', 1)
            ->whereNull('deleted_at')
            ->whereNotNull('created_at')
            ->orderBy('created_at', 'desc');

        $this->assertSame($qb, $result);
    }
}
