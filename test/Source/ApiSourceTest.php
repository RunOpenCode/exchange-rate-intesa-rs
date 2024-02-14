<?php

declare(strict_types=1);

namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Tests\Source;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Api;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum\RateType;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception\RuntimeException;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Source\ApiSource;
use RunOpenCode\ExchangeRate\Exception\UnknownCurrencyCodeException;

final class ApiSourceTest extends TestCase
{
    /**
     * @test
     * @dataProvider getDataForTestFetch
     *
     * @return void
     */
    public function testFetch(string $type, string $currency, float $expectedValue)
    {
        $source = new ApiSource();

        $result = $source->fetch($currency, $type, new \DateTime('2024-02-01'));
        $this->assertSame($expectedValue, $result->getValue());
        $this->assertSame('banca_intesa_serbia', $result->getSourceName());
        $this->assertSame($currency, $result->getCurrencyCode());
        $this->assertSame($type, $result->getRateType());
        $this->assertSame('RSD', $result->getBaseCurrencyCode());
    }

    /**
     * @return iterable<string, array{string,string, float}>
     */
    public function getDataForTestFetch()
    {
        yield 'Test median type' => [RateType::MEDIAN, 'EUR', 117.1679];
        yield 'Test foreign exchange buying type' => [RateType::FOREIGN_EXCHANGE_BUYING, 'USD', 103.0072];
        yield 'Test foreign cash buying type' => [RateType::FOREIGN_CASH_BUYING, 'BAM', 0.0];
        yield 'Test foreign exchange selling type' => [RateType::FOREIGN_EXCHANGE_SELLING, 'AUD', 74.4441];
        yield 'Test foreign cash selling type' => [RateType::FOREIGN_CASH_SELLING, 'GBP', 148.3312];
    }

    /**
     * @return void
     */
    public function testItThrowsUnknownCurrencyExceptionInvalidCurrencyIsProvided()
    {
        $this->expectException(UnknownCurrencyCodeException::class);

        $source = new ApiSource();
        $source->fetch('foo');
    }

    /**
     * @return void
     */
    public function testItThrowsInvalidArgumentExceptionWhenInvalidRateTypeIsProvided()
    {
        $this->expectException(RuntimeException::class);

        $source = new ApiSource();
        $source->fetch('USD', 'foo');
    }

    /**
     * @return void
     */
    public function testItThrowsExceptionWhenRequestFails()
    {
        $this->expectException(SourceNotAvailableException::class);

        $client = $this->createMock(Client::class);
        $client->method('request')->willThrowException(new \Exception());

        /** @var Client $client */
        $source = new ApiSource($client);
        $source->fetch('USD');
    }

    /**
     * @test
     *
     * @return void
     */
    public function testName()
    {
        $source = new ApiSource();
        $this->assertEquals(Api::NAME, $source->getName());
    }
}