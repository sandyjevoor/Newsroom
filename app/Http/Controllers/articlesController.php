<?php

namespace App\Models;
namespace App\Http\Controllers;

use App\uploads;
use App\uploadFiles;
use App\articles;
use App\medias;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\articleResponse;

class articlesController extends Controller
{
    /**
     * Remove ifExists
     * Executes when true flag is present
     */
    public function ifExists(Request $request)
    {     
		
		$input = $request->json()->all();
		$existing_article_id = $input['ext_article_id'];
		$media_items = $input['attachments'];
		
        
        // Delete existing article from articles table
        $deleteArticle = DB::table('articles')
                                ->where('ext_article_id', $existing_article_id)
                                ->delete();

        // Delete existing media items from medias table
        foreach($media_items as $row)
            {
            $existing_media_item_id = $row['ext_upload_item_id'];
            
            $deleteMediaItems = DB::table('medias')
                                    ->where('ext_upload_item_id', $existing_media_item_id)
                                    ->delete();
        }
    }
    /**
     * Get values from table
     */
    public function getValues($ext_upload_item_id)
    {
        $getRow = DB::table('upload_files')
                            ->where('ext_upload_item_id', $ext_upload_item_id)
                            ->first();
        return $getRow;
    }

    /*
     * Executes createArticles
     * Checks if retry there
     */
	 
    public function createArticles(Request $request)
    {      
		$input = $request->json()->all();
		$ext_article_id = $input['ext_article_id'];
		$attachments = $input['attachments'];
		$retry_create = $input['retry'];
        $articleFound = false;

        $articleID = DB::table('articles')
                            ->where('ext_article_id', $ext_article_id)
                            ->value('ext_article_id');

        // Check if rety set to true && articles available
        if($ext_article_id == $articleID && $retry_create == "true")
        {
            $this->ifExists($request);
            $articleFound = false;
        }
        else if ($ext_article_id == $articleID)
        {
            $articleFound = true;
            return response()->json([
                'ext_article_id'     => $articleID,
                'message'           => 'error in create article, article id already present',
            ]);
        }
        
        // Insert to article
        if(!$articleFound)
        {
            $createArticle = new articles;
            $createArticle      -> ext_article_id       = $request->ext_article_id;
            $createArticle      -> title                = $request->title;
            $createArticle      -> headline             = $request->headline;
            $createArticle      -> kicker               = $request->kicker;
            $createArticle      -> caption              = $request->caption;
            $createArticle      -> tags                 = $request->tags;
            $createArticle      -> body                 = $request->body;
            $createArticle      -> declaration          = $request->declaration;
            $createArticle      -> location             = $request->location;
            $createArticle      -> language             = $request->language;
            $createArticle      -> district             = $request->district;
            $createArticle      -> state                = $request->state;
            $createArticle      -> reporter_name        = $request->reporter_name;
            $createArticle      -> reporter_id          = $request->reporter_id;
            $createArticle      -> publish_status       = $request->publish_status;
            $createArticle      -> ingest_status        = $request->ingest_status;
            $createArticle      -> ingest_id            = $request->ingest_id;
            $createArticle      -> save();

            //insert to medias
            $arrmediaItems = array();

            foreach ($attachments as $row) {
            $medias = new medias;

            // Get row from uploadFiles
            $ext_upload_item_id = $row['ext_upload_item_id'];
            $getUploadedItem = $this->getValues($ext_upload_item_id);
            
            $medias     -> article()->associate($createArticle);
            $medias     -> ext_upload_item_id   = $getUploadedItem->ext_upload_item_id;
            $medias     -> file_name            = $getUploadedItem->file_name;
            $medias     -> file_type            = $getUploadedItem->file_type;
            $medias     -> file_size            = $getUploadedItem->file_size;
            $medias     -> upload_url           = $getUploadedItem->upload_url;

            $medias -> save();

            // Create array from inserted medias
            $arrmediaItems[] = $medias;
            }
        }

		$result=$this->getStoredata($ext_article_id);
		return $result;
        
    }

    /**
     * Executes getArticle
     * Returns specific article from database
     */
    public function getArticle(Request $request)
    {
        $input = $request->json()->all();
		$ext_article_id = $input['ext_article_id'];
      
		$result=$this->getStoredata($ext_article_id);	
		return $result;
    }
	
	public function getStoredata($ext_article_id)
	{
		$article = DB::table('articles')->select('id','ext_article_id','title','headline','kicker','caption','tags','body','declaration','location','language','district','state','reporter_name','reporter_id','publish_status','ingest_status','ingest_id','created_at','updated_at')
                     ->where('ext_article_id', $ext_article_id)                    
                     ->get();
		foreach ($article as $user) {
			$ID = $user->id;			
			}
			
		$A =new articleResponse;	
		$A->id1  = $article[0]->id;
		$A->ext_article_id  = $article[0]->ext_article_id;			
		$A->title  = $article[0]->title;
		$A->headline  = $article[0]->headline;
		$A->kicker  = $article[0]->kicker;
		$A->caption  = $article[0]->caption;			
		$A->tags  = $article[0]->tags;
		$A->body  = $article[0]->body;
		$A->declaration  = $article[0]->declaration;
		$A->location  = $article[0]->location;			
		$A->language  = $article[0]->language;
		$A->district  = $article[0]->district;
		$A->state  = $article[0]->state;
		$A->reporter_name  = $article[0]->reporter_name;			
		$A->reporter_id  = $article[0]->reporter_id;
		$A->publish_status  = $article[0]->publish_status;
		$A->ingest_status  = $article[0]->ingest_status;
		$A->ingest_id  = $article[0]->ingest_id;			
		$A->created_at  = $article[0]->created_at;
		$A->updated_at  = $article[0]->updated_at;		
				
		$result=$this->getmediaDeatils($ID);
		$A->attachments  = $result;
		
		$response['article']=$A;
	    return json_encode($response);
		
	}	
	
	public function getmediaDeatils($ID)
	{
		$data = [];
		
		$medias = medias::Where('article_id', $ID)->get();
		
		$map = $medias->map(function($medias){
		$data['id'] = $medias->id;
		$data['ext_upload_item_id'] = $medias->ext_upload_item_id;
		$data['article_id'] = $medias->article_id;
		$data['file_name'] = $medias->file_name;
		$data['file_type'] = $medias->file_type;
		$data['file_size'] = $medias->file_size;
		$data['upload_url'] = $medias->upload_url;		
		$data['created_at'] = $medias->created_at;
		$data['updated_at'] = $medias->updated_at;
		$data['file_size_mb'] = $medias->file_size_mb;		
		return $data;
		});
		return $map;
	}
}
