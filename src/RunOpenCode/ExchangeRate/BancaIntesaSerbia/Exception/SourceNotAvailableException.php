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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception;

use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Contract\ExceptionInterface;

/**
 * Class SourceNotAvailableException
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception
 */
class SourceNotAvailableException extends \RunOpenCode\ExchangeRate\Exception\SourceNotAvailableException implements ExceptionInterface
{

}
