<?php 

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Scraper;

class ScrapeSainsburysCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'sainsburys:scrape {url}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Scrape products from Sainsburys and return as a JSON array";

    /**
     * The scraping service
     *
     */
    protected $scraper;

    /**
     * Create a new instance
     *
     * @param Scraper $scraper The scraping service
     * @return void
     */
    public function __construct(Scraper $scraper) {
        parent::__construct();

        $this->scraper = $scraper;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $products = $this->scraper->scrapeProdUrlsFromListing($this->argument('url'))->map(function($url) {
            if (($product = $this->scraper->scrapeProduct($url)) !== false) {
                $product['size'] = $this->scraper->getResponseContentLength();
                return $product;
            }
        });
        
        $totalUnitPrice = $products->reduce(function($prev, $product) {
            return $prev + $product['unit_price'];
        }, 0);
        
        $result = [
            'results' => $products,
            'total' => number_format($totalUnitPrice, 2, '.', '')
        ];
        
        print json_encode($result, JSON_PRETTY_PRINT);
    }

}