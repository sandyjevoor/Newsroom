<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class uploads extends Model
{
    protected $table = 'uploads';
    protected $fillable = ['ext_upload_id'];
	 protected $appends = ['uploadFiles'];
    /**
     * uploads has many uploadFiles
     */
    public function uploadfileItems()
    {
        return $this->hasMany('App\uploadFiles', 'upload_id');
    }
	
  
}