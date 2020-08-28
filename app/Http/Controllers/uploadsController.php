<?php
namespace App\Models;
namespace App\Http\Controllers;
use DB;
use App\uploads;
use App\uploadFiles;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Aws\Exception\AwsException;
use App\uploadResponse;


class uploadsController extends Controller
{   

    /**
     * Remove ifExists
     * Executes when true flag is present
     */
    public function ifExists(Request $request)
    {
       // $existing_upload_id = implode($request->only(['ext_upload_id']));
        //$existing_upload_items = json_decode($request->input('upload_items'), TRUE);
		$input = $request->json()->all();
		$existing_upload_id = $input['ext_upload_id'];
		$upload_item = $input['upload_items'];
		$existing_upload_items =$upload_item;
		
        // Delete existing items from uploads table
        $deleteUploads = DB::table('uploads')
                                ->where('ext_upload_id', $existing_upload_id)
                                ->delete();
        
        // Delete existing items from upload_files table
        foreach($existing_upload_items["data"] as $row)
        {
            $existing_ext_upload_item_id = $row['ext_upload_item_id'];
            
            $deleteUploadItems = DB::table('upload_files')
                                    ->where('ext_upload_item_id', $existing_ext_upload_item_id)
                                    ->delete();
        }
    }

    /**
     * Get signedURL form AWS
     * Return signedURL
     */
    public function signedURL($file_name)
    {
        $s3Client = new S3Client([
            'region'    => env('AWS_DEFAULT_REGION'),
            'version'   => 'latest',
        ]);

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket'    => env('AWS_BUCKET'),
            'Key'       => $file_name,
        ]);

        $requestURL = $s3Client->createPresignedRequest($cmd, '+24 hours');

        $signedURL = (string)$requestURL->getUri();

        return $signedURL;
    }

    /**
     * Executes uploadFiles
     * Checks if retry is there
     * 
     */
    public function uploadFiles(Request $request)
    {
        /* $ext_upload_id = implode($request->only(['ext_upload_id']));
        $upload_items = json_decode($request->input('upload_items'), TRUE);
        $retry_upload = implode($request->only(['retry'])); */
		
		$input = $request->json()->all();
		$ext_upload_id = $input['ext_upload_id'];
		$upload_item = $input['upload_items'];
		$upload_items = $upload_item;
		//$upload_items = json_decode(json_encode($upload_item),TRUE);
		$retry_upload =$input['retry'];
		
        $uploadItemFound = false;
        $uploadFound = false;
        
        $extUploadID = DB::table('uploads')
                                ->where('ext_upload_id', $ext_upload_id)
                                ->value('ext_upload_id');

        // Check if rety set to true && uploads available
        if($ext_upload_id == $extUploadID && $retry_upload == "true") 
        {
            $this->ifExists($request);
            $uploadFound = false;
        }
        // Check if uploads available only
        else if ($ext_upload_id == $extUploadID)
        {
            $uploadFound = true;
            return response()->json([
                'ext_upload_id'     => $extUploadID,
                'message'           => 'error in uploads, upload id already present',
            ]);
        }

        // Check if upload_items available
        foreach($upload_items as $row)
        {
            $ext_upload_item_id = $row['ext_upload_item_id'];
            
            $extUploadItemID = DB::table('upload_files')
                                        ->where('ext_upload_item_id', $ext_upload_item_id)
                                        ->value('ext_upload_item_id');

            // If upload_items available                             
            if ($ext_upload_item_id == $extUploadItemID) 
            {
                
                $uploadItemFound = true;
                return response()->json([
                    'ext_upload_item_id' => $extUploadItemID,
                    'message'            => 'error in upload files, upload item id already present',
                ]);
            }
        }

        // Insert to uploads & uploadFiles
        if(!$uploadFound && !$uploadItemFound) {
            $Uploads = new uploads;
            $Uploads -> ext_upload_id = $request->ext_upload_id;
            $Uploads -> save();
       
        // Insert to uploadFiles
            $arruploadItems = array();

            // Looping through uploadFiles hash
            foreach($upload_items as $row) {

                $file_name = $row['file_name'];
                $uploadURL = $this->signedURL($file_name);
                $uploadFiles = new uploadFiles;
                $uploadFiles -> ext_upload_item_id  = $row['ext_upload_item_id'];
                $uploadFiles -> file_name           = $row['file_name'];
                $uploadFiles -> file_type           = $row['file_type'];
                $uploadFiles -> file_size           = $row['file_size'];
                $uploadFiles -> upload_url          = $uploadURL;
                $uploadFiles -> upload()->associate($Uploads);
                $uploadFiles -> save();
                // Create array from uploadFiles
                $arruploadItems[] = $uploadFiles;
            }
        }
		
		//call upload details		
		$result=$this->getDeatils($ext_upload_id);
		
		//Response after execution
		return $result;
    }

    /**
     * getuploads -> uploadFiles
     */
    public function getUploads(Request $request)
    {
		$input = $request->json()->all();
		$ext_upload_id = $input['ext_upload_id'];
       // $ext_upload_id = implode($request->only(['ext_upload_id']));       
        
		$result = $this->getDeatils($ext_upload_id);
		return $result;
    }
	
	
	
   public function getInsertValues($ext_upload_id)
	{
		$data = [];
		
		//$ext_upload_id = implode($request->only(['ext_upload_id']));
        
        $ID = DB::table('uploads')->where('ext_upload_id', $ext_upload_id)->value('id');
        
        $upload = uploads::find($ID)->uploadfileItems;	
		
		 $map = $upload->map(function($upload){
		$data['id'] = $upload->id;
		$data['ext_upload_item_id'] = $upload->ext_upload_item_id;
		$data['upload_id'] = $upload->upload_id;
		$data['file_name'] = $upload->file_name;
		$data['file_type'] = $upload->file_type;
		$data['file_size'] = $upload->file_size;
		$data['upload_url'] = $upload->upload_url;
		$data['created_at'] = $upload->created_at;
		$data['updated_at'] = $upload->updated_at;
		
		return $data;
		});
		return $map; 
		
	}
	
	 public function getDeatils($ext_upload_id)
	{			
		$uploads = uploads::Where('ext_upload_id', $ext_upload_id)->get();
		
		$result=$this->getInsertValues($ext_upload_id);
		
		 $U=new uploadResponse;
			$U->id1  = $uploads[0]->id;
			$U->ext_upload_id = $uploads[0]->ext_upload_id;
			$U->created_at = $uploads[0]->created_at;
			$U->updated_at = $uploads[0]->updated_at;
			$U->uploadFiles=$result;
		
		
		 $response['uploads']=$U;
	     return json_encode($response);
		
	}

}
