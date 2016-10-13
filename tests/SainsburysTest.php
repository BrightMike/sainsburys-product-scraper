<?php

use App\Console\Commands\ScrapeSainsburysCommand;
use Symfony\Component\DomCrawler\Crawler;

class SainsburysTest extends TestCase {

    /**
    * Test that a price can be correctly parsed from the text on the website
    *
    * @return void
    */
    public function testParsePrice() {
        $class = new ReflectionClass(App\Scraper::class);
        $method = $class->getMethod('parsePrice');
        $method->setAccessible(true);
        
        $scraper = new App\Scraper();
        
        $this->assertEquals(1.80, $method->invokeArgs($scraper, array("£1.80/unit")));
    }

    /**
     * Test that all details can be parsed from the raw html of a product
     *
     **/
    public function testParseProductInfo() {
        $class = new ReflectionClass(App\Scraper::class);
        $method = $class->getMethod('parseProductInfo');
        $method->setAccessible(true);

        $scraper = new App\Scraper();
        $crawler = new Crawler('<div class="productSummary">
            <div class="productTitleDescriptionContainer"><h1>Sainsbury\'s Avocado, Ripe &amp; Ready x2</h1></div>
            <div class="pricing"><div class="pricePerUnit">£1.80/unit</div></div></div>
            <div class="section" id="information"><h3 class="productDataItemHeader">Description</h3><div class="productText"><p>Avocados</p></div></div>');
        
        $expected = [
            "title" => "Sainsbury's Avocado, Ripe & Ready x2",
            "unit_price" => '1.80',
            "description" => "Avocados"
        ];

        $this->assertEquals($expected, $method->invokeArgs($scraper, array($crawler)));
    }
}