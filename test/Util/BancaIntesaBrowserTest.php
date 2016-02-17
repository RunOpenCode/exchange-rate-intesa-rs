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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Tests\Util;

use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Util\BancaIntesaBrowser;

class BancaIntesaBrowserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function documentReceived()
    {
        $browser = new BancaIntesaBrowser();
        $htmlDoc = $browser->getHtmlDocument(new \DateTime('now'));


        $supported = array('EUR', 'AUD', 'ATS', 'BEF', 'CAD', 'CNY', 'HRK', 'CZK', 'DKK', 'FIM', 'FRF', 'DEM', 'GRD',
            'HUF', 'IEP', 'ITL', 'JPY', 'KWD', 'LUF', 'NOK', 'PTE', 'RUB', 'SKK', 'ESP', 'SEK', 'CHF',
            'GBP', 'USD', 'BAM', 'PLN');

        foreach ($supported as $cc) {
            $this->assertContains($cc, $htmlDoc, sprintf('Should contain column with currency code "%s".', $cc));
        }
    }

    /**
     * @test
     */
    public function documentReceivedForDateInPast()
    {
        $browser = new BancaIntesaBrowser();
        $htmlDoc = $browser->getHtmlDocument(new \DateTime('2016-01-01'));


        $supported = array('EUR', 'AUD', 'ATS', 'BEF', 'CAD', 'CNY', 'HRK', 'CZK', 'DKK', 'FIM', 'FRF', 'DEM', 'GRD',
            'HUF', 'IEP', 'ITL', 'JPY', 'KWD', 'LUF', 'NOK', 'PTE', 'RUB', 'SKK', 'ESP', 'SEK', 'CHF',
            'GBP', 'USD', 'BAM', 'PLN');

        foreach ($supported as $cc) {
            $this->assertContains($cc, $htmlDoc, sprintf('Should contain column with currency code "%s".', $cc));
        }
    }
}
