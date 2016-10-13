<?php

namespace App;
use Goutte\Client;


class Scraper {

    /**
     * Goutte Client
     */
    protected $client;

    function __construct() {
        $this->client = new Client();
    }
    
    /**
     * Find all product urls from a listing page
     *
     * @param string $listingUrl The url of the listing page
     * @return Collection Urls in a collection
     */
    function scrapeProdUrlsFromListing($listingUrl) {
        $crawler = $this->client->request('GET', $listingUrl);
        $prodUrls = $crawler->filter('.productInfo > h3 > a')->extract(array('href'));
        
        return collect($prodUrls);
    }


    public function scrapeProduct($url) {
        $crawler = $this->client->request('GET', $url);
        //var_dump($this->client->getResponse()->getContent());
        return $this->parseProductInfo($crawler);
    }


    public function parseProductInfo($crawler) {
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
    public function parsePrice($price) {
        $pricePattern = "/[0-9]+\.[0-9]{2}/";
        preg_match($pricePattern, $price, $matches);

        return !empty($matches) ? $matches[0] : false;
    }

    public function parseTitle($title) {
        return trim(html_entity_decode($title));
    }

    public function parseDescription($description) {
        return trim($description);
    }
}
