<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * Implementation of exchange rate crawler for Banca Intesa Serbia, http://www.bancaintesa.rs.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Tests\Source;

use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Util\BancaIntesaBrowser;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Source\WebPageSource;

class WebPageSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function fetchMedian()
    {
        $rate = $this->mockSource('default')->fetch('EUR', 'default');
        $this->assertSame(122.4168, $rate->getValue());
    }

    /**
     * @test
     */
    public function fetchForeignCash()
    {
        $rate = $this->mockSource('foreign_cash_buying')->fetch('EUR', 'foreign_cash_buying');
        $this->assertSame(119.3564, $rate->getValue());
    }

    /**
     * @test
     */
    public function fetchForeignExchange()
    {
        $rate = $this->mockSource('foreign_exchange_buying')->fetch('EUR', 'foreign_exchange_buying');
        $this->assertSame(119.3564, $rate->getValue());
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function unsupported()
    {
        $source = new WebPageSource(new BancaIntesaBrowser());
        $source->fetch('EUR', 'not_supported');
    }

    /**
     * Mock source.
     *
     * @param string $rateType
     * @return WebPageSource
     */
    protected function mockSource($rateType)
    {
        $stub = $this->getMockBuilder(BancaIntesaBrowser::class)->getMock();
        $stub->method('getHtmlDocument')->willReturn(file_get_contents(__DIR__ . '/../Fixtures/data.html'));

        return new WebPageSource($stub);
    }
}
