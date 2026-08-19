<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Models\VisitorPass;
use Illuminate\Support\Facades\Storage;
use Mail; 

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function upload($file, $module, $moduleId)
    {
      if (!empty($file)) {
          $doc_type     = $file->getClientMimeType();
          $type         = explode('/', $doc_type);
          $doc_name     = $file->getClientOriginalName();
          //$new_doc_name = date('YmdHis') . '.' . $file->getClientOriginalExtension();
          $new_doc_path = env('APP_ENV') . '/public/' . $module . '/' . $moduleId . '/';
          $storage      = Storage::disk('local'); 
          // ** For S3 Driver usage
          $metadata     = [
                'Metadata' => [
                    'filename'     => $doc_name,
                    'content-type' => $doc_type
                ]
            ];
          //$response    = $storage->put($doc_path, file_get_contents($file));
            $response = $storage->putFileAs(
                env('APP_ENV') . '/public/' . $module . '/' . $moduleId, $file, $doc_name
            );

          $upload_path = null;
          if ($response) {
              $upload_path = $storage->url($response);
              $mime_type   = $doc_type; // getting file mimetype
              $size        = $file->getSize(); // getting file size
              return asset($upload_path);
          }
      }
  }

  public function sendEmailForUser($user){
    if(isset($user)){
        if($user->role == "visitor"){
          $visitor_pass = VisitorPass::find($user->visitor_pass_id);
          $data = array('name'=>$user->name,'static_qr'=>$user->card_id,'from_date'=>$visitor_pass->from_date,'to_date'=>$visitor_pass->to_date,'from_time'=>$visitor_pass->from_time,'to_time'=>$visitor_pass->to_time);
		      Mail::send(['text'=>'email.visitor_mail'], $data, function($message) use($user){
            $message->to($user->email, $user->name)->subject('QR Details');
          });
        } else if($user->role != 'employee'){
          $data = array('name'=>$user->name,'email'=>$user->name);
		      Mail::send(['text'=>'email.loginDetails_mail'], $data, function($message) use($user){
            $message->to($user->email, $user->name)->subject('Login Details');
          });
        }
    }
  }
}
