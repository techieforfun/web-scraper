<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Associate with the respective linkable model.
     */
    public function linkable()
    {
        return $this->morphTo();
    }
}
