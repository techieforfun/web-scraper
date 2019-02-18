<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImdbMovie extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'url' => $this->link->url,
            'title' => $this->title,
            'title_of_movie' => $this->title_of_movie,
            'main_picture' => $this->main_picture,
            'rate' => $this->rate,
            'summary' => $this->summary
        ];
    }
}
