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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum;

/**
 * Class RateType
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum
 */
final class RateType
{
    const MEDIAN = 'median';
    const FOREIGN_CASH_BUYING = 'foreign_cash_buying';
    const FOREIGN_CASH_SELLING = 'foreign_cash_selling';
    const FOREIGN_EXCHANGE_BUYING = 'foreign_exchange_buying';
    const FOREIGN_EXCHANGE_SELLING = 'foreign_exchange_selling';

    private function __construct() { /* noop */ }
}
