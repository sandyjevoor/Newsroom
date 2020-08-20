<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class medias extends Model
{
    protected $table = 'medias';

    protected $fillable = [
        'ext_upload_item_id',
        'article_id',
        'file_name',
        'file_type',
        'file_size',
        'upload_url',
    ];

    /**
     * medias belongs to articles
     */
    public function article()
    {
        return $this->belongsTo('App\articles');
    }

    /**
     *  list of accessors can be defined here
     *  this will be executed after retriving data from database
     */
    public function getFileSizeMbAttribute($value)
    {
        return $this->file_size / 1024;
    }
    /**
     * any custom accessors need to be appended to the model 
     */
    protected $appends = ['file_size_mb'];
}
