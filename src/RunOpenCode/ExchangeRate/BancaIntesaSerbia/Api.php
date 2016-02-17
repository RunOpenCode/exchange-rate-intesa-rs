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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia;

/**
 * Class Api
 *
 * Api definition of Banca Intesa Serbia crawler.
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia
 */
final class Api
{
    /**
     * Unique name of source.
     */
    const NAME = 'banca_intesa_serbia';

    /**
     * Supported rate types and currency codes by Banca Intesa Serbia.
     *
     * NOTE: Banca Intesa Serbia still publishes rates of some of the obsolete currencies.
     *
     * @var array
     */
    private static $supports = array(
        'default' => array('EUR', 'AUD', 'ATS', 'BEF', 'CAD', 'CNY', 'HRK', 'CZK', 'DKK', 'FIM', 'FRF', 'DEM', 'GRD',
                           'HUF', 'IEP', 'ITL', 'JPY', 'KWD', 'LUF', 'NOK', 'PTE', 'RUB', 'SKK', 'ESP', 'SEK', 'CHF',
                           'GBP', 'USD', 'BAM', 'PLN'),
        'foreign_cash_buying' => array('EUR', 'AUD', 'CAD', 'HRK', 'CZK', 'DKK', 'HUF', 'JPY', 'NOK', 'RUB', 'SEK',
                                       'CHF', 'GBP', 'USD', 'BAM', 'PLN'),
        'foreign_cash_selling' => array('EUR', 'AUD', 'CAD', 'HRK', 'CZK', 'DKK', 'HUF', 'JPY', 'NOK', 'RUB', 'SEK',
                                        'CHF', 'GBP', 'USD', 'BAM', 'PLN'),
        'foreign_exchange_buying' => array('EUR', 'AUD', 'CAD', 'CNY', 'DKK', 'JPY', 'NOK', 'RUB', 'SEK', 'CHF', 'GBP', 'USD'),
        'foreign_exchange_selling' => array('EUR', 'AUD', 'CAD', 'CNY', 'DKK', 'JPY', 'NOK', 'RUB', 'SEK', 'CHF', 'GBP', 'USD')
    );

    private function __construct() { }

    /**
     * Check if Banca Intesa Serbia supports given exchange rate currency code for given rate type.
     *
     * @param string $currencyCode Currency code.
     * @param string $rateType Rate type.
     * @return bool TRUE if currency code within rate type is supported.
     */
    public static function supports($currencyCode, $rateType)
    {
        if (
            !array_key_exists($rateType, self::$supports)
            ||
            !in_array($currencyCode, self::$supports[$rateType], true)
        ) {
            return false;
        }

        return true;
    }
}
