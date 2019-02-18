<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImdbMovie extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Associate with the respective link.
     */
    public function link()
    {
        return $this->morphOne(Link::class, 'linkable');
    }
}
