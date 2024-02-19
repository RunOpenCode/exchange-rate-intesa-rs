<?php

declare(strict_types=1);

namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Tests;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Api;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum\RateType;

final class ApiTest extends TestCase
{
    /**
     * @dataProvider getDataForTestSupports
     *
     * @return void
     */
    public function testSupports(string $currencyCode, string $rateType, bool $expected)
    {
        $this->assertSame($expected, Api::supports($currencyCode, $rateType));
    }

    /**
     * @return iterable<string,array{string, string, bool}>
     */
    public function getDataForTestSupports()
    {
        yield 'It does support EUR for median' => ['EUR', RateType::MEDIAN, true];
        yield 'It does not support invalid rate type' => ['EUR', 'foo', false];
        yield 'It does not support ATS for foreign cash buying' => ['ATS', RateType::FOREIGN_EXCHANGE_SELLING, false];
    }
}