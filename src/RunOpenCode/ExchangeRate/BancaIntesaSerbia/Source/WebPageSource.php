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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Source;

use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum\RateType;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception\RuntimeException;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Log\LoggerAwareTrait;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Api;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Util\BancaIntesaBrowser;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser\HtmlParser;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

/**
 * Class WebPageSource
 *
 * Fetch rates from Banca Intesa website, as public user, without using their API service.
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia\Source
 */
final class WebPageSource implements SourceInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $cache;

    /**
     * @var BancaIntesaBrowser
     */
    private $browser;

    public function __construct(BancaIntesaBrowser $browser = null)
    {
        $this->browser = ($browser !== null) ? $browser : new BancaIntesaBrowser();
        $this->cache = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Api::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($currencyCode, $rateType = RateType::DEFAULT, \DateTime $date = null)
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);

        if (!Api::supports($currencyCode, $rateType)) {
            throw new RuntimeException(sprintf('Banca Intesa Serbia does not supports currency code "%s" for rate type "%s".', $currencyCode, $rateType));
        }

        if ($date === null) {
            $date = new \DateTime('now');
        }

        if (!array_key_exists($rateType, $this->cache)) {

            try {
                $this->load($date);

            } catch (\Exception $e) {
                $message = sprintf('Unable to load data from "%s" for "%s" of rate type "%s".', $this->getName(), $currencyCode, $rateType);

                $this->getLogger()->emergency($message);
                throw new SourceNotAvailableException($message, 0, $e);
            }
        }

        if (array_key_exists($currencyCode, $this->cache[$rateType])) {
            return $this->cache[$rateType][$currencyCode];
        }

        $message = sprintf('API Changed: source "%s" does not provide currency code "%s" for rate type "%s".', $this->getName(), $currencyCode, $rateType);
        $this->getLogger()->critical($message);
        throw new RuntimeException($message);
    }

    /**
     * Load rates from Banca Intesa Serbia website.
     *
     * @param \DateTime $date
     * @return RateInterface[]
     * @throws SourceNotAvailableException
     */
    private function load(\DateTime $date)
    {
        $parser = new HtmlParser($this->browser->getHtmlDocument($date), $date);

        foreach ($parser->getRates() as $rate) {

            if (!array_key_exists($rate->getRateType(), $this->cache)) {
                $this->cache[$rate->getRateType()] = array();
            }

            $this->cache[$rate->getRateType()][$rate->getCurrencyCode()] = $rate;
        }
    }
}
