<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class articleResponse 
{
    public $id1 = '';
	public $ext_article_id = '';
    public $title ='';
    public $headline = '';
    public $kicker = '';
    public $caption = '';
    public $tags = '';
    public $body = '';
    public $declaration = '';
    public $location = '';
    public $language ='';
    public $district = '';
    public $state ='';
    public $reporter_name ='';
    public $reporter_id ='';
    public $publish_status ='';
    public $ingest_status ='';
    public $ingest_id ='';
	public $created_at='';
	public $updated_at='';
	public $attachments='';
}
