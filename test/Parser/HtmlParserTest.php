<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * Implementation of exchange rate crawler for Banca Intesa Serbia, http://www.bancaintesa.rs.
 *
 * (c) 2017 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Tests\Parser;

use PHPUnit\Framework\TestCase;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser\HtmlParser;

class HtmlParserTest extends TestCase
{

    /**
     * @test
     */
    public function parsedRatesCount()
    {
        $htmlParser = new HtmlParser(file_get_contents(__DIR__ . '/../Fixtures/data.html'), new \DateTime('now'));
        $this->assertSame(86, count($htmlParser->getRates()));
    }

    /**
     * @test
     */
    public function parsedRatesAreCorrect()
    {
        $htmlParser = new HtmlParser(file_get_contents(__DIR__ . '/../Fixtures/data.html'), new \DateTime('now'));
        $rates = $htmlParser->getRates();

        $this->assertContainsOnlyInstancesOf('RunOpenCode\ExchangeRate\Model\Rate', $rates);
    }
}
