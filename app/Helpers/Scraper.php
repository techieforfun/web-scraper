<?php

namespace App\Helpers;

class Scraper
{
    /**
     * Get the parsed URL.
     *
     * @param string $url
     * @return mixed
     */
    public static function parseUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return parse_url($url);
        }
        return null;
    }


    /**
     * Download a specific resource.
     *
     * @param string $url
     * @param string $userAgent
     * @param array $httpHeaders
     * @return mixed
     */
    public static function downloadResource(
        $url,
        $userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/26.0',
        $httpHeaders = [
            'Accept: text/html'
        ],
        $delay = 1 // seconds
    ) {
        sleep($delay);
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            return [
                'httpCode' => $httpCode,
                'data' => $data,
                'error' => $curl
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Process the html resource.
     *
     * @param string $resource
     * @param string $processor
     * @return mixed
     */
    public static function processHtml(
        $resource,
        $processor = 'imdb_movie_processor'
    ) {
        $processedContent = [];

        switch ($processor) {
            case 'imdb_movie_processor':
                $dom = new \DOMDocument;
                @$dom->loadHtml($resource);
                $xpath = new \DOMXPath($dom);

                $queries = [
                    'title' => '/html/head/title',
                    'title_of_movie' => '//*[@id="title-overview-widget"]/div[1]/div[2]/div/div[2]/div[2]/h1',
                    'main_picture' => '//*[@id="title-overview-widget"]/div[1]/div[3]/div[1]/a/img/@src',
                    'rate' => '//*[@id="title-overview-widget"]/div[1]/div[2]/div/div[1]/div[1]/div[1]/strong/span',
                    'summary' => '//*[@id="title-overview-widget"]/div[2]/div[1]/div[1]'
                ];

                foreach ($queries as $key => $query) {
                    $set = $xpath->query($query);
                    if ($set->length > 0) {
                        $processedContent[$key] = trim($set[0]->nodeValue);
                    }
                }
                break;
        }
        return $processedContent;
    }
}
