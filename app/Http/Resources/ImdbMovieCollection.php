<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ImdbMovieCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->transform(function ($item) {
                return [
                    'id' => $item->id,
                    'url' => $item->link->url,
                    'title' => $item->title,
                    'title_of_movie' => $item->title_of_movie,
                    'main_picture' => $item->main_picture,
                    'rate' => $item->rate,
                    'summary' => $item->summary
                ];
            })
        ];
    }
}
