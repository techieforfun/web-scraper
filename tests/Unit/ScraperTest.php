<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Helpers\Scraper;

class ScraperTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function test_it_parses_the_url()
    {
        $urls = [
            'https://www.imdb.com/title/tt0454921/',
            'https://www.imdb.com/title/tt0245844/?ref_=nv_sr_1',
            'https://google.com/search?q=natural+language+processing'
        ];
        $urlSections = [
            [
                'scheme' => 'https',
                'host' => 'www.imdb.com',
                'path' => '/title/tt0454921/',
                'query' => null,
            ],
            [
                'scheme' => 'https',
                'host' => 'www.imdb.com',
                'path' => '/title/tt0245844/',
                'query' => 'ref_=nv_sr_1'
            ],
            [
                'scheme' => 'https',
                'host' => 'google.com',
                'path' => '/search',
                'query' => 'q=natural+language+processing'
            ],
        ];
        foreach ($urls as $key => $url) {
            $parsedUrl = Scraper::parseUrl($url);

            $this->assertIsArray($parsedUrl);
            // it's a most have
            $sectionKey = 'host';
            $this->assertEquals($parsedUrl[$sectionKey], $urlSections[$key][$sectionKey]);
            foreach ($parsedUrl as $sectionKey => $sectionValue) {
                switch ($sectionKey) {
                    case 'scheme':
                    case 'path':
                    case 'query':
                        $this->assertEquals($parsedUrl[$sectionKey], $urlSections[$key][$sectionKey]);
                        break;
                }
            }
        }
    }

    public function test_it_downloads_the_resource()
    {
        $url = "https://www.imdb.com/title/tt0454921/";

        $resource = Scraper::downloadResource($url);

        $this->assertIsArray($resource);
        $this->assertArrayHasKey('httpCode', $resource);
        $this->assertArrayHasKey('data', $resource);
        $this->assertArrayHasKey('error', $resource);
        $this->assertEquals(200, $resource['httpCode']);
    }

    public function test_it_processes_the_html()
    {
        $filePath = base_path('tests/Unit/asset/movie.html');
        $this->assertFileExists($filePath);
        $content = file_get_contents($filePath, true);

        $this->assertIsString($content);
        $processedContent = Scraper::processHtml($content, 'imdb_movie_processor');
        $this->assertIsArray($processedContent);
        $keys = [
            'title',
            'title_of_movie',
            'main_picture',
            'rate',
            'summary'
        ];
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $processedContent);
        }

        $this->assertEquals('The Pursuit of Happyness (2006) - IMDb', $processedContent['title']);
        $this->assertEquals('The Pursuit of Happyness (2006)', $processedContent['title_of_movie']);
        $this->assertEquals('https://m.media-amazon.com/images/M/MV5BMTQ5NjQ0NDI3NF5BMl5BanBnXkFtZTcwNDI0MjEzMw@@._V1_UX182_CR0,0,182,268_AL_.jpg', $processedContent['main_picture']);
        $this->assertEquals('8.0', $processedContent['rate']);
        $this->assertEquals('A struggling salesman takes custody of his son as he\'s poised to begin a life-changing professional career.', $processedContent['summary']);
    }
}
