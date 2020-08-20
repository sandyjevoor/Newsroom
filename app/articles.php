<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class articles extends Model
{
    //
    protected $table = 'articles';
    protected $fillable = [
        'ext_article_id',
        'title',
        'headline',
        'kicker',
        'caption',
        'tags',
        'body',
        'declaration',
        'location',
        'language',
        'district',
        'state',
        'reporter_name',
        'reporter_id',
        'publish_status',
        'ingest_status',
        'ingest_id'
    ];

    /**
     * 
     */
    public function mediaItems()
    {
        return $this->hasMany('App\medias', 'article_id');
    }
}
