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
	
	
	 public function update_article(Request $request)
    {      
		$input = $request->json()->all();
        $articleFound = false;
		
		// Grab our Input as individual variables so they are easier to work with
		if (isset($input['ext_article_id'])) {
				$ext_article_id    =   $input['ext_article_id'];
			}
		if (isset($input['attachments'])) {
				$attachments       =   $input['attachments'];
			}
		if (isset($input['retry'])) {
				$retry_create      =   $input['retry'];
			}
		if (isset($input['title'])) {
				$title 		       =   $input['title'];
			}
		if (isset($input['headline'])) {
				$headline          =   $input['headline'];
			}
		if (isset($input['kicker'])) {
				$kicker            =   $input['kicker'];
			}
		if (isset($input['caption'])) {
				$caption           =   $input['caption'];
			}
		if (isset($input['tags'])) {
				$tags 		       =   $input['tags'];
			}
		if (isset($input['body'])) {
				$body 			   =   $input['body'];
			}
		if (isset($input['declaration'])) {
				$declaration       =   $input['declaration'];
			}
		if (isset($input['location'])) {
				$location          =   $input['location'];
			}
		if (isset($input['language'])) {
				$language 		   =   $input['language'];
			}
		if (isset($input['district'])) {
				$district 		   =   $input['district'];
			}
		if (isset($input['state'])) {
				$state 			   =   $input['state'];
			}
		if (isset($input['reporter_name'])) {
				$reporter_name 	   =   $input['reporter_name'];
			}
		if (isset($input['reporter_id'])) {
				$reporter_id 	   =   $input['reporter_id'];
			}
		if (isset($input['publish_status'])) {
				$publish_status    =   $input['publish_status'];
			}
		if (isset($input['ingest_status'])) {
				$ingest_status     =   $input['ingest_status'];
			}
		if (isset($input['ingest_id'])) {
				$ingest_id 		   =   $input['ingest_id'];
			}
			
								 		
		$article_ID = DB::table('articles')
							->where('ext_article_id', $ext_article_id)
                            ->value('id');
		if($article_ID=="")
		{
			return response()->json([
                'ext_article_id'     => $ext_article_id,
                'message'           => 'error in update article, article id not present',
            ]);
		}
		else
		{
		
		// Use Eloquent to grab the gift record that we want to update,
		// referenced by the ID passed to the REST endpoint
		$createArticle = articles::find($article_ID);
		
		if(isset($title))
		{
		   $createArticle->title 			 = $title 		? $title : $createArticle->title;
		}
		if(isset($headline))
		{
		   $createArticle->headline		 = $headline 	? $headline : $createArticle->headline;
		}
		if(isset($kicker))
		{
		   $createArticle->kicker 			 = $kicker 		? $kicker : $createArticle->kicker;
		}      
		if(isset($caption))
		{
		   $createArticle->caption			 = $caption	    ? $caption : $createArticle->caption;
		}      
		if(isset($tags))
		{
		   $createArticle->tags			 = $tags		? $tags : $createArticle->tags;
		}  		
		if(isset($body))
		{
		   $createArticle->body			 = $body		? $body : $createArticle->body;   
		}  
		if(isset($declaration))
		{
		   $createArticle->declaration		 = $declaration ? $declaration : $createArticle->declaration;    
		}     
		if(isset($location))
		{
		   $createArticle->location		 = $location	? $location : $createArticle->location;    
		}     
		if(isset($language))
		{
		   $createArticle->language		 = $language 	? $language : $createArticle->language;  
		} 		
		if(isset($district))
		{
		   $createArticle->district		 = $district 		? $district : $createArticle->district;
		} 
		if(isset($state))
		{
		   $createArticle->state			 = $state 			 ? $state : $createArticle->state; 
		}         
		if(isset($reporter_name))
		{
		   $createArticle->reporter_name	 = $reporter_name 	 ? $reporter_name : $createArticle->reporter_name;		
		}
		if(isset($reporter_id))
		{
		   $createArticle->reporter_id		 = $reporter_id 	 ? $reporter_id : $createArticle->reporter_id;	
		}
		if(isset($publish_status))
		{
		   $createArticle->publish_status	 = $publish_status	 ? $publish_status : $createArticle->publish_status; 	
		}
		if(isset($ingest_status))
		{
		   $createArticle->ingest_status	 = $ingest_status	 ? $ingest_status : $createArticle->ingest_status;  	
		}    
		if(isset($ingest_id))
		{
		   $createArticle->ingest_id		 = $ingest_id 		 ? $ingest_id : $createArticle->ingest_id;
		}       
		

		$createArticle->save();      

		
		
		
		 //Check wheater media files are present
    			
			$items = array();
			$count = 0;
			foreach ($attachments as $row) {           
			$ext_upload_item_id = $row['ext_upload_item_id'];
            
			// Get row from medias  					
			$ext_upload_ID = DB::table('medias')	
							 ->where('ext_upload_item_id', $ext_upload_item_id)			
							 ->where('article_id', $article_ID)							
                             ->value('ext_upload_item_id');
			
				if($ext_upload_ID == $ext_upload_item_id)
				{
					$ext_upload_item_id = $row['ext_upload_item_id'];
					$getUploadedItem = $this->getValues($ext_upload_item_id);
					
					$medias_ID = DB::table('medias')
							 ->where('ext_upload_item_id', $ext_upload_item_id)			
							 ->where('article_id', $article_ID)	
                            ->value('id');
		
					// Use Eloquent to grab the gift record that we want to update,
					// referenced by the ID passed to the REST endpoint
						$file_name            = $getUploadedItem->file_name;
						$file_type            = $getUploadedItem->file_type;
						$file_size            = $getUploadedItem->file_size;
						$upload_url           = $getUploadedItem->upload_url;
					
					$updateMedias = medias::find($medias_ID);
					$updateMedias->file_name 			 = $file_name 		? $file_name : $updateMedias->file_name;
					$updateMedias->file_type			 = $file_type 	    ? $file_type : $updateMedias->file_type;
					$updateMedias->file_size 			 = $file_size 		? $file_size : $updateMedias->file_size;
					$updateMedias->upload_url			 = $upload_url	    ? $upload_url : $updateMedias->upload_url;
					
					$updateMedias -> save();
					
				}
				else
				{				
										
					$medias = new medias;

						// Get row from uploadFiles
						$ext_upload_item_id = $row['ext_upload_item_id'];
						$getUploadedItem = $this->getValues($ext_upload_item_id);
            
						//$medias     -> article()->associate($createArticle);
						$medias     -> ext_upload_item_id   = $getUploadedItem->ext_upload_item_id;
						$medias		-> article_id			= $article_ID;
						$medias     -> file_name            = $getUploadedItem->file_name;
						$medias     -> file_type            = $getUploadedItem->file_type;
						$medias     -> file_size            = $getUploadedItem->file_size;
						$medias     -> upload_url           = $getUploadedItem->upload_url;

						$medias -> save();
				
				}
		
		
			}
		
		$result=$this->getStoredata($ext_article_id);
		return $result;
        
    }
	}
}
