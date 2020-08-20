<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class uploadFiles extends Model
{
    protected $table = 'upload_files';
    
    protected $fillable = [
        'ext_upload_item_id',
        'upload_id',
        'file_name',
        'file_type',
        'file_size',
        'upload_url',
    ];
    /**
     * uploadFiles belongs to upload
     */
    public function upload()
    {
        return $this->belongsTo('App\uploads');
    }
}
