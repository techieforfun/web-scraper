<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Resources\ImdbMovie as ImdbMovieResource;
use App\Http\Resources\ImdbMovieCollection;

use App\ImdbMovie;
use App\Link;

use App\Helpers\Scraper;

class ImdbMovieController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->title = 'Imdb Movie';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->get('limit', 20);
        $pageNumber = $request->get('pageNumber', 1);
        $orderBy = $request->get('orderBy', 'title_of_movie');
        $orderDirection = $request->get('orderDirection', 'asc');

        $imdbMovies = ImdbMovie::orderBy(
            $orderBy,
            $orderDirection
        )
        ->paginate(
            $limit,
            ['*'],
            'page',
            $pageNumber
        );

        return new ImdbMovieCollection($imdbMovies);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'url' => 'required|string|max:65535'
        ]);

        $url = trim($data['url']);
        if (!Link::where('url', $url)->exists()) {
            $parsedUrl = Scraper::parseUrl($url);
            if (ends_with($parsedUrl['host'], 'imdb.com')) {
                $resource = Scraper::downloadResource($url);
                if (is_array($resource)
                    && array_key_exists('httpCode', $resource)
                    && array_key_exists('data', $resource)
                    && array_key_exists('error', $resource)
                    && $resource['httpCode'] == 200
                ) {
                    $processedContent = Scraper::processHtml($resource['data'], 'imdb_movie_processor');
                    if (!is_null($processedContent)
                        && is_array($processedContent)
                        && array_key_exists('title', $processedContent)
                        && is_string($processedContent['title'])
                        && array_key_exists('title_of_movie', $processedContent)
                        && is_string($processedContent['title_of_movie'])
                        && array_key_exists('main_picture', $processedContent)
                        && is_string($processedContent['main_picture'])
                        && array_key_exists('rate', $processedContent)
                        && is_string($processedContent['rate'])
                        && array_key_exists('summary', $processedContent)
                        && is_string($processedContent['summary'])
                    ) {
                        if (!ImdbMovie::where('title', $processedContent['title'])->exists()) {
                            $imdbMovie = ImdbMovie::create([
                                'title' => $processedContent['title'],
                                'title_of_movie' => $processedContent['title_of_movie'],
                                'main_picture' => $processedContent['main_picture'],
                                'rate' => $processedContent['rate'],
                                'summary' => $processedContent['summary']
                            ]);
                            $imdbMovie->link()->create([
                                'url' => $url
                            ]);

                            return response()->json([
                                'message' => __(
                                    ':model (id: :id) has been created successfully',
                                    [
                                        'model' => __($this->title),
                                        'id' => $imdbMovie->id
                                    ]
                                ),
                            ], 201);
                        } else {
                            $errorMessage = 'URL is a duplicate.';
                        }
                    } else {
                        $errorMessage = 'URL is not valid for an IMDB movie.';
                    }
                } else {
                    $errorMessage = 'URL is not working properly at the moment.';
                }
            } else {
                $errorMessage = 'URL is not valid for an IMDB movie.';
            }
        } else {
            $errorMessage = 'URL is a duplicate.';
        }
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                'url' => [
                    $errorMessage
                ]
            ],
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ImdbMovie  $imdbMovie
     * @return \Illuminate\Http\Response
     */
    public function show(ImdbMovie $imdbMovie)
    {
        return new ImdbMovieResource($imdbMovie);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ImdbMovie  $imdbMovie
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ImdbMovie $imdbMovie)
    {
        $data = $request->validate([
            'url' => 'required|string|max:65535'
        ]);

        $url = trim($data['url']);
        if (!Link::where('url', $url)->where('linkable_id', '!=', $imdbMovie->id)->exists()) {
            $parsedUrl = Scraper::parseUrl($url);
            if (ends_with($parsedUrl['host'], 'imdb.com')) {
                $resource = Scraper::downloadResource($url);
                if (is_array($resource)
                    && array_key_exists('httpCode', $resource)
                    && array_key_exists('data', $resource)
                    && array_key_exists('error', $resource)
                    && $resource['httpCode'] == 200
                ) {
                    $processedContent = Scraper::processHtml($resource['data'], 'imdb_movie_processor');
                    if (!is_null($processedContent)
                        && is_array($processedContent)
                        && array_key_exists('title', $processedContent)
                        && is_string($processedContent['title'])
                        && array_key_exists('title_of_movie', $processedContent)
                        && is_string($processedContent['title_of_movie'])
                        && array_key_exists('main_picture', $processedContent)
                        && is_string($processedContent['main_picture'])
                        && array_key_exists('rate', $processedContent)
                        && is_string($processedContent['rate'])
                        && array_key_exists('summary', $processedContent)
                        && is_string($processedContent['summary'])
                    ) {
                        if (!ImdbMovie::where('title', $processedContent['title'])->exists()) {
                            $imdbMovie->fill([
                                'title' => $processedContent['title'],
                                'title_of_movie' => $processedContent['title_of_movie'],
                                'main_picture' => $processedContent['main_picture'],
                                'rate' => $processedContent['rate'],
                                'summary' => $processedContent['summary']
                            ])->save();

                            $imdbMovie->link->fill([
                                'url' => $url
                            ])->save();

                            return response()->json([
                                'message' => __(
                                    ':model (id: :id) has been updated successfully',
                                    [
                                        'model' => __($this->title),
                                        'id' => $imdbMovie->id
                                    ]
                                ),
                            ], 200);
                        } else {
                            $errorMessage = 'URL is a duplicate.';
                        }
                    } else {
                        $errorMessage = 'URL is not valid for an IMDB movie.';
                    }
                } else {
                    $errorMessage = 'URL is not working properly at the moment.';
                }
            } else {
                $errorMessage = 'URL is not valid for an IMDB movie.';
            }
        } else {
            $errorMessage = 'URL is a duplicate.';
        }
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => [
                'url' => [
                    $errorMessage
                ]
            ],
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ImdbMovie  $imdbMovie
     * @return \Illuminate\Http\Response
     */
    public function destroy(ImdbMovie $imdbMovie)
    {
        try {
            $imdbMovie->link()->delete();
            $imdbMovie->delete();
            return response()->json([
                'message' => __(
                    ':model (id: :id) has been deleted successfully',
                    [
                        'model' => __($this->title),
                        'id' => $imdbMovie->id
                    ]
                ),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => __(
                    'Deleting the :model (id: :id) was unsuccessful',
                    [
                        'model' => __($this->title),
                        'id' => $imdbMovie->id
                    ]
                ) . $description,
            ], 409);
        }
    }
}
