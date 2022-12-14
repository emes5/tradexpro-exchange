<?php

use App\Http\Repositories\BuyOrderRepository;
use App\Http\Repositories\CoinPairRepository;
use App\Http\Repositories\DashboardRepository;
use App\Http\Repositories\SellOrderRepository;
use App\Http\Repositories\UserWalletRepository;
use App\Http\Services\BitCoinApiService;
use App\Http\Services\BitgoWalletService;
use App\Http\Services\CoinPaymentsAPI;
use App\Http\Services\CoinService;
use App\Http\Services\DashboardService;
use App\Http\Services\ERC20TokenApi;
use App\Http\Services\Logger;
use App\Model\ActivityLog;
use App\Model\AdminSetting;
use App\Model\Buy;
use App\Model\Coin;
use App\Model\CoinPair;
use App\Model\CurrencyList;
use App\Model\DepositeTransaction;
use App\Model\MembershipBonusDistributionHistory;
use App\Model\MembershipClub;
use App\Model\MembershipPlan;
use App\Model\Sell;
use App\Model\Transaction;
use App\Model\UserWallet;
use App\Model\VerificationDetails;
use App\Model\Wallet;
use App\Model\WalletAddressHistory;
use App\Model\WithdrawHistory;
use App\User;
use Carbon\Carbon;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Jenssegers\Agent\Agent;
use Pusher\Pusher;
use Ramsey\Uuid\Uuid;
use App\Model\IcoPhase;
use App\Model\CountryList;


/**
 * @param $role_task
 * @param $my_role
 * @return int
 */
function checkRolePermission($role_task, $my_role)
{

    $role = Role::find($my_role);

    if (!empty($role->task)) {

        if (!empty($role->task)) {
            $tasks = array_filter(explode('|', $role->task));
        }

        if (isset($tasks)) {
            if ((in_array($role_task, $tasks) && array_key_exists($role_task, actions())) || (Auth::user()->user_type == USER_ROLE_SUPER_ADMIN)) {
                return 1;
            } else {
                return 0;
            }
        }
    }
    return 0;
}

function previousMonthName($m){
    $months = [];
    for ($i=$m; $i >= 0; $i--) {
        array_push($months, date('F', strtotime('-'.$i.' Month')));
    }

    return array_reverse($months);
}
function previousYearMonthName(){

    $months = [];
    for ($i=0; $i <12; $i++) {

        array_push($months, Carbon::now()->startOfYear()->addMonth($i)->format('F'));
    }

    return $months;
}

function previousDayName(){
    $days = array();
    for ($i = 1; $i < 8; $i++) {
        array_push($days,Carbon::now()->startOfWeek()->subDays($i)->format('l'));
    }

    return array_reverse($days);
}
function previousMonthDateName(){
    $days = array();
    for ($i = 0; $i < 30; $i++) {
        array_push($days,Carbon::now()->startOfMonth()->addDay($i)->format('d-M'));
    }

    return $days;
}


/**
 * @param null $array
 * @return array|bool
 */
function allsetting($array = null)
{
    if (!isset($array[0])) {
        $allsettings = AdminSetting::get();
        if ($allsettings) {
            $output = [];
            foreach ($allsettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return false;
    } elseif (is_array($array)) {
        $allsettings = AdminSetting::whereIn('slug', $array)->get();
        if ($allsettings) {
            $output = [];
            foreach ($allsettings as $setting) {
                $output[$setting->slug] = $setting->value;
            }
            return $output;
        }
        return false;
    } else {
        $allsettings = AdminSetting::where(['slug' => $array])->first();
        if ($allsettings) {
            $output = $allsettings->value;
            return $output;
        }
        return false;
    }
}

/**
 * @param null $input
 * @return array|mixed
 */

function addActivityLog($action,$source,$ip_address,$location){
    $return = false;
    if (ActivityLog::create(['action'=>$action,'user_id'=>$source,'ip_address'=>$ip_address,'location'=>$location]))
        $return = true;
    return $return;


}

function country($input=null){

    if (is_null($input)) {
        return CountryList::where('status',STATUS_ACTIVE)->pluck('value','key');
    } else {

        return CountryList::where('key', $input)->pluck('value')->first();
    }
}

/**
 * @param $registrationIds
 * @param $type
 * @param $data_id
 * @param $count
 * @param $message
 * @return array
 */
//google firebase
function pushNotification($registrationIds,$type, $data_id, $count,$message)
{

    // $news = \App\News::find($data_id);
    $fields = array
    (
        'to' => $registrationIds,
        "delay_while_idle" => true,
        "time_to_live" => 3,
        /*    'notification' => [
                'body' => strip_tags(str_limit($news->description,30)),
                'title' => str_limit($news->title,25),
            ],*/
        'data'=> [
            'message' => $message,
            'title' => 'monttra',
            'id' =>$data_id,
            'is_background' => true,
            'content_available'=>true,

        ]
    );


    $headers = array
    (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return $fields;

}

/**
 * @param $string
 * @return string|string[]|null
 */
//function clean($string) {
//    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
//    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
//    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
//}

/**
 * @param $registrationIds
 * @param $type
 * @param $data_id
 * @param $count
 * @param $message
 * @return array
 */
//google firebase
function pushNotificationIos($registrationIds,$type, $data_id, $count,$message)
{

//    $news = \App\News::find($data_id);

    $fields = array
    (
        'to' => $registrationIds,
        "delay_while_idle" => true,

        "time_to_live" => 3,
        'notification' => [
            'body' => '',
            'title' => $message,
            'vibrate' => 1,
            'sound' => 'default',
        ],
        'data'=> [
            'message' => '',
            'title' => $message,
            'id' => $data_id,
            'is_background' => true,
            'content_available'=>true,


        ]
    );

    $headers = array
    (
        'Authorization: key=' . API_ACCESS_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    curl_close($ch);

    return $fields;

}





/**
 * @param $a
 * @return string
 */
//Random string
function randomString($a)
{
    $x = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $c = strlen($x) - 1;
    $z = '';
    for ($i = 0; $i < $a; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }
    return $z;
}

/**
 * @param int $a
 * @return string
 */
// random number
function randomNumber($a = 10)
{
    $x = '123456789';
    $c = strlen($x) - 1;
    $z = '';
    for ($i = 0; $i < $a; $i++) {
        $y = rand(0, $c);
        $z .= substr($x, $y, 1);
    }
    return $z;
}

//use array key for validator
/**
 * @param $array
 * @param string $seperator
 * @param array $exception
 * @return string
 */
function arrKeyOnly($array, $seperator = ',', $exception = [])
{
    $string = '';
    $sep = '';
    foreach ($array as $key => $val) {
        if (in_array($key, $exception) == false) {
            $string .= $sep . $key;
            $sep = $seperator;
        }
    }
    return $string;
}

/**
 * @param $img
 * @param $path
 * @param null $user_file_name
 * @param null $width
 * @param null $height
 * @return bool|string
 */
function uploadInStorage($img, $path, $user_file_name = null, $width = null, $height = null)
{

    if (!file_exists($path)) {

        mkdir($path, 777, true);
    }

    if (isset($user_file_name) && $user_file_name != "" && file_exists($path . $user_file_name)) {
        unlink($path . $user_file_name);
    }
    // saving image in target path
    $imgName = uniqid() . '.' . $img->getClientOriginalExtension();
    $imgPath = public_path($path . $imgName);
    // making image
    $makeImg = \Intervention\Image\Image::make($img)->orientate();
    if ($width != null && $height != null && is_int($width) && is_int($height)) {
        // $makeImg->resize($width, $height);
        $makeImg->fit($width, $height);
    }

    if ($makeImg->save($imgPath)) {
        return $imgName;
    }
    return false;
}

function uploadimage($img, $path, $user_file_name = null, $width = null, $height = null)
{

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
    if (isset($user_file_name) && $user_file_name != "" && file_exists($path . $user_file_name)) {
        unlink($path . $user_file_name);
    }
    // saving image in target path
    $imgName = uniqid() . '.' . $img->getClientOriginalExtension();
    $imgPath = public_path($path . $imgName);
    // making image
    $makeImg = Image::make($img)->orientate();
    if ($width != null && $height != null && is_int($width) && is_int($height)) {
        // $makeImg->resize($width, $height);
        $makeImg->fit($width, $height);
    }

    if ($makeImg->save($imgPath)) {
        return $imgName;
    }
    return false;
}


/**
 * @param $path
 * @param $file_name
 */
function removeImage($path, $file_name)
{
    if (isset($file_name) && $file_name != "" && file_exists($path . $file_name)) {
        unlink($path . $file_name);
    }
}

//Advertisement image path
/**
 * @return string
 */
function path_image()
{
    return IMG_VIEW_PATH;
}

/**
 * @return string
 */
function upload_path()
{
    return 'uploads/';
}



/**
 * @param $file
 * @param $destinationPath
 * @param null $oldFile
 * @return bool|string
 */
function uploadFile($new_file, $path, $old_file_name = null, $width = null, $height = null)
{
    if (!file_exists(public_path($path))) {
        mkdir(public_path($path), 0777, true);
    }
    if (isset($old_file_name) && $old_file_name != "" && file_exists($path . substr($old_file_name, strrpos($old_file_name, '/') + 1))) {

        unlink($path . '/' . substr($old_file_name, strrpos($old_file_name, '/') + 1));
    }

    $input['imagename'] = uniqid() . time() . '.' . $new_file->getClientOriginalExtension();
    $imgPath = public_path($path . $input['imagename']);

    $makeImg = Image::make($new_file);
    if ($width != null && $height != null && is_int($width) && is_int($height)) {
        $makeImg->resize($width, $height);
        $makeImg->fit($width, $height);
    }

    if ($makeImg->save($imgPath)) {
        return $input['imagename'];
    }
    return false;

}

function containsWord($str, $word)
{
    return !!preg_match('#\\b' . preg_quote($word, '#') . '\\b#i', $str);
}

/**
 * @param $destinationPath
 * @param $file
 */
function deleteFile($destinationPath, $file)
{
    if (isset($file) && $file != "" && file_exists($destinationPath . $file)) {
        unlink($destinationPath . $file);
    }
}

//function for getting client ip address
/**
 * @return mixed|string
 */
function get_clientIp()
{
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

/**
 * @return array|bool
 */
function language()
{
    $lang = [];
    $path = base_path('resources/lang');
    foreach (glob($path . '/*.json') as $file) {
        $langName = basename($file, '.json');
        $lang[$langName] = $langName;
    }
    return empty($lang) ? false : $lang;
}

/**
 * @param null $input
 * @return array|mixed
 */
function langName($input = null)
{
    $output = [
        'ar' => 'Arabic',
        'de' => 'German',
        'en' => 'English',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fr' => 'French',
        'it' => 'Italian',
        'pl' => 'Polish',
        'pt' => 'Portuguese (European)',
        'pt-br' => 'Portuguese (Brazil)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'zh-CN' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)',
        'zh-HK' => 'Chinese (Hong Kong)',
        'zh-SG' =>'Chinese (Singapore)',
        'zh' =>'Chinese (Singapore)',
        'ko' => 'Korean',
        'ja' => 'Japanese',
        'nl' => 'Dutch',
        'id' => 'Indonesian',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}

function langNameOld($input = null)
{
    $output = [
        'ar' => 'Arabic',
        'de' => 'German',
        'en' => 'English',
        'es' => 'Spanish',
        'et' => 'Estonian',
        'fr' => 'French',
        'it' => 'Italian',
        'pl' => 'Polish',
        'pt' => 'Portuguese (European)',
        'pt-br' => 'Portuguese (Brazil)',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'th' => 'Thai',
        'tr' => 'Turkish',
        'zh-CN' => 'Chinese (Simplified)',
        'zh-TW' => 'Chinese (Traditional)',
        'zh-HK' => 'Chinese (Hong Kong)',
        'zh-SG' =>'Chinese (Singapore)',
        'zh' =>'Chinese (Singapore)',
        'ko' => 'Korean',
        'ja' => 'Japanese',
        'nl' => 'Dutch',
        'id' => 'Indonesian',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}

/**
 * @param null $input
 * @return array|mixed|string
 */
function langNameMobile($input = null)
{
    $output = [
        'en' => 'English',
        'fr' => 'French',
        'it' => 'Italian',
        'pt-PT' => ' Português(Portugal)',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        if(isset($output[$input]))
            return $output[$input];
        return '';
    }
}

if (!function_exists('settings')) {

    function settings($keys = null)
    {
        if ($keys && is_array($keys)) {
            return AdminSetting::whereIn('slug', $keys)->pluck('value', 'slug')->toArray();
        } elseif ($keys && is_string($keys)) {
            $setting = AdminSetting::where('slug', $keys)->first();
            return empty($setting) ? false : $setting->value;
        }
        return AdminSetting::pluck('value', 'slug')->toArray();
    }
}

function landingPageImage($index,$static_path){
    if (settings($index)){
        return asset(path_image()).'/'.settings($index);
    }
    return asset('assets/landing').'/'.$static_path;
}

function userSettings($keys = null){
    if ($keys && is_array($keys)) {
        return UserSetting::whereIn('slug', $keys)->pluck('value', 'slug')->toArray();
    } elseif ($keys && is_string($keys)) {
        $setting = UserSetting::where('slug', $keys)->first();
        return empty($setting) ? false : $setting->value;
    }
    return UserSetting::pluck('value', 'slug')->toArray();
}
//Call this in every function
/**
 * @param $lang
 */
function set_lang($lang)
{
    $default = settings('lang');
    $lang = strtolower($lang);
    $languages = language();
    if (in_array($lang, $languages)) {
        app()->setLocale($lang);
    } else {
        if (isset($default)) {
            $lang = $default;
            app()->setLocale($lang);
        }
    }
}

/**
 * @param null $input
 * @return array|mixed
 */
function langflug($input = null)
{

    $output = [
        'en' => '<i class="flag-icon flag-icon-us"></i> ',
        'pt-PT' => '<i class="flag-icon flag-icon-pt"></i>',
        'fr' => '<i class="flag-icon flag-icon-fr"></i>',
        'it' => '<i class="flag-icon flag-icon-it"></i>',
    ];
    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input];
    }
}


//find odd even
/**
 * @param $number
 * @return string
 */
function oddEven($number)
{
//    dd($number);
    if ($number % 2 == 0) {
        return 'even';
    } else {
        return 'odd';
    }
}

// get user currency
function getUserCurrency()
{
    if(Auth::user()) {
        $currency = Auth::user()->currency;
    } else {
        $currency = settings('currency');
    }
    return $currency;
}
function toCoinPrice($coinType)
{
    $coinPrice = 1;
    $coin = Coin::where(['coin_type' => $coinType])->first();
    if (!empty($coin)) {
        $coinPrice = $coin->coin_price == 0 ? 1 : $coin->coin_price;
    }
    return $coinPrice;
}
function fromCoinPrice($coinType)
{
    $coinPrice = 0;
    $coin = Coin::where(['coin_type' => $coinType])->first();
    if (!empty($coin)) {
        $coinPrice = $coin->coin_price == 0 ? 1 : $coin->coin_price;
    }
    return $coinPrice;
}
// get own site market rate
function getOwnMarketRate($from,$to)
{
    $rate = 0;
    try {
        $tradeCoinId = get_coin_id($from);
        $baseCoinId = get_coin_id($to);
        $repo = new CoinPairRepository(CoinPair::class);
        $pair = $repo->getCoinPairsData($baseCoinId, $tradeCoinId);
        if (!empty($pair) && ($pair->last_price > 0)) {
            $rate = $pair->last_price;
        } else {
            $fromCoinPrice = fromCoinPrice($from);
            $toCoinPrice = toCoinPrice($to);
            $rate = bcdiv($fromCoinPrice,$toCoinPrice,8);
        }
    } catch (\Exception $e) {
       storeException('getOwnMarketRate', $e->getMessage());
    }

    return $rate;
}


function convert_currency($amount, $to = 'USD', $from = 'BTC',$currency=NULL)
{
    $returnAmountData = 0;
    try {
        $toCoinType = $to;
        $fromCoinType = $from;
        $toCoin = Coin::where(['coin_type' => $to])->first();
        if(empty($toCoin)) {
            $toCoinType = 'USDT';
        }
        $fromCoin = Coin::where(['coin_type' => $from])->first();
        if(empty($fromCoin)) {
            $fromCoinType = 'USDT';
        }
        $rate = getOwnMarketRate($fromCoinType,$toCoinType);
        $returnAmount = bcmul($amount,$rate,8);
        if (is_null($currency)) {
            $returnAmountData = $returnAmount;
        } else {
            $currency = strtoupper(!empty($currency) ? $currency : getUserCurrency());
            $returnAmountData = convert_fiat_currency($from,$currency,$returnAmount);
        }
    } catch (\Exception $e) {
        storeException('convert_currency', $e->getMessage());
    }

    return $returnAmountData;
}

// get fiat currency rate
function convert_fiat_currency($from,$to,$amount)
{
    try {
        $fromCoin = CurrencyList::where(['code' => strtoupper($from)])->first();
        $toCoinRate = toCoinRate($to);
        if (empty($fromCoin)) {
            $returnAmount = bcmul($toCoinRate,$amount,8);
        } else {
            $returnAmount = bcmul(bcdiv(1,$toCoinRate,8),$amount,8);
        }
    } catch (\Exception $e) {
        storeException('convert_fiat_currency ', $e->getMessage());
        $returnAmount = 0;
    }
    return $returnAmount;
}

// convert fiat to fiat
function convert_fiat_to_fiat()
{

}

function fromCoinRate($from)
{
    $fromCoin = CurrencyList::where(['code' => strtoupper($from)])->first();
    if(empty($fromCoin)) {
        $fromCoinRate = 1;
    } else {
        if($fromCoin->rate == 0) {
            $fromCoinRate = 1;
        } else {
            $fromCoinRate = $fromCoin->rate;
        }
    }
    return $fromCoinRate;
}

function toCoinRate($to)
{
    $toCoin = CurrencyList::where(['code' => strtoupper($to)])->first();
    if(empty($toCoin)) {
        $toCoinRate = 1;
    } else {
        if($toCoin->rate == 0) {
            $toCoinRate = 1;
        } else {
            $toCoinRate = $toCoin->rate;
        }
    }
    return $toCoinRate;
}

function convert_currency_rate($amount, $to = 'USD', $from = 'BTC')
{
    $to = check_default_coin_type($to);
    $from = check_default_coin_type($from);
    $apiKey = env('CRYPTOCOMPARE_API_KEY') ?? '';
    $url = "https://min-api.cryptocompare.com/data/price?fsym=$from&tsyms=$to&api_key=$apiKey";
    $json = file_get_contents($url); //,FALSE,$ctx);
    $jsondata = json_decode($json, TRUE);

    if(isset($jsondata['Response']) && $jsondata['Response']=='Error') {
        Log::info('convert_currency error ->'. $jsondata['Message']);
        $returnAmount = 0;
    }else{
        $returnAmount = bcmul($amount, custom_number_format($jsondata[$to]),8);
    }
    return $returnAmount;
}

function convert_currency_rate_all($data,$currency='USD')
{
    $returnData = [];
    $apiKey = env('CRYPTOCOMPARE_API_KEY') ?? '';
    $url = "https://min-api.cryptocompare.com/data/pricemulti?fsyms=$data&tsyms=".$currency."&api_key=$apiKey";
    $json = file_get_contents($url); //,FALSE,$ctx);
    $jsondata = json_decode($json, TRUE);

    if(isset($jsondata['Response']) && $jsondata['Response']=='Error') {
        Log::info('convert_currency_rate_all error ->'. $jsondata['Message']);
    }else{
        $returnData = $jsondata;
    }
    return $returnData;
}
// fees calculation
function calculate_fees($amount, $method)
{
    $settings = allsetting();

    try {
        if ($method == SEND_FEES_FIXED) {
            return $settings['send_fees_fixed'];
        } elseif ($method == SEND_FEES_PERCENTAGE) {
            return ($settings['send_fees_percentage'] * $amount) / 100;
        }  else {
            return 0;
        }
    } catch (\Exception $e) {
        return 0;
    }
}

/**
 * @param null $message
 * @return string
 */
function getToastrMessage($message = null)
{
    if (!empty($message)) {

        // example
        // return redirect()->back()->with('message','warning:Invalid username or password');

        $message = explode(':', $message);
        if (isset($message[1])) {
            $data = 'toastr.' . $message[0] . '("' . $message[1] . '")';
        } else {
            $data = "toastr.error(' write ( errorType:message ) ')";
        }

        return '<script>' . $data . '</script>';

    }

}

function getUserBalance($user_id){
    $wallets = Wallet::where(['user_id' => $user_id, 'coin_type' => 'Default']);

    $data['available_coin'] = $wallets->sum('balance');
    $data['available_used'] = $data['available_coin'] * settings('coin_price');
//    $data['pending_withdrawal_coin'] = WithdrawHistory::whereIn('wallet_id',$wallets->pluck('id'))->where('status',STATUS_PENDING)->sum('amount');
//    $data['pending_withdrawal_usd'] =  $data['pending_withdrawal_coin']*settings('coin_price');
    $coins = Coin::orderBy('id', 'ASC')->get();
    if (isset($coins[0])) {
        foreach($coins as $coin) {
            $walletAmounts = Wallet::where(['user_id' => $user_id, 'coin_type' => $coin->type])->sum('balance');
            $data[$coin->type] = $walletAmounts;
        }
    }
    $data['pending_withdrawal_coin'] = 0;
    $data['pending_withdrawal_usd'] = 0;
    return $data;
}

// total withdrawal
function total_withdrawal($user_id)
{
    $total = 0;
    $withdrawal = WithdrawHistory::join('wallets', 'wallets.id', '=','withdraw_histories.wallet_id')
        ->where('wallets.user_id', $user_id)
        ->where('withdraw_histories.status',STATUS_SUCCESS)
        ->get();
    if (isset($withdrawal[0])) {
        $total = $withdrawal->sum('amount');
    }

    return $total;
}
// total deposit
function total_deposit($user_id)
{
    $total = 0;
    $deposit = DepositeTransaction::join('wallets', 'wallets.id', '=','deposite_transactions.receiver_wallet_id')
        ->where('wallets.user_id', $user_id)
        ->where('deposite_transactions.status',STATUS_SUCCESS)
        ->get();
    if (isset($deposit[0])) {
        $total = $deposit->sum('amount');
    }

    return $total;
}

function getActionHtml($list_type,$user_id,$item){

    $html = '<div class="activity-icon"><ul>';
    if ($list_type == 'active_users'){
        $html .='
               <li><a title="'.__('View').'" href="'.route('adminUserProfile').'?id='.encrypt($user_id).'&type=view" class="user-two btn btn-info btn-sm"><span><i class="fa fa-eye"></i>'.__(' View').'</span></a></li>
               <li><a title="'.__('Edit').'" href="'.route('admin.UserEdit').'?id='.encrypt($user_id).'&type=edit" class="user-two btn btn-primary btn-sm"><span><i class="fa fa-edit"></i>'.__(' Edit').'</span></a></li>
               <li>'.suspend_html('admin.user.suspend',encrypt($user_id)).'</li>';
                if(!empty($item->google2fa_secret)) {
                    $html .='<li>'.gauth_html('admin.user.remove.gauth',encrypt($user_id)).'</li>';
                }
                $html .='<li>'.delete_html('admin.user.delete',encrypt($user_id)).'</li>';

    } elseif ($list_type == 'suspend_user') {
        $html .='<li><a title="'.__('View').'" href="'.route('admin.UserEdit').'?id='.encrypt($user_id).'&type=view" class="btn btn-info btn-sm"><span><i class="fa fa-eye"></i>'.__(' View').'</span></a></li>
          <li><a data-toggle="tooltip" title="Activate" href="'.route('admin.user.active',encrypt($user_id)).'" class="btn btn-success btn-sm"><span><i class="fa fa-check-circle-o"></i>'.__(' Activate').'</span></a></li>
         ';

    } elseif($list_type == 'deleted_user') {
        $html .='<li><a title="'.__('View').'" href="'.route('admin.UserEdit').'?id='.encrypt($user_id).'&type=view" class="btn btn-info btn-sm"><span><i class="fa fa-eye"></i>'.__(' View').'</span></a></li>
          <li><a data-toggle="tooltip" title="Activate" href="'.route('admin.user.active',encrypt($user_id)).'" class="btn btn-success btn-sm"><span><i class="fa fa-check-circle-o"></i>'.__(' Activate').'</span></a></li>
         ';

    } elseif($list_type == 'email_pending') {
        $html .=' <li><a data-toggle="tooltip" title="Email verify" href="'.route('admin.user.email.verify',encrypt($user_id)).'" class="btn btn-success btn-sm"><span><i class="fa fa-envelope"></i>'.__(' Verify').'</span></a></li>';
    } elseif ($list_type == 'phone_pending') {
        $html .=' <li><a data-toggle="tooltip" title="Phone verify" href="'.route('admin.user.phone.verify',encrypt($user_id)).'" class="btn btn-success btn-sm"><span><i class="fa fa-mobile"></i>'.__(' Verify').'</span></a></li>';
    }
    $html .='</ul></div>';
    return $html;
}

// Html render
/**
 * @param $route
 * @param $id
 * @return string
 */
function edit_html($route, $id)
{
    $html = '<li class="viewuser"><a title="'.__('Edit').'" href="' . route($route, encrypt($id)) . '"><i class="fa fa-pencil"></i></a></li>';
    return $html;
}


/**
 * @param $route
 * @param $id
 * @return string
 * @throws Exception
 */

function receipt_view_html($image_link)
{
    $num = random_int(1111111111,9999999999999);
    $html = '<div class="deleteuser"><a title="'.__('Bank receipt').'" href="#view_' . $num . '" data-toggle="modal">Bank Deposit</a> </div>';
    $html .= '<div id="view_' . $num . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-lg">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Bank receipt') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><img src="'.$image_link.'" alt=""></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function delete_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('delete').'" href="#delete_' . decrypt($id) . '" data-toggle="modal" class="btn btn-danger btn-sm"><span><i class="fa fa-trash"></i>'.__(' Delete').'</span></a> </li>';
    $html .= '<div id="delete_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to delete ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
function delete_html2($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('delete').'" href="#delete_' . ($id) . '" data-toggle="modal"><span class="flaticon-delete-user"></span></a> </li>';
    $html .= '<div id="delete_' . ($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to delete ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function suspend_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('Suspend').'" href="#suspends_' . decrypt($id) . '" data-toggle="modal" class="btn btn-warning btn-sm"><span><i class="fa fa-minus-circle"></i>'.__(' Suspend').'</span></a> </li>';
    $html .= '<div id="suspends_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Suspend') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to suspend ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function active_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('Active').'" href="#active_' . decrypt($id) . '" data-toggle="modal"><span class="flaticon-delete"></span></a> </li>';
    $html .= '<div id="active_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Delete') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Active ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success" href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function accept_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('Accept').'" href="#accept_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-check-circle-o" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="accept_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Accept ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success" href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function default_accept_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('Accept').'" href="#accept_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-check-circle-o" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="accept_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<form action="'.route($route).'" method="get">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Accept') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">';
    $html .= '<p class="text-warning">' . __('Do you want to Accept ?') . '</p>';
    $html .= '<input type="hidden" name="withdrawal_id" value="'.$id.'">';
    $html .= '<label>' . __('Transaction Hash') . '</label>';
    $html .= '<input type="text" required name="transaction_hash" class="form-control">';
    $html .= '<small>' . __('It is a default coin withdrawal . so please manually send coin and put here the transaction hash ') . '</small>';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button type="submit" class="btn btn-success" >' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function reject_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('Reject').'" href="#reject_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-minus-square" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="reject_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Reject') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Reject ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger" href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

function reject_html_get_reject_note($route, $id)
{
    $html = '<li class="deleteuser"><a title="'.__('Reject').'" href="#reject_' . decrypt($id) . '" data-toggle="modal"><span class=""><i class="fa fa-minus-square" aria-hidden="true"></i>
    </span></a> </li>';
    $html .= '<div id="reject_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Reject') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<form action="'.route($route) . '"method="post">';
    $html .= '<input type="hidden" name="_token" value="'.csrf_token() .'" />';
    $html .= '<input type="hidden" name="id" value="'.$id .'" />';
    $html .= '<div class="modal-body">';
    $html .= '<p>' . __('Do you want to Reject ?') . '</p>';
    $html .= '<label>'.__('Rejected For').'</label>';
    $html .= '<textarea name="reject_note" style="width:100%;height: 120px;" required></textarea>';
    $html .= '</div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<button class="btn btn-danger" type="submit">' . __('Confirm') . '</button>';
    $html .= '</div>';
    $html .= '</form>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
/**
 * @param $route
 * @param $id
 * @return string
 */
function ChangeStatus($route, $id)
{
    $html = '<li class=""><a href="#status_' . $id . '" data-toggle="modal"><i class="fa fa-ban"></i></a> </li>';
    $html .= '<div id="status_' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Block') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Block this product ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

/**
 * @param $route
 * @param $id
 * @return string
 */
function BlockStatusChange($route, $id)
{   $html = '<ul class="activity-menu">';
    $html .= '<li class=" "><a title="'.__('Status change').'" href="#blockuser' . $id . '" data-toggle="modal"><i class="fa fa-check"></i></a> </li>';
    $html .= '<div id="blockuser' . $id . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Block') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to Unblock this product ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</ul>';

    return $html;
}

/**
 * @param $route
 * @param $param
 * @return string
 */
function cancelSentItem($route,$param)
{
    $html  = '<li class=""><a title="'.__('Cancel').'" class="delete" href="#blockuser' . $param . '" data-toggle="modal"><i class="fa fa-remove"></i></a> </li>';
    $html .= '<div id="blockuser' . $param . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Cancel') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to cancel this product ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-success"href="' . route($route).$param. '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';


    return $html;
}

function modal_image_show($id,$image_name)
{
    $html  = '<a title="'.__('Cancel').'" class="delete" href="#image_id_' . $id . '" data-toggle="modal">'.__('Show Bank Receipt') .'</a>';
    $html .= '<div id="image_id_' . $id. '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-md">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Cancel') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body">
    <div style="display:block;">
    <img style="width: 100%;" src="'.asset('/uploaded_file/sleep').'/'.$image_name.'" alt="Forest"></div></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';

    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
//status search
/**
 * @param $keyword
 * @return array
 */
function status_search($keyword)
{
    $st = [];
    if (strpos('_active', strtolower($keyword)) != false) {
        array_push($st, STATUS_SUCCESS);
    }
    if (strpos('_pending', strtolower($keyword)) != false) {
        array_push($st, STATUS_PENDING);
    }
    if (strpos('_inactive', strtolower($keyword)) != false) {
        array_push($st, STATUS_PENDING);
    }

    if (strpos('_deleted', strtolower($keyword)) != false) {
        array_push($st, STATUS_DELETED);
    }
    return $st;
}

function cim_search($keyword)
{

    return $keyword;
}

/**
 * @param $route
 * @param $status
 * @param $id
 * @return string
 */
function statusChange_html($route, $status, $id)
{
    $icon = ($status != STATUS_SUCCESS) ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>';
    $status_title = ($status != STATUS_SUCCESS) ? statusAction(STATUS_SUCCESS) : statusAction(STATUS_SUSPENDED);
    $html = '';
    $html .= '<a title="'.__('Status change').'" href="' . route($route, encrypt($id)) . '">' . $icon . '<span>' . $status_title . '</span></a> </li>';
    return $html;
}

//exists img search
/**
 * @param $image
 * @param $path
 * @return string
 */
function imageSrc($image, $path)
{

    $return = asset('admin/images/default.jpg');
    if (!empty($image) && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}
//exists img search
/**
 * @param $image
 * @param $path
 * @return string
 */
function imageSrcUser($image, $path)
{

    $return = asset('assets/img/avater.png');
    if (!empty($image) && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}

function imageSrcVerification($image, $path)
{


    $return = asset('/assets/images/default_card.svg');
    if (!empty($image) && file_exists(public_path($path . '/' . $image))) {
        $return = asset($path . '/' . $image);
    }
    return $return;
}

/**
 * @param $title
 */
function title($title)
{
    session(['title' => $title]);
}


/**
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * @return bool|string|\Webpatser\Uuid\Uuid
 * @throws Exception
 */
function uniqueNumber()
{

    $rand = Uuid::generate();
    $rand = substr($rand,30);
    $prefix = Auth::user()->prefix;
    if(ProductSerialAndGhost::where('serial_id',$prefix.$rand)->orwhere('ghost_id',$prefix.'G'.$rand)->exists())
        return uniqueNumber();
    else
        return $rand;
}



function customNumberFormat($value)
{
    if (is_integer($value)) {
        return number_format($value, 8, '.', '');
    } elseif (is_string($value)) {
        $value = floatval($value);
    }
    $number = explode('.', number_format($value, 10, '.', ''));
    return $number[0] . '.' . substr($number[1], 0, 2);
}

if (!function_exists('max_level')) {
    function max_level()
    {
        $max_level = allsetting('max_affiliation_level');

        return $max_level ? $max_level : 3;
    }

}

if (!function_exists('user_balance')) {
    function user_balance($userId)
    {
        $balance = Wallet::where('user_id', $userId)->sum(DB::raw('balance + referral_balance'));

        return $balance ? $balance : 0;
    }

}

if (!function_exists('visual_number_format'))
{
    function visual_number_format($value)
    {
        if (is_integer($value)) {
            return number_format($value, 2, '.', '');
        } elseif (is_string($value)) {
            $value = floatval($value);
        }
        $number = explode('.', number_format($value, 14, '.', ''));
        $intVal = (int)$value;
        if ($value > $intVal || $value < 0) {
            $intPart = $number[0];
            $floatPart = substr($number[1], 0, 8);
            $floatPart = rtrim($floatPart, '0');
            if (strlen($floatPart) < 2) {
                $floatPart = substr($number[1], 0, 2);
            }
            return $intPart . '.' . $floatPart;
        }
        return $number[0] . '.' . substr($number[1], 0, 2);
    }
}

// comment author name
function comment_author_name($id)
{
    $name = '';
    $user = User::where('id', $id)->first();
    if(isset($user)) {
        $name = $user->first_name.' '.$user->last_name;
    }

    return $name;
}

function gauth_html($route, $id)
{
    $html = '<li class="deleteuser"><a title="' . __('Reset gauth') . '" href="#remove_gauth_' . decrypt($id) . '" data-toggle="modal" class="btn btn-success btn-sm"><span><i class="fa fa-refresh"></i>'.__(' gAuth').'</span></a> </li>';
    $html .= '<div id="remove_gauth_' . decrypt($id) . '" class="modal fade delete" role="dialog">';
    $html .= '<div class="modal-dialog modal-sm">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><h6 class="modal-title">' . __('Reset Gauth') . '</h6><button type="button" class="close" data-dismiss="modal">&times;</button></div>';
    $html .= '<div class="modal-body"><p>' . __('Do you want to remove gauth ?') . '</p></div>';
    $html .= '<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">' . __("Close") . '</button>';
    $html .= '<a class="btn btn-danger"href="' . route($route, $id) . '">' . __('Confirm') . '</a>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}
if (!function_exists('all_months')) {
    function all_months($val = null)
    {
        $data = array(
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
        );
        if ($val == null) {
            return $data;
        } else {
            return $data[$val];
        }
    }
}
function custom_number_format($value)
{
    if (is_integer($value)) {
        return number_format($value, 8, '.', '');
    } elseif (is_string($value)) {
        $value = floatval($value);
    }
    $number = explode('.', number_format($value, 14, '.', ''));
    return $number[0] . '.' . substr($number[1], 0, 8);
}

function converts_currency($amountInUsd, $to, $price)
{
    try {
        $array['amount'] = $amountInUsd;

        if ($to == "LTCT"){
            $to = "LTC";
        }

        if ( ($price['error'] == "ok") ) {

            $one_coin = $price['result'][$to]['rate_btc']; // dynamic coin rate in btc

            $one_usd = $price['result']['USD']['rate_btc']; // 1 usd == btc rate

            $total_amount_in_usd = bcmul($one_usd, $amountInUsd,8);

            return custom_number_format(bcdiv($total_amount_in_usd, $one_coin,8));
        }
    } catch (\Exception $e) {

        return number_format(0, 8);
    }
}


function convert_to_crypt($amountInBTC, $to)
{
    try {
        $coinpayment = new CoinPaymentsAPI();

        $price = $coinpayment->GetRates('');
        if ( ($price['error'] == "ok") ) {

            $one_coin = $price['result'][$to]['rate_btc']; // dynamic coin rate in btc
            $one_usd = $price['result']['USD']['rate_btc']; // 1 usd ==  btc rate

            $total_amount_in_btc = bcmul($one_coin, $amountInBTC,8);
            $total_amount_in_usd = bcdiv($total_amount_in_btc, $one_usd,8);

            return custom_number_format(bcdiv($total_amount_in_usd, settings('coin_price'),8));
        }
    } catch (\Exception $e) {
        return custom_number_format($amountInBTC, 8);
    }
}


//User Activity
function createUserActivity($userId, $action = '', $ip = null, $device = null){
    if ($ip == null) {
        $current_ip = get_clientIp();
    } else {
        $current_ip = $ip;
    }
    if($device == null){
        $agent = new Agent();
        $deviceType = isset($agent) && $agent->isMobile() == true ? 'Mobile' : 'Web';
    }else{
        $deviceType = $device == 1 ?  'Mobile' : 'Web';
    }

//        try{
//            $location = GeoIP::getLocation($current_ip);
//            $country = $location->country;
//        }catch(\Exception $e){
//            $country  = '';
//        }

    $activity['user_id'] = $userId;
    $activity['action'] = $action;
    $activity['ip_address'] = isset($current_ip) ? $current_ip : '0.0.0.0';
    $activity['source'] = $deviceType;
    $activity['location'] = '';
    ActivityLog::create($activity);
}
// user image
function show_image($id, $type)
{
    $img = asset('assets/img/avater.png');
    if ($type =='logo') {
        if (!empty(allsetting('logo'))) {
            $img = asset(path_image().allsetting('logo'));
        } else {
            $img = asset('assets/user/images/logo.svg');
        }
    } elseif($type == 'login_logo') {
        if (!empty(allsetting('login_logo'))) {
            $img = asset(path_image().allsetting('login_logo'));
        } else {
            $img = asset('assets/user/images/logo.svg');
        }
    } else {
        if (!empty($id)) {
            $user = User::where('id', $id)->first();
            if (isset($user) && !empty($user->photo)) {
                $img = asset(IMG_USER_PATH . $user->photo);
            }
        }
    }
    return $img;
}
// plan image
function show_plan_image($plan_id,$img=null)
{
    $image = asset('assets/img/badge/Gold.svg');
    if (!empty($img)) {
        $image = asset(path_image().$img);
    } else {
        if ($plan_id == 1) {
            $image = asset('assets/img/badge/Silver.svg');
        } elseif ($plan_id == 2) {
            $image = asset('assets/img/badge/Gold.svg');
        } elseif ($plan_id == 3) {
            $image = asset('assets/img/badge/Platinum.svg');
        }
    }

    return $image;
}

// member plan bonus percentage
function plan_bonus_percentage($type,$bonus,$amount)
{
    $bonus_percentage = $bonus;
    if ($type == PLAN_BONUS_TYPE_FIXED) {
        $bonus_percentage = (100 * $bonus) / $amount;
    }

    return number_format($bonus_percentage,2);
}
// calculate bonus
function calculate_plan_bonus($bonus_percentage,$amount)
{
    $bonus = ($bonus_percentage * $amount) / 100;

    return number_format($bonus,8);
}

// get coin payment address
function get_coin_payment_address($payment_type)
{
    Log::info('get_coin_payment_address '. 'start the process ');
    try {
        $coin_payment = new CoinPaymentsAPI();
        $address = $coin_payment->GetCallbackAddress($payment_type);
        Log::info('address : '.json_encode($address));
        if ( isset($address['error']) && ($address['error'] == 'ok') ) {
            return $address['result']['address'];
        } else {
            Log::info('get_coin_payment_address '. ' address not generated');
            return false;
        }
    } catch (\Exception $e) {
        Log::info('get_coin_payment_address exception '. $e->getMessage());

    }
}
// get node address
function get_node_address($coin_type)
{
    Log::info('get_node_address '. 'start the process ');
    try {
        $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coin_type])
            ->select('coins.*', 'coin_settings.*')
            ->first();
        if ($coin) {
            $bitCoinApi =  new BitCoinApiService($coin->coin_api_user,decryptId($coin->coin_api_pass),$coin->coin_api_host,$coin->coin_api_port);
            $address = $bitCoinApi->getNewAddress();
            Log::info('address : '.$address);
            if (isset($address)) {
                return $address;
            } else {
                Log::info('get_node_address '. ' address not generated');
                return false;
            }
        } else {
            storeException('get_node_address ', ' Coin not found');
            return false;
        }

    } catch (\Exception $e) {
        Log::info('get_node_address exception '. $e->getMessage());

    }
}


// get bitgo address
function get_bitgo_address($coin_type)
{
    Log::info('get_bitgo_address '. 'start the process ');
    try {
        $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coin_type])
            ->first();
        if ($coin) {
            if (empty($coin->bitgo_wallet_id)) {
                storeException('get_bitgo_address ', 'bitgo_wallet_id not found');
                return false;
            } else {
                $bitgoApi =  new BitgoWalletService();
                $address = $bitgoApi->createBitgoWalletAddress($coin->coin_type,$coin->bitgo_wallet_id,$coin->chain);
                storeException('get_bitgo_address ', json_encode($address));
                if ($address['success']) {
                    return $address['data']['address'];
                } else {
                    storeException('get_bitgo_address address', $address['message']);
                    return false;
                }
            }
        } else {
            storeException('get_bitgo_address ', ' Coin not found');
            return false;
        }
    } catch (\Exception $e) {
        storeException('get_bitgo_address ', $e->getMessage());
    }
}

// get erc20 or bep20 address
function get_erc20_address($coin_type)
{
    storeException('get_erc20_address', 'start the process ');
    try {
        $coin = Coin::join('coin_settings','coin_settings.coin_id', '=', 'coins.id')
            ->where(['coins.coin_type' => $coin_type])
            ->first();
        if ($coin) {
            if (empty($coin->chain_link)) {
                storeException('get_erc20_address ', 'chain link not found');
                return false;
            } else {
                $api =  new ERC20TokenApi($coin);
                $address = $api->createNewWallet();
                storeException('get_erc20_address ', json_encode($address));
                if ($address['success']) {
                    return $address['data'];
                } else {
                    storeException('get_erc20_address address', $address['message']);
                    return false;
                }
            }
        } else {
            storeException('get_erc20_address ', ' Coin not found');
            return false;
        }
    } catch (\Exception $e) {
        storeException('get_erc20_address ', $e->getMessage());
    }
}

// get coin address
function get_coin_address($coin_type, $network)
{
    $address = '';
    $data = [
        'wallet_key' => ''
    ];
    try {
        if ($network == COIN_PAYMENT) {
            $address = get_coin_payment_address($coin_type);
        } elseif ($network == BITCOIN_API) {
            $address = get_node_address($coin_type);
        }  elseif ($network == BITGO_API) {
            $address = get_bitgo_address($coin_type);
        }  elseif ($network == ERC20_TOKEN || $network == BEP20_TOKEN) {
            $result = get_erc20_address($coin_type);
            if($result) {
                $address = $result->address;
                $data['wallet_key'] = $result->privateKey;
            }
        }
        $data['address'] = $address;
    } catch (\Exception $e) {
        storeException('get_coin_address', $e->getMessage());
    }
    return $data;
}

// get plan name by amount
function find_plan_by_amount($amount)
{
    $plans = MembershipPlan::where(['status' => STATUS_ACTIVE])->orderBy('amount','asc')->get();
    $plan = "";
    if (isset($plans[0])) {
        foreach ($plans as $pln) {
            if ($amount >= $pln->amount) {
                $plan = $pln;
            }
        }
    }

    return $plan;
}

// get blocked coin
function get_blocked_coin($user_id, $wallet_id = null)
{
    $coin = 0;
    if (isset($wallet_id)) {
        $membership = MembershipClub::where(['user_id' => $user_id, 'wallet_id' => $wallet_id, 'status' => STATUS_ACTIVE])->first();
    } else {
        $membership = MembershipClub::where(['user_id' => $user_id, 'status' => STATUS_ACTIVE])->first();
    }
    if (isset($membership)) {
        $coin = $membership->amount;
    }

    return $coin;
}

// get my plan info
function get_plan_info($user_id)
{
    $data['club_id'] = '';
    $data['plan_id'] = '';
    $data['blocked_coin'] = 0;
    $data['plan_name'] = '';
    $data['plan_image'] = '';
    $club = MembershipClub::where(['status' => STATUS_ACTIVE,'user_id' => $user_id])->first();
    if (isset($club)) {
        $data['club_id'] =  $club->id;
        $data['plan_id'] = $club->plan_id;
        $data['blocked_coin'] = $club->amount;
        if (!empty($club->plan_id)) {
            $data['plan_name'] = $club->plan->plan_name;
            $data['plan_image'] = show_plan_image( $club->plan_id,$club->plan->image);;
        }
    }

    return $data;
}


// check available phase
function checkAvailableBuyPhase()
{
    $activePhases = IcoPhase::where('status', STATUS_ACTIVE)->orderBy('start_date', 'asc')->get();
// dd($activePhases);
    if ( isset($activePhases[0])) {
        $phaseInfo = '';
        $phaseStatus = 0;
        $now = Carbon::now()->format("Y-m-d H:i:s");
        $futureDate = '';

        foreach ($activePhases as $activePhase) {
            if ( ($now >= $activePhase->start_date) && $now <= $activePhase->end_date ) {
                $phaseStatus = 1;
                $phaseInfo = $activePhase;
                break;
            } elseif ( $activePhase->start_date > $now ) {
                $phaseStatus = 2;
                $phaseInfo = '';
                $futureDate = $activePhase->start_date;
                break;
            }
        }

        if ( $phaseStatus == 0 ) {
            return [
                'status' => false
            ];
        } elseif ( $phaseStatus == 1 ) {
            return [
                'status' => true,
                'futurePhase' => false,
                'pahse_info' => $phaseInfo
            ];
        } else {
            return [
                'status' => true,
                'futurePhase' => true,
                'pahse_info' => $phaseInfo,
                'futureDate' => $futureDate
            ];
        }
    }

    return [
        'status' => false
    ];
}

// calculate fees of ico phase
function calculate_phase_percentage($amount, $fees)
{
    $fees = ($amount*$fees)/100;

    return $fees;
}

// check primary wallet
function is_primary_wallet($wallet_id, $coin_type)
{
    $wallets = Wallet::where(['user_id' => Auth::id(), 'coin_type' => $coin_type, 'is_primary'=> 1])->get();
    $this_primary_id = 0;
    $primary = 0;
    if (isset($wallets[0])) {
        foreach ($wallets as $wallet) {
            if ($wallet->id == $wallet_id) {
                $this_primary_id = $wallet->id;
            }
        }
    }
    if ($this_primary_id == $wallet_id) {
        $primary = 1;
    }

    return $primary;

}

// check coin type
function check_coin_type($type)
{
    $coin = Coin::where('type', $type)->first();
    if (isset($coin)) {
        return $coin->type;
    }

    return 'BTC';
}

// find primary wallet
function get_primary_wallet($user_id, $coin_type)
{
    $primaryWallet = Wallet::where(['user_id' => $user_id, 'coin_type' => $coin_type])->first();
    if (isset($primaryWallet)) {
        return $primaryWallet;
    } else {
        $createWallet = Wallet::create(['user_id' => $user_id, 'name' => $coin_type.' Wallet', 'coin_type' => $coin_type, 'is_primary' => 1]);
        return $createWallet;
    }
}

// get user distributed plan bonus
function user_plan_bonus($user_id)
{
    $bonus_amount = 0;
    $bonus = MembershipBonusDistributionHistory::where('user_id', $user_id)->get();
    if (isset($bonus[0])) {
        $bonus_amount = $bonus->sum('bonus_amount_btc');
    }

    return $bonus_amount;
}

if(!function_exists('co_wallet_feature_active')) {
    function co_wallet_feature_active()
    {
        $coWalletFeatureActive = settings(CO_WALLET_FEATURE_ACTIVE_SLUG);
        if($coWalletFeatureActive == STATUS_ACTIVE) return true;
        else return false;
    }
}

function find_coin_type($coin_type)
{
    $type = $coin_type;
    if ($coin_type == 'Default') {
        $type = settings('coin_name');
    }

    return $type;
}

function getUserId(){
    try{
        return auth('api')->user()->id;
    }catch (\Exception $e){
        return 0;
    }
}

function show_image_path($img_name, $path)
{
    $img = asset('assets/img/placeholder-image.png');
    if (!empty($img_name)) {
        $img = asset(path_image().$path.$img_name);
    }

    return $img;
}

function isFeesZero($id, $baseCoin, $tradeCoin, $amount, $orderType, $price = null)
{
    if ($price == null) {
        if ($orderType == 'buy') {
            $repo = new SellOrderRepository(Sell::class);
            $price = $repo->getSellMarketPrice($baseCoin, $tradeCoin, $amount);
        } else {
            $repo = new BuyOrderRepository(Buy::class);
            $price = $repo->getBuyMarketPrice($baseCoin, $tradeCoin, $amount);
        }

        if (calcualte_fee_for_user($id) != 0 && bcmul(bcmul($price, $amount), bcdiv(calcualte_fee_for_user($id), '100')) == 0) {
            return bcdiv('0.00000001', bcmul(bcdiv(calcualte_fee_for_user($id), '100'), $price));
        } else {
            return 0;
        }
    } else {
        if (calcualte_fee_for_user($id) != 0 && bcmul(bcmul($price, $amount), bcdiv(calcualte_fee_for_user($id), '100')) == 0) {
            return bcmul(bcdiv('0.00000001', calcualte_fee_for_user($id)), '100');
        } else {
            return 0;
        }
    }
}

function isFeesZeroForMarket($id,$amount){
    $fees = calcualte_fee_for_user($id);
    if ($fees != 0 && bcmul($amount, bcdiv($fees, '100')) == 0) {
        return bcmul(bcdiv('0.00000001', $fees), '100');
    } else {
        return 0;
    }
}

function calcualte_fee_for_user($id)
{
    $fees = calculated_fee_limit($id);
    return ($fees['maker_fees'] > $fees['taker_fees'] ? $fees['maker_fees'] : $fees['taker_fees']);
}

function calculated_fee_limit($userId)
{
    $query = DB::select("select sum(btc) as total FROM transactions WHERE (buy_user_id = $userId or sell_user_id = $userId) AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $tradeVolume = $query[0]->total;
    $fees = [
        'maker_fees' => 0,
        'taker_fees' => 0,
        'thirtyDayVolume' => $tradeVolume,
    ];

    $limits = AdminSetting::where('slug', 'like', 'trade_limit_%')->get();

    $slugs = [];
    foreach ($limits as $limit) {
        if (bccomp($tradeVolume, $limit->value) !== -1) {
            $slugs[] = 'maker_' . explode('_', $limit->slug)[2];
            $slugs[] = 'taker_' . explode('_', $limit->slug)[2];
            $adminSetting = allsetting($slugs);
            $fees['maker_fees'] = $adminSetting['maker_' . explode('_', $limit->slug)[2]];
            $fees['taker_fees'] = $adminSetting['taker_' . explode('_', $limit->slug)[2]];
        }
    }

    return $fees;
}

function getBtcRate($tradeCoinId)
{
    $btcCoin = Coin::where(['coin_type' => 'BTC'])->first();
    try {
        $dashboardService = new DashboardService();
        //TODO: get BTC Id By Query
        $response = $dashboardService->getLastPriceList($btcCoin->id, $tradeCoinId)->first();
        return $response->price;
    } catch (\Exception $e) {
        return 0;
    }
}



function customEncrypt($string) {
    $key = env('APP_KEY');
    $result = '';
    for($i=0, $k= strlen($string); $i<$k; $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $char = chr(ord($char)+ord($keychar));
        $result .= $char;
    }
    return base64_encode($result);
}

function customDecrypt($string) {
    $key = env('APP_KEY');
    $result = '';
    $string = base64_decode($string);
    for($i=0,$k=strlen($string); $i< $k ; $i++) {
        $char = substr($string, $i, 1);
        $keychar = substr($key, ($i % strlen($key))-1, 1);
        $char = chr(ord($char)-ord($keychar));
        $result.=$char;
    }
    return $result;
}


function getError($e){
    if(env('APP_DEBUG')){
        return " => ".$e->getMessage();
    }
    return '';
}

function arrValueOnly($array, $seperator = ',')
{
    $string = '';
    $sep = '';
    foreach ($array as $key => $val) {
        $string .= $sep . $val;
        $sep = $seperator;
    }
    return $string;
}

function coin_type_restrict_trade($a = null)
{
    $coinService = new CoinService();
    $coinType = $coinService->getCoin(['status' => 1, 'trade_status' => 1])->toArray();
    $a = strtoupper($a);
    if ($a == null) {
        return $coinType;
    } else {
        return isset($coinType[$a]) ? $coinType[$a] : [];
    }
}

function bscointype($coin = null)
{
    $coinService = new CoinService();
    $allCoins = $coinService->getCoin(['status' => 1, 'trade_status' => 1]);
    $baseCoin = array();
    foreach ($allCoins as $coin) {
        if ($coin['is_base'] == '1') {
            array_push($baseCoin, $coin['id']);
        }
    }

    return $baseCoin;
}

function getService($data = [], $appName = null)
{
    try {
        $coinService = new CoinService();
        $response = $coinService->{$data['method']}($data['params']);
        return $response->toArray();
    } catch (\Exception $exception) {
        return false;
    }
}

function getImageUrl($filePath)
{
    return url(Storage::url($filePath));
}

function fixedlenstr($data, $length = 20, $padString = "0", $position = 'left')
{
    if ($position === 'left') {
        $position = STR_PAD_LEFT;
    } elseif ($position === 'right') {
        $position = STR_PAD_RIGHT;
    } else {
        $position = STR_PAD_BOTH;
    }
    return str_pad($data, $length, $padString, $position);
}



function decryptId($encryptedId)
{
    try {
        $id = decrypt($encryptedId);
    } catch (Exception $e) {
        storeException('decryptId',$e->getMessage());
        return ['success' => false];
    }
    return $id;
}

/**
 * Prepare OrderPlace, OrderRemove data for broadcasting and call broadcastData function for broadcast
 * @param $order Object
 * @param $orderType string buy/sell
 * @param $event string
 * @param $userId integer|null
 * @return void
 */
function broadcastOrderData($order, $orderType, $event, $userId = null)
{
    $t = time();

    if ($userId == null) {
        $userId = Auth::id();
    }
    if ($orderType == 'buy') {
        if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev')) {
            $data = DB::table(DB::raw('(select visualNumberFormat(TRUNCATE(price,8)) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total
                                        from buys
                                        where base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . '
                                        group by price
                                        order by price desc )
                                     t1'))
                ->leftJoin(DB::raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from buys where user_id =' . $order->user_id . ' and base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . ' group by price order by price desc) t2'), ['t1.price' => 't2.price'])
                ->select('t1.amount', 't2.amount as my_size')->first();

            $myOrder = Buy::select(DB::raw('visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount'))->where(['user_id' => $userId, 'price' => $order->price, 'base_coin_id' => $order->base_coin_id, 'trade_coin_id' => $order->trade_coin_id, 'status' => 0, 'is_market' => 0])->first();

        }
    } else {
        if (true || (env('APP_ENV') == 'local') || (env('APP_ENV') == 'dev')) {
            $data = DB::table(DB::raw('(select visualNumberFormat(TRUNCATE(price,8)) as price, visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount, visualNumberFormat(TRUNCATE(sum((amount - processed) * price), 8)) as total
                                        from sells
                                        where base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . '
                                        group by price
                                        order by price desc )
                                     t1'))
                ->leftJoin(DB::raw('(select visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount ,price from sells where user_id =' . $order->user_id . ' and base_coin_id = ' . $order->base_coin_id . ' and trade_coin_id = ' . $order->trade_coin_id . ' and  status = 0 and is_market = 0 and deleted_at IS NULL and price = ' . $order->price . ' group by price order by price desc) t2'), ['t1.price' => 't2.price'])
                ->select('t1.amount', 't2.amount as my_size')->first();
            $myOrder = Sell::select(DB::raw('visualNumberFormat(TRUNCATE(sum(amount-processed),8)) as amount'))->where(['user_id' => $userId, 'price' => $order->price, 'base_coin_id' => $order->base_coin_id, 'trade_coin_id' => $order->trade_coin_id, 'status' => 0, 'is_market' => 0])->first();


        }
    }

    $amount = empty($data) ? 0 : $data->amount;
    $mySize = empty($data) ? 0 : $data->my_size;
    $globalData = [
        'orderType' => $orderType,
        'price' => $order->price,
        'amount' => $amount,
        'my_size' => $mySize,
        'total' => bcmul($order->price, $amount),
        'base_coin_id' => $order->base_coin_id,
        'trade_coin_id' => $order->trade_coin_id,
    ];
    broadcastPublic( $event, $globalData);

    $userFees = $order->maker_fees > $order->taker_fees ? $order->maker_fees : $order->taker_fees;
    $fees = bcmul(bcmul(bcmul($order->price, bcsub($order->amount, $order->processed)), $userFees), '0.01');
    $personalData = [
        'orderType' => $orderType,
        'price' => $order->price,
        'my_size' => $myOrder->amount,
        'amount' => bcsub($order->amount, $order->processed),
        'total' => bcmul($order->price, bcsub($order->amount, $order->processed)),
        'base_coin_id' => $order->base_coin_id,
        'trade_coin_id' => $order->trade_coin_id,
        'id' => $order->id,
        'fees' => $fees,
        'created_at' => $order->created_at->toDateTimeString()
    ];

    broadcastPrivate( $event, $personalData, $order->user_id);
}


/**
 * Api Request for Broadcasting.
 * @param $channelName string
 * @param $eventName string
 * @param $broadcastData array
 * @param null $userId integer
 * @return void
 */
function broadcastPublic($eventName, $broadcastData)
{
   // dispatch(new BroadcastJob('collubus-public-chanel', $eventName, $broadcastData))->onQueue('broadcast-data');
}

/**
 * Api Request for Broadcasting.
 * @param $channelName string
 * @param $eventName string
 * @param $broadcastData array
 * @param null $userId integer
 * @return void
 */
function broadcastPrivate( $eventName, $broadcastData, $userId)
{
    $channelName = 'private-'.env("PUSHER_PRIVATE_CHANEL_NAME").'.' . customEncrypt($userId);
//    dispatch(new BroadcastJob($channelName, $eventName, $broadcastData))->onQueue('broadcast-data');
}



/**
 * Broadcast Wallet data after balance Update
 * @param $walletId integer
 * @return void
 */
function broadcastWalletData($walletId, $userId = null)
{

    $walletRepo = new UserWalletRepository(UserWallet::class);
    $wallet = $walletRepo->getById($walletId);
    $repo = new DashboardRepository();
    if ($userId != null) {
        $onOrder = $repo->getOnOrderBalance($wallet->coin_id, $userId);
    } else {
        $onOrder = $repo->getOnOrderBalance($wallet->coin_id);
    }
    $data = [
        'coin_id' => $wallet->coin_id,
        'balance' => $wallet->balance,
        'on_order' => $onOrder
    ];
    broadcastPrivate( 'updateWallet', $data, $wallet->user_id);

}


function check_coin_status($coin, $userId, $amount, $fees = 0)
{
    $data = [
        'success' => true,
        'message' => 'ok',
        'data' => []
    ];
    if(isset($coin)) {
        if($coin->coin_status != STATUS_ACTIVE) {
            $data = [
                'success' => false,
                'message' => $coin->coin_type.__(" coin is inactive right now."),
                'data' => []
            ];
            return $data;
        }
        if($coin->is_withdrawal != STATUS_ACTIVE) {
            $data = [
                'success' => false,
                'message' => $coin->coin_type.__(" coin is not available for withdrawal right now"),
                'data' => []
            ];
            return $data;
        }
        if (($amount + $fees) < $coin->minimum_withdrawal) {
            $data = [
                'success' => false,
                'message' => __('Minimum withdrawal amount ') . $coin->minimum_withdrawal . ' ' . $coin->coin_type,
                'data' => []
            ];
            return $data;
        }
        if (($amount + $fees) > $coin->maximum_withdrawal) {
            $data = [
                'success' => false,
                'message' => __('Maximum withdrawal amount ') . $coin->maximum_withdrawal . ' ' .$coin->coin_type,
                'data' => []
            ];
            return $data;
        }

        $walletBalance = $coin->balance;
        if($walletBalance < ($amount + $fees)) {
            $data = [
                'success' => false,
                'message' => __("Wallet has no enough balance to withdrawal"),
                'data' => []
            ];
            return $data;
        }
    }

    return $data;
}

function check_withdrawal_fees($amount, $fess_percentage)
{
    return ($fess_percentage * $amount) / 100;
}


function get_coin_type($coin_id)
{
    $type = '';
    $coin = Coin::find($coin_id);
    if (isset($coin)) {
        $type = $coin->coin_type;
    }

    return $type;
}

/**
 * broadcast all data like balance update, order delete, Trade History Update after transaction
 * @param $transaction object
 * @return void
 *
 */
function broadcastTransactionData($transaction)
{
    $transaction = Transaction::select('base_coin_id', 'trade_coin_id', 'buy_user_id', 'sell_user_id', DB::raw("visualNumberFormat(amount) as amount"), DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(last_price) as last_price"), DB::raw("visualNumberFormat(total) as total"), 'price_order_type', 'created_at', DB::raw("TIME(created_at) as time"), 'buy_fees', 'sell_fees', 'buy_user_id', 'sell_user_id')->where('id', $transaction->id)->first();

    $coinPairs = CoinPair::select('parent_coin_id', 'child_coin_id', DB::raw("visualNumberFormat(price) as price"), DB::raw("visualNumberFormat(volume) as volume"), DB::raw("`change`"), DB::raw("visualNumberFormat(high) as high"), DB::raw("visualNumberFormat(low) as low"))
        ->where(['parent_coin_id' => $transaction->base_coin_id, 'child_coin_id' => $transaction->trade_coin_id])->first()->toArray();

    $globalData = [
        'price' => $transaction->price,
        'price_order_type' => $transaction->price_order_type,
        'last_price' => $transaction->last_price,
        'amount' => $transaction->amount,
        'total' => $transaction->total,
        'unix_time' => strtotime($transaction->created_at),
        'time' => $transaction->time,
        'created_at' => $transaction->created_at,
        'base_coin_id' => $transaction->base_coin_id,
        'trade_coin_id' => $transaction->trade_coin_id,
        'buy_user_id' => $transaction->buy_user_id,
        'sell_user_id' => $transaction->sell_user_id,
        'buy_fees' => $transaction->buy_fees,
        'sell_fees' => $transaction->sell_fees
    ];
    //TODO: Change To one call replace of multiple
    broadcastPublic( 'transaction', $globalData);
    broadcastPublic( 'twentyFourHoursChange', $coinPairs);
    broadcastPrivate( 'transaction', $globalData, $transaction->buy_user_id);
    if ($transaction->buy_user_id != $transaction->sell_user_id) {
        broadcastPrivate( 'transaction', $globalData, $transaction->sell_user_id);
    }
}

function get_coin_id($coin_type)
{
    $id = 1;
    $coin = Coin::where('coin_type' ,$coin_type)->first();
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_coin_id_test($coin_type)
{
    $id = 0;
    $coin = Coin::where('coin_type' ,$coin_type)->first();
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_default_base_coin_id($coin_type = null)
{
    $id = 2;
    if(isset($coin_type)) {
        $coin = Coin::where(['coin_type' => $coin_type])->first();
    } else {
        $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
        if($pair) {
            $coin = Coin::where(['id' => $pair->parent_coin_id])->first();
        }
    }
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}
function get_default_trade_coin_id($coin_type = null)
{
    $id = 1;
    if(isset($coin_type)) {
        $coin = Coin::where(['coin_type' => $coin_type])->first();
    } else {
        $pair = CoinPair::where(['status' => STATUS_ACTIVE])->first();
        if($pair) {
            $coin = Coin::where(['id' => $pair->child_coin_id])->first();
        }
    }
    if (isset($coin)) {
        $id = $coin->id;
    }

    return $id;
}


function make_unique_slug($title,$table_name = NULL)
{
    $table = array(
        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r', '/' => '-', ' ' => '-'
    );

    // -- Remove duplicated spaces
    $stripped = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $title);

    // -- Returns the slug
    $slug = strtolower(strtr($title, $table));
    $slug = str_replace("?","",$slug);
    if(isset($table_name)) {
        $item = DB::table($table_name)->where('slug',$slug)->first();
        if (isset($item)) {
            $slug = setSlugAttribute($slug, $table_name);
        }
    }

    return $slug;
}

function setSlugAttribute($value, $table)
{

    if (DB::table($table)->where('slug',$value)->exists()) {
        return incrementSlug($value, $table);
    }
    return $value;
}

function incrementSlug($slug,$table)
{
    $original = $slug;
    $count = 2;

    while (DB::table($table)->where('slug', $slug)->exists()) {
        $slug = "{$original}-" . $count++;
    }

    return $slug;
}
function create_coin_wallet($user_id)
{
    $items = getMissingCoinWallet($user_id);
    if (!empty($items)) {
        foreach ($items as $item) {
            storeNewWallet($item);
        }
    }
}

function storeNewWallet($item)
{
    $checkWallet =  Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id'], 'coin_type' => $item['coin_type']])->first();
    if (isset($checkWallet)) {
    } else {
        $checkWalletAgain =  Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
        if (empty($checkWalletAgain)) {
            $againCheck = Wallet::where(['user_id' => $item['user_id'], 'coin_id' => $item['coin_id']])->first();
            if (empty($againCheck)) {
                $a = Wallet::firstOrCreate([
                    'user_id' => $item['user_id'],
                    'coin_id' => $item['coin_id']
                ],[
                    'name' => $item['coin_type'].' wallet',
                    'coin_type' => $item['coin_type']
                ]);
            }
        }
    }
}

function getMissingCoinWallet($user_id)
{
    $coins = Coin::where(['status' => STATUS_ACTIVE])->get();
    $data = [];
    if (isset($coins[0])) {
        foreach ($coins as $coin) {
            $exist = Wallet::where(['user_id' => $user_id, 'coin_id' => $coin->id])->first();
            if(isset($exist)) {
            } else {
                $data[] = [
                    'coin_id' => $coin->id,
                    'coin_type' => $coin->coin_type,
                    'user_id' => $user_id,
                    'name' => $coin->coin_type.' wallet',
                ];
            }
        }
    }
    return $data;
}

function get_wallet_balance_all($user_id, $coin_type = null)
{
    $data['balance'] = 0;
    $data['balance_usd'] = 0;
    if (isset($coin_type) && (!empty($coin_type))) {
        $data['balance'] = Wallet::where(['user_id' => $user_id, 'coin_type' => $coin_type])->sum('balance');
        if ($coin_type == DEFAULT_COIN_TYPE) {
            $data['balance_usd'] = bcmul(settings('coin_price'),$data['balance'],8);
        } else {
            $url = file_get_contents('https://min-api.cryptocompare.com/data/price?fsym='.$coin_type.'&tsyms=USD');
            $data['balance_usd'] = bcmul($data['balance'],  json_decode($url,true)['USD'],8);
        }
    } else {
        $data['balance'] = Wallet::where(['user_id' => $user_id])->sum('balance');
    }
    return $data;
}
function get_coin_icon($icon)
{
    if (!empty($icon)) {
        return assert(IMG_ICON_PATH.'/'.$icon);
    } else {
        return asset('assets/user/images/bitcoin.png');
    }
}

function get_coin_usd_value($amount, $coin_type)
{
    return convert_currency($amount, $to = 'USDT', $coin_type,getUserCurrency());
}

function checkUserKyc($userId, $type, $verificationType)
{
    $response = ['success' => true, 'message' => 'success'];
    if ($type == KYC_DRIVING_REQUIRED) {
        $drive_front = VerificationDetails::where('user_id',$userId)->where('field_name','drive_front')->first();
        $drive_back = VerificationDetails::where('user_id',$userId)->where('field_name','drive_back')->first();
        if((isset($drive_front ) && isset($drive_back)) && (($drive_front->status == STATUS_SUCCESS) && ($drive_back->status == STATUS_SUCCESS))) {
            $response = ['success' => true, 'message' => 'success'];
        } else {
            $response = ['success' => false, 'message' => __('Before ').$verificationType.__(' you must have verified driving licence')];
        }
        return $response;
    } elseif($type == KYC_PASSPORT_REQUIRED) {
        $pass_front = VerificationDetails::where('user_id',$userId)->where('field_name','pass_front')->first();
        $pass_back = VerificationDetails::where('user_id',$userId)->where('field_name','pass_back')->first();
        if((isset($pass_front ) && isset($pass_back)) && (($pass_front->status == STATUS_SUCCESS) && ($pass_back->status == STATUS_SUCCESS))) {
            $response = ['success' => true, 'message' => 'success'];
        } else {
            $response = ['success' => false, 'message' => __('Before ').$verificationType.__(' you must have verified passport')];
        }
        return $response;
    } else {
        $nid_front = VerificationDetails::where('user_id',$userId)->where('field_name','nid_front')->first();
        $nid_back = VerificationDetails::where('user_id',$userId)->where('field_name','nid_back')->first();
        if((isset($nid_front ) && isset($nid_back)) && (($nid_front->status == STATUS_SUCCESS) && ($nid_back->status == STATUS_SUCCESS))) {
            $response = ['success' => true, 'message' => 'success'];
        } else {
            $response = ['success' => false, 'message' => __('Before ').$verificationType.__(' you must have verified NID')];
        }
        return $response;
    }
}

function sendDataThroughWebSocket($channel_name,$event_name,$data) {
    $config = config('broadcasting.connections.pusher');
    $pusher = new Pusher($config['key'], $config['secret'], $config['app_id'], $config['options']);
    $pusher->trigger($channel_name , $event_name, $data);
}

function responseData($status,$message='',$data=[])
{
    $message = !empty($message) ? $message : __('Something went wrong');
    return ['success' => $status,'message' => $message, 'data' => $data];
}

function storeException($type,$message)
{
    $logger = new Logger();
    $logger->log($type,$message);
}

// get wallet personal address
function get_wallet_personal_add($add1,$add2)
{
    $ex = STRONG_KEY.$add1;
    $data = explode($ex,$add2);
    return $data[1];
}


function getUserCurrencyApi()
{
    $data['base_currency'] = 'USD';
    $data['currency'] = 'USD';
    $data['currency_symbol'] = '$';
    $data['currency_rate'] = 1;
    if(Auth::guard('api')->check()) {
        $currency = CurrencyList::where(['code' => auth('api')->user()->currency])->first();
        if($currency) {
            $data['currency'] = $currency->code;
            $data['currency_symbol'] = $currency->symbol;
            $data['currency_rate'] = $currency->rate;
        }
    }
    return $data;
}

function checkWalletBalanceByCoin($coinId)
{
    $data = Wallet::where(['coin_id' => $coinId])->sum('balance');
    return floatval($data) > 0 ? 1 : 0;
}
function checkWalletAddressByCoin($coinType)
{
    return WalletAddressHistory::where(['coin_type' => $coinType])->count();
}
function checkPairByCoin($coinId)
{
    $item = CoinPair::where(['parent_coin_id' => $coinId])->orWhere(['child_coin_id' => $coinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkDepositByCoin($coinType)
{
    $item = DepositeTransaction::where(['coin_type' => $coinType])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkWithdrawalByCoin($coinType)
{
    $item = WithdrawHistory::where(['coin_type' => $coinType])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkBuyByCoin($baseCoinId,$tradeCoinId)
{
    $item = Buy::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkSellByCoin($baseCoinId,$tradeCoinId)
{
    $item = Sell::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}
function checkTransactionByCoin($baseCoinId,$tradeCoinId)
{
    $item = Transaction::where(['base_coin_id' => $baseCoinId, 'trade_coin_id' => $tradeCoinId])->get();
    if (isset($item[0])) {
        return 1;
    }
    return 0;
}

function checkCoinPairDeleteCondition($coinPair)
{
    $response = ['success' => true, 'message' => __('Success')];
    $checkBuy = checkBuyByCoin($coinPair->parent_coin_id,$coinPair->child_coin_id);
    if ($checkBuy > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some buy order, so you should not delete this pair.')];
    }
    $checkSell = checkSellByCoin($coinPair->parent_coin_id,$coinPair->child_coin_id);
    if ($checkSell > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some sell order, so you should not delete this pair.')];
    }
    $checkOrder = checkTransactionByCoin($coinPair->parent_coin_id,$coinPair->child_coin_id);
    if ($checkOrder > 0) {
        return ['success' => false, 'message' => __('This coin pair already have some transaction, so you should not delete this pair.')];
    }

    return $response;
}
function checkCoinDeleteCondition($coin)
{
    $response = ['success' => true, 'message' => __('Success')];
//    $checkCoinWalletBalance = checkWalletBalanceByCoin($coin->id);
//    if ($checkCoinWalletBalance > 0) {
//        return ['success' => false, 'message' => __('This coin wallet already have some balance, so you should not delete this coin.')];
//    }
    $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
    if ($checkCoinWalletAddress > 0) {
        return ['success' => false, 'message' => __('This coin wallet already have some address, so you should not delete this coin.')];
    }
    $checkCoinPair = checkPairByCoin($coin->id);
    if ($checkCoinPair > 0) {
        return ['success' => false, 'message' => __('This coin already have coin pair, so first delete that pair then try again')];
    }
    $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
    if ($checkCoinDeposit > 0) {
        return ['success' => false, 'message' => __('This coin already have some deposit, so you should not delete this coin')];
    }
    $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
    if ($checkCoinWithdrawal > 0) {
        return ['success' => false, 'message' => __('This coin already have some withdrawal, so you should not delete this coin')];
    }
    return $response;
}

function checkCoinTypeUpdateCondition($coin)
{
    $response = ['success' => true, 'message' => __('Success')];
    $checkCoinWalletBalance = checkWalletBalanceByCoin($coin->id);
//    if ($checkCoinWalletBalance > 0) {
//        return ['success' => false, 'message' => __('This coin type wallet already have some balance, so you should not change this coin type.')];
//    }
    $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
    if ($checkCoinWalletAddress > 0) {
        return ['success' => false, 'message' => __('This coin type wallet already have some address, so you should not change this coin type.')];
    }
    $checkCoinPair = checkPairByCoin($coin->id);
    if ($checkCoinPair > 0) {
        return ['success' => false, 'message' => __('This coin type already have coin pair, so you should not change the coin Type, first delete that pair then try again')];
    }
    $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
    if ($checkCoinDeposit > 0) {
        return ['success' => false, 'message' => __('This coin type already have some deposit, so you should not change this coin type')];
    }
    $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
    if ($checkCoinWithdrawal > 0) {
        return ['success' => false, 'message' => __('This coin type already have some withdrawal, so you should not change this coin type')];
    }
    return $response;
}

function checkCoinNetworkUpdateCondition($coin)
{
    $response = ['success' => true, 'message' => __('Success')];
//    $checkCoinWalletBalance = checkWalletBalanceByCoin($coin->id);
//    if ($checkCoinWalletBalance > 0) {
//        return ['success' => false, 'message' => __('This coin network wallet already have some balance, so you should not change this coin network.')];
//    }
    $checkCoinWalletAddress = checkWalletAddressByCoin($coin->coin_type);
    if ($checkCoinWalletAddress > 0) {
        return ['success' => false, 'message' => __('This coin network wallet already have some address, so you should not change this coin network.')];
    }
    $checkCoinDeposit = checkDepositByCoin($coin->coin_type);
    if ($checkCoinDeposit > 0) {
        return ['success' => false, 'message' => __('This coin network already have some deposit, so you should not change this coin network')];
    }
    $checkCoinWithdrawal = checkWithdrawalByCoin($coin->coin_type);
    if ($checkCoinWithdrawal > 0) {
        return ['success' => false, 'message' => __('This coin network already have some withdrawal, so you should not change this coin network')];
    }
    return $response;
}

function checkPair($baseCoinId,$tradeCoinId)
{
    $pair = CoinPair::where(['parent_coin_id' => $baseCoinId, 'child_coin_id' => $tradeCoinId, 'status' => STATUS_ACTIVE])->first();
    if ($pair) {
        return true;
    }
    return false;
}
