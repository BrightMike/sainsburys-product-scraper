<?php

namespace App;
use Goutte\Client;


class Scraper {

    /**
     * Goutte Client
     */
    protected $client;

    public function __construct() {
        $this->client = new Client();
    }
    
    /**
     * Find all product urls from a listing page
     *
     * @param string $listingUrl The url of the listing page
     * @return Collection Urls in a collection
     */
    public function scrapeProdUrlsFromListing($listingUrl) {
        $crawler = $this->client->request('GET', $listingUrl);
        $prodUrls = $crawler->filter('.productInfo > h3 > a')->extract(array('href'));
        
        return collect($prodUrls);
    }


    /**
     * Scrape a products data from its URL
     *
     * @param string $url The url of the product page
     * @return array<string> The product title, unit_price and description in an array
     **/
    public function scrapeProduct($url) {
        $crawler = $this->client->request('GET', $url);
        
        return $this->parseProductInfo($crawler);
    }


    /**
     * Parse a products info from the Dom Crawler object loaded with html
     *
     * @param DomCrawler $crawler A Dom crawler loaded with html content
     * @return array<string> The product title, unit_price and description in an array
     **/
    protected function parseProductInfo($crawler) {
        $rawPrice = $crawler->filter('.productSummary .pricing > .pricePerUnit')->first()->text();
        $rawTitle = $crawler->filter('.productSummary .productTitleDescriptionContainer > h1')->first()->text();
        $rawDescription = $crawler->filter('.productDataItemHeader:first-child + .productText')->first()->text();
        
        $product = [
            "title" => $this->parseTitle($rawTitle),
            "unit_price" => $this->parsePrice($rawPrice),
            "description" => $this->parseDescription($rawDescription)
        ];

        return $product;
    }


    /**
     * Return the length in kb of the last page request
     *
     * @return string The length in kb of the previous scraped page
     */
    public function getResponseContentLength() {
        $contentLength = round($this->client->getResponse()->getHeader("Content-Length", true)/1024, 2);
    
        return $contentLength . 'kb';
    }
    
    /** 
    * Parse the numerical value from the price string
    *
    * @return bool | float False on failure. The price on success.
    *
    */
    protected function parsePrice($price) {
        $pricePattern = "/[0-9]+\.[0-9]{2}/";
        preg_match($pricePattern, $price, $matches);

        return !empty($matches) ? $matches[0] : false;
    }

    /**
     * Decode any html entities and trim the raw title
     *
     * @param string $title The raw title
     * @return string The cleaned title
     */
    protected function parseTitle($title) {
        return trim(html_entity_decode($title));
    }

    /**
     * Parse the raw description, decodes html and trims whitespace
     *
     * @param string $description The raw description
     * @return string The parsed description
     **/
    protected function parseDescription($description) {
        return trim(html_entity_decode($description));
    }
}
