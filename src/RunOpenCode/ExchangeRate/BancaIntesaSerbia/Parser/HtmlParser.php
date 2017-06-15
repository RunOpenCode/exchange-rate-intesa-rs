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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser;

use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum\RateType;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Api;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class HtmlParser
 *
 * Parse HTML document with daily rates from Banca Intesa Serbia.
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser
 */
class HtmlParser
{
    /**
     * @var RateInterface[]
     */
    private $rates;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $html;

    /**
     * HtmlParser constructor.
     * @param $node string
     * @param $date \DateTime
     */
    public function __construct($node, \DateTime $date)
    {
        $this->html = $node;
        $this->date = $date;
    }

    /**
     * @return array|\RunOpenCode\ExchangeRate\Contract\RateInterface[]
     */
    public function getRates()
    {
        if (empty($this->rates)) {
            $this->rates = $this->parseHtml($this->html, $this->date);
        }

        return $this->rates;
    }

    /**
     * @param $html string
     * @param \DateTime $date
     * @return array
     */
    private function parseHtml($html, \DateTime $date)
    {
        $crawler = new Crawler($html);
        $crawler = $crawler->filter('table');

        return $this->extractRates($crawler, $date);
    }

    /**
     * @param Crawler $crawler
     * @param \DateTime $date
     * @return array
     */
    private function extractRates(Crawler $crawler, \DateTime $date)
    {
        $extractedRates = array();

        $crawler->filter('tr')->each(function (Crawler $node) use ($date, &$extractedRates) {

            $row = $this->parseRow($node);

            if (null !== $row) {

                $extractedRates[] = $this->buildRate(
                    $row[RateType::MEDIAN] / $row['unit'],
                    $row['currencyCode'],
                    RateType::MEDIAN,
                    $date
                );

                $extractedRates[] = $this->buildRate(
                    $row[RateType::FOREIGN_EXCHANGE_BUYING] / $row['unit'],
                    $row['currencyCode'],
                    RateType::FOREIGN_EXCHANGE_BUYING,
                    $date
                );

                $extractedRates[] = $this->buildRate(
                    $row[RateType::FOREIGN_EXCHANGE_SELLING] / $row['unit'],
                    $row['currencyCode'],
                    RateType::FOREIGN_EXCHANGE_SELLING,
                    $this->date
                );

                $extractedRates[] = $this->buildRate(
                    $row[RateType::FOREIGN_CASH_BUYING] / $row['unit'],
                    $row['currencyCode'],
                    RateType::FOREIGN_CASH_BUYING,
                    $this->date
                );

                $extractedRates[] = $this->buildRate(
                    $row[RateType::FOREIGN_CASH_SELLING] / $row['unit'],
                    $row['currencyCode'],
                    RateType::FOREIGN_CASH_SELLING,
                    $this->date
                );
            }
        });

        /**
         * @var Rate $rate
         */
        foreach ($extractedRates as $key => $rate){
            if (!$rate->getValue()) {
                unset($extractedRates[$key]);
            }
        }

        return $extractedRates;
    }

    /**
     * @param Crawler $crawler
     * @return array|null
     */
    private function parseRow(Crawler $crawler)
    {
        $currentRow = array(
            'currencyCode' => ''
        );

        $nodeValues = $crawler->filter('td')->each(function (Crawler $node) {
            return trim($node->text());
        });

        if (count($nodeValues)) {
            $currentRow['currencyCode'] = trim($nodeValues[1]);
            $currentRow['unit'] = (int) trim($nodeValues[2]);
            $currentRow[RateType::FOREIGN_EXCHANGE_BUYING] = (float) trim($nodeValues[3]);
            $currentRow[RateType::MEDIAN] = (float) trim($nodeValues[4]);
            $currentRow[RateType::FOREIGN_EXCHANGE_SELLING] = (float) trim($nodeValues[5]);
            $currentRow[RateType::FOREIGN_CASH_BUYING] = (float) trim($nodeValues[6]);
            $currentRow[RateType::FOREIGN_CASH_SELLING] = (float) trim($nodeValues[7]);
        }

        return strlen($currentRow['currencyCode']) === 3 ? $currentRow : null;
    }

    /**
     * @param $value
     * @param $currencyCode
     * @param $rateType
     * @param $date
     * @return Rate
     */
    private function buildRate($value, $currencyCode, $rateType, $date)
    {
        return new Rate(Api::NAME, $value, $currencyCode, $rateType, $date, 'RSD', new \DateTime('now'), new \DateTime('now') );
    }
}
