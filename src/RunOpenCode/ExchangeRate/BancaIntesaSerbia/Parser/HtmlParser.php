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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser;

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
        $rates = array();

        $crawler->filter('tr')->each(function (Crawler $node) use ($date, &$rates) {

            $row = $this->parseRow($node);

            if (null !== $row) {

                $rates[] = $this->buildRate(
                    $row['default'] / $row['unit'],
                    $row['currencyCode'],
                    'default',
                    $date
                );

                $rates[] = $this->buildRate(
                    $row['foreign_exchange_buying'] / $row['unit'],
                    $row['currencyCode'],
                    'foreign_exchange_buying',
                    $date
                );

                $rates[] = $this->buildRate(
                    $row['foreign_exchange_selling'] / $row['unit'],
                    $row['currencyCode'],
                    'foreign_exchange_selling',
                    $this->date
                );

                $rates[] = $this->buildRate(
                    $row['foreign_cash_buying'] / $row['unit'],
                    $row['currencyCode'],
                    'foreign_cash_buying',
                    $this->date
                );

                $rates[] = $this->buildRate(
                    $row['foreign_cash_selling'] / $row['unit'],
                    $row['currencyCode'],
                    'foreign_cash_selling',
                    $this->date
                );
            }
        });

        /**
         * @var Rate $rate
         */
        foreach ($rates as $key => $rate){
            if (!$rate->getValue()) {
                unset($rates[$key]);
            }
        }

        return $rates;
    }

    private function parseRow(Crawler $crawler)
    {
        $currentRow = array(
            'currencyCode' => ''
        );

        $crawler->filter('td')->each(function (Crawler $node, $i) use (&$currentRow) {

            switch ($i) {
                case 1:
                    $currentRow['currencyCode'] = trim($node->text());
                    break;
                case 2:
                    $currentRow['unit'] = (int)trim($node->text());
                    break;
                case 3:
                    $currentRow['foreign_exchange_buying'] = (float)trim($node->text());
                    break;
                case 4:
                    $currentRow['default'] = (float)trim($node->text());
                    break;
                case 5:
                    $currentRow['foreign_exchange_selling'] = (float)trim($node->text());
                    break;
                case 6:
                    $currentRow['foreign_cash_buying'] = (float)trim($node->text());
                    break;
                case 7:
                    $currentRow['foreign_cash_selling'] = (float)trim($node->text());
                    break;
            }
        });

        return strlen($currentRow['currencyCode']) === 3 ? $currentRow : null;
    }

    private function buildRate($value, $currencyCode, $rateType, $date) {

        return new Rate(
            Api::NAME,
            $value,
            $currencyCode,
            $rateType,
            $date,
            'RSD',
            new \DateTime('now'),
            new \DateTime('now')
        );
    }
}