<?php

namespace App\Http\Services;


use App\Http\Repositories\AdminSettingRepository;
use App\Model\AdminSetting;
use App\Model\Announcement;
use App\Model\CoinPair;
use App\Model\CustomPage;
use App\Model\LandingBanner;
use App\Model\LandingFeature;
use App\Model\SocialMedia;


class LandingService
{
    public $logger;
    public function __construct()
    {
        $this->logger = new Logger();
    }

    /*
     * save or update landing feature
     *
     */
    public function saveLandingFeature($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $data = [
                'feature_title'=> $request->feature_title,
                'description'=> $request->description,
                'status'=> $request->status
            ];
            $old_img = '';
            if (!empty($request->edit_id)) {
                $item = LandingFeature::where(['id'=>$request->edit_id])->first();
                if(isset($item) && (!empty($item->feature_icon))) {
                    $old_img = $item->feature_icon;
                }
            }
            if (!empty($request->feature_icon)) {
                $icon = uploadFile($request->feature_icon,IMG_PATH,$old_img);
                if ($icon != false) {
                    $data['feature_icon'] = $icon;
                }
            }
            if(!empty($request->edit_id)) {
                LandingFeature::where(['id'=>$request->edit_id])->update($data);
                $response = ['success' => true, 'message' => __('Landing feature updated successfully!')];
            } else {
                LandingFeature::create($data);
                $response = ['success' => true, 'message' => __('Landing feature created successfully!')];
            }
        } catch (\Exception $e) {
            $this->logger->log('saveLandingFeature', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    /*
     *
     * create or update social media
     */
    public function saveLandingSocialMedia($request)
    {
        $response = ['success' => false, 'message' => __('Something went wrong')];
        try {
            $data = [
                'media_title'=> $request->media_title,
                'media_link'=> $request->media_link,
                'status'=> $request->status
            ];
            $old_img = '';
            if (!empty($request->edit_id)) {
                $item = SocialMedia::where(['id'=>$request->edit_id])->first();
                if(isset($item) && (!empty($item->media_icon))) {
                    $old_img = $item->media_icon;
                }
            }
            if (!empty($request->media_icon)) {
                $icon = uploadFile($request->media_icon,IMG_PATH,$old_img);
                if ($icon != false) {
                    $data['media_icon'] = $icon;
                }
            }
            if(!empty($request->edit_id)) {
                SocialMedia::where(['id'=>$request->edit_id])->update($data);
                $response = ['success' => true, 'message' => __('Social media updated successfully!')];
            } else {
                SocialMedia::create($data);
                $response = ['success' => true, 'message' => __('Social media created successfully!')];
            }
        } catch (\Exception $e) {
            $this->logger->log('saveLandingSocialMedia', $e->getMessage());
            $response = ['success' => false, 'message' => __('Something went wrong')];
        }
        return $response;
    }

    public function customPageSlugCheck($post_data){
        $text_array = explode(' ', $post_data['title']);
        $slug = '';
        foreach ($text_array as $i => $split_text) {
            if(!empty($slug)){
                $slug = $slug .'-'. strtolower(trim($split_text));
            }else{
                $slug = strtolower(trim($split_text));
            }
        }
        if(isset($post_data['id'])){
            $res = CustomPage::where('id','<>',$post_data['id'])->where('key', 'like', '%'.$slug.'%')->get()->count();
        }else{
            $res = CustomPage::where('key', 'like', '%'.$slug.'%')->get()->count();
        }
        if($res){
            $response['slug'] = $slug.'-'.$res;
        }else{
            $response['slug'] = $slug;
        }
        return response()->json($response);
    }

    public function checkKeyCustom($key,$id = null){
        if(isset($id)){
            $res = CustomPage::where('id','<>',$id)->where('key', 'like', '%'.$key.'%')->get()->count();
        }else{
            $res = CustomPage::where('key', 'like', '%'.$key.'%')->get()->count();
        }
        if($res){
            return false;
        }else{
            return true;
        }
    }

    //
    public function adminLandingApiLinkSave($request)
    {
        try {
            AdminSetting::updateOrCreate(['slug' => 'apple_store_link'],['value' => $request['apple_store_link']]);
            AdminSetting::updateOrCreate(['slug' => 'android_store_link'],['value' => $request['android_store_link']]);
            AdminSetting::updateOrCreate(['slug' => 'google_store_link'],['value' => $request['google_store_link']]);
            AdminSetting::updateOrCreate(['slug' => 'macos_store_link'],['value' => $request['macos_store_link']]);
            AdminSetting::updateOrCreate(['slug' => 'windows_store_link'],['value' => $request['windows_store_link']]);
            AdminSetting::updateOrCreate(['slug' => 'linux_store_link'],['value' => $request['linux_store_link']]);
            AdminSetting::updateOrCreate(['slug' => 'api_link'],['value' => $request['api_link']]);

            $response = responseData(true,__('Settings updated successfully'));
        } catch (\Exception $e) {
            storeException('adminLandingApiLinkSave', $e->getMessage());
            $response = responseData(false,__('Something went wrong'));
        }
        return $response;
    }
}
