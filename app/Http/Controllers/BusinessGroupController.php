<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BusinessGroup;
use DB;


class BusinessGroupController extends Controller
{
    //	
	public function UploadBusinessgroup(Request $request)
    {
       // $auth_token = implode($request->only(['auth_token']));
		//$schema_name = implode($request->only(['schema_name']));
		
		$data = $request->json()->all();
		$auth_token = $data['auth_token'];
		$schema_name = $data['schema_name'];
		
		 $Auth_Token = DB::table('business_groups')
                                ->where('auth_token', $auth_token)
                                ->value('auth_token');

		
		 // Check if rety set to true && schema available
        if($auth_token == $Auth_Token) 
        {
             $uploadFound = true;
            return response()->json([
                'auth_token'     => $auth_token,
                'message'           => 'error in uploads, upload id already present',
            ]);
        }
        // Check if schema not  available only
        else
        {
            $businessGroup = new BusinessGroup;
            $businessGroup -> auth_token = $request->auth_token;
			$businessGroup -> schemaname = $request->schema_name;
            $businessGroup -> save();
         }
		 
		 //call upload details		
		$result=$this->getDetails($auth_token);
		
		//Response after execution
		return $result;
	}
	
	public function getSchema(Request $request)
    {
        $auth_token = implode($request->only(['auth_token']));  
		
        $result=$this->getDetails($auth_token);
		
		//Response after execution
		return $result;
    }
	
	public function getDetails($auth_token)
	{
		$businessGroup = BusinessGroup::Where('auth_token', $auth_token)->get();
		$response['Schema']=$businessGroup;
	    return json_encode($response);
	}
}
