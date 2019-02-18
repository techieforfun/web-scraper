<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\ImdbMovie;
use App\Link;

class ImdbMovieTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    // index
    public function test_it_lists_imdb_movies()
    {
        $num = mt_rand(100, 200);

        $imdbMovies = factory(ImdbMovie::class, $num)->create();
        foreach ($imdbMovies as $key => $movie) {
            $movie->link()->create([
                'url' => $this->faker->url() . $key
            ]);
        }

        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('GET', '/api/imdb-movie', [
            'limit' => 1
        ]);

        $response->assertStatus(200);
        $jsonResponse = $response->decodeResponseJson();
        $this->assertArrayHasKey('data', $jsonResponse);
        $this->assertArrayHasKey('links', $jsonResponse);
        $this->assertArrayHasKey('meta', $jsonResponse);
        $this->assertEquals($num, $jsonResponse['meta']['total']);
        $this->assertEquals(1, $jsonResponse['meta']['per_page']);
        $this->assertCount(1, $jsonResponse['data']);

        $dataKeys = [
            'id',
            'url',
            'title',
            'title_of_movie',
            'main_picture',
            'rate',
            'summary'
        ];
        $this->assertCount(count($dataKeys), $jsonResponse['data'][0]);
        foreach ($dataKeys as $key) {
            $this->assertArrayHasKey($key, $jsonResponse['data'][0]);
        }
    }

    // store
    public function test_it_stores_valid_imdb_movie()
    {
        $url = 'https://www.imdb.com/title/tt0454921/';

        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('POST', '/api/imdb-movie', [
            'url' => $url
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas((new Link)->getTable(), [
            'linkable_type' => 'App\\ImdbMovie',
            'url' => $url
        ]);
        $link = Link::where('url', $url)->orderBy('id', 'desc')->limit(1)->first();
        $this->assertDatabaseHas((new ImdbMovie)->getTable(), [
            'id' => $link->linkable_id
        ]);
        $imdbMovie = ImdbMovie::find($link->linkable_id);

        $sampleImdbMovies = factory(ImdbMovie::class, mt_rand(20, 50))->create();
        foreach ($sampleImdbMovies as $key => $movie) {
            $movie->link()->create([
                'url' => $this->faker->url() . $key
            ]);
        }

        // uniqueness
        $this->assertEquals(
            Link::where(
                'url',
                $url
            )
            ->count(),
            1
        );
        $this->assertEquals(
            ImdbMovie::where(
                'title',
                $imdbMovie->title
            )
            ->count(),
            1
        );
    }

    public function test_it_throws_an_exception_on_mass_assignment()
    {
        $inputs = [
            'id' => mt_rand(100, 1000)
        ];

        $imdbMovie = factory(ImdbMovie::class)->make()->toArray();
        foreach ($inputs as $inputName => $inputValue) {
            $imdbMovie[$inputName] = $inputValue;
            $createdImdbMovie = ImdbMovie::create($imdbMovie);
            $this->assertNotEquals($imdbMovie[$inputName], $createdImdbMovie[$inputName]);
        }
    }

    public function test_it_validates_inputs_to_store_new_imdb_movie()
    {
        $inputs = [
            'url' => [
                // required
                '',
                // type
                ['some'],
                // max length
                str_random(65536),
                // unique
                call_user_func(
                    function () {
                        $url = 'https://www.imdb.com/title/tt0454921/';
                        factory(ImdbMovie::class)->create()->link()->create(
                            [
                                'url' => $url
                            ]
                        );
                        return $url;
                    }
                )
            ]
        ];

        foreach ($inputs as $inputName => $inputValues) {
            foreach ($inputValues as $inputValue) {
                $response = $this->withHeaders([
                    'Accept' => 'application/json'
                ])->json('POST', '/api/imdb-movie', [
                    $inputName => $inputValue
                ]);
                $response->assertStatus(422);
                $jsonResponse = $response->decodeResponseJson();
                $this->assertArrayHasKey('errors', $jsonResponse);
                $this->assertArrayHasKey($inputName, $jsonResponse['errors']);
            }
        }

        // general case
        $desiredInputs = [];
        foreach ($inputs as $inputName => $inputValues) {
            foreach ($inputValues as $inputValue) {
                if (array_key_exists($inputName, $desiredInputs)) {
                    break;
                }
                $desiredInputs[$inputName] = $inputValue;
            }
        }
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('POST', '/api/imdb-movie', $desiredInputs);
        $response->assertStatus(422);
        $jsonResponse = $response->decodeResponseJson();
        $this->assertArrayHasKey('errors', $jsonResponse);
        $this->assertCount(count($inputs), $jsonResponse['errors']);
        foreach ($inputs as $inputName => $inputValues) {
            $this->assertArrayHasKey($inputName, $jsonResponse['errors']);
        }
    }

    // show
    public function test_it_shows_imdb_movie()
    {
        $imdbMovie = factory(ImdbMovie::class)->create();
        $imdbMovie->link()->create([
            'url' => $this->faker->url()
        ]);
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('GET', '/api/imdb-movie/' . $imdbMovie->id);

        $response->assertStatus(200);
        $jsonResponse = $response->decodeResponseJson();
        $this->assertCount(1, $jsonResponse);
        $this->assertArrayHasKey('data', $jsonResponse);

        $responseData = [
            'id',
            'url',
            'title',
            'title_of_movie',
            'main_picture',
            'rate',
            'summary'
        ];
        $this->assertCount(count($responseData), $jsonResponse['data']);
        foreach ($responseData as $key) {
            $this->assertArrayHasKey($key, $jsonResponse['data']);
        }
        $this->assertEquals($imdbMovie->id, $jsonResponse['data']['id']);
    }

    // update
    public function test_it_updates_the_imdb_movie()
    {
        $affectedImdbMovie = factory(ImdbMovie::class)->create();
        $affectedImdbMovie->link()->create([
            'url' => 'https://www.imdb.com/title/tt0454921/'
        ]);

        $newUrl = 'https://www.imdb.com/title/tt0298203/';

        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('PATCH', '/api/imdb-movie/' . $affectedImdbMovie->id, [
            'url' => $newUrl
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas((new Link)->getTable(), [
            'linkable_type' => 'App\\ImdbMovie',
            'url' => $newUrl
        ]);
        $link = Link::where('url', $newUrl)->orderBy('id', 'desc')->limit(1)->first();
        $this->assertDatabaseHas((new ImdbMovie)->getTable(), [
            'id' => $link->linkable_id
        ]);
        $imdbMovie = ImdbMovie::find($link->linkable_id);

        $sampleImdbMovies = factory(ImdbMovie::class, mt_rand(20, 50))->create();
        foreach ($sampleImdbMovies as $key => $movie) {
            $movie->link()->create([
                'url' => $this->faker->url() . $key
            ]);
        }

        // uniqueness
        $this->assertEquals(
            Link::where(
                'url',
                $newUrl
            )
            ->count(),
            1
        );
        $this->assertEquals(
            ImdbMovie::where(
                'title',
                $imdbMovie->title
            )
            ->count(),
            1
        );
    }

    public function test_it_validates_inputs_to_update_the_imdb_movie()
    {
        $affectedImdbMovie = factory(ImdbMovie::class)->create();
        $affectedImdbMovie->link()->create([
            'url' => 'https://www.imdb.com/title/tt0454921/'
        ]);

        $inputs = [
            'url' => [
                // required
                '',
                // type
                ['some'],
                // max length
                str_random(65536),
                // unique
                call_user_func(
                    function () {
                        $url = 'https://www.imdb.com/title/tt0298203/';
                        factory(ImdbMovie::class)->create()->link()->create(
                            [
                                'url' => $url
                            ]
                        );
                        return $url;
                    }
                )
            ]
        ];

        foreach ($inputs as $inputName => $inputValues) {
            foreach ($inputValues as $inputValue) {
                $response = $this->withHeaders([
                    'Accept' => 'application/json'
                ])->json('PATCH', '/api/imdb-movie/' . $affectedImdbMovie->id, [
                    $inputName => $inputValue
                ]);
                $response->assertStatus(422);
                $jsonResponse = $response->decodeResponseJson();
                $this->assertArrayHasKey('errors', $jsonResponse);
                $this->assertArrayHasKey($inputName, $jsonResponse['errors']);

                $this->assertDatabaseMissing($affectedImdbMovie->link->getTable(), [
                    'id' => $affectedImdbMovie->link->id,
                    $inputName => $inputValue
                ]);
            }
        }

        // general case
        $desiredInputs = [];
        foreach ($inputs as $inputName => $inputValues) {
            foreach ($inputValues as $inputValue) {
                if (array_key_exists($inputName, $desiredInputs)) {
                    break;
                }
                $desiredInputs[$inputName] = $inputValue;
            }
        }
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('PATCH', '/api/imdb-movie/' . $affectedImdbMovie->id, $desiredInputs);
        $response->assertStatus(422);
        $jsonResponse = $response->decodeResponseJson();
        $this->assertArrayHasKey('errors', $jsonResponse);
        $this->assertCount(count($inputs), $jsonResponse['errors']);
        foreach ($inputs as $inputName => $inputValues) {
            $this->assertArrayHasKey($inputName, $jsonResponse['errors']);
        }
        $this->assertDatabaseMissing($affectedImdbMovie->link->getTable(), array_merge(
            [
                'id' => $affectedImdbMovie->link->id
            ],
            $desiredInputs
        ));
    }

    // destroy
    public function test_it_deletes_the_imdb_movie()
    {
        $affectedImdbMovie = factory(ImdbMovie::class)->create();
        $affectedImdbMovie->link()->create(
            [
                'url' => 'https://www.imdb.com/title/tt0454921/'
            ]
        );
        $link = $affectedImdbMovie->link;
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->json('DELETE', '/api/imdb-movie/' . $affectedImdbMovie->id);

        $response->assertStatus(200);
        $this->assertDatabaseMissing($affectedImdbMovie->getTable(), [
            'id' => $affectedImdbMovie->id
        ]);
        $this->assertDatabaseMissing((new Link)->getTable(), [
            'id' => $link->id
        ]);
    }

    // relation
    public function test_it_checks_if_methods_of_relations_exist()
    {
        $imdbMovie = factory(ImdbMovie::class)->make();
        $relationMethods = [
            'link',
        ];
        foreach ($relationMethods as $method) {
            $this->assertTrue(method_exists($imdbMovie, $method));
        }
    }
}
