<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace'=>'Api\Public', 'prefix' => 'v1/markets'], function () {
    Route::get('price/{pair?}', 'PublicController@getExchangePrice')->name('getExchangeTrade');
    Route::get('orderBook/{pair}', 'PublicController@getExchangeOrderBook')->name('getExchangeOrderBook');
    Route::get('trade/{pair}', 'PublicController@getExchangeTrade')->name('getExchangeTrade');
    Route::get('chart/{pair}', 'PublicController@getExchangeChart')->name('getExchangeChart');
});

Route::post('/coin-payment-notifier', 'Api\WalletNotifier@coinPaymentNotifier')->name('coinPaymentNotifier');
Route::post('bitgo-wallet-webhook','Api\WalletNotifier@bitgoWalletWebhook')->name('bitgoWalletWebhook');

Route::group(['namespace'=>'Api', 'middleware' => 'wallet_notify'], function (){
    Route::post('wallet-notifier','WalletNotifier@walletNotify');
    Route::post('wallet-notifier-confirm','WalletNotifier@notifyConfirm');
});


Route::group(['middleware' => ['checkApi']], function () {
    Route::group(['namespace'=>'Api', 'middleware' => []], function () {
        // auth
        Route::get('common-settings', 'LandingController@commonSettings');
        Route::post('sign-up', 'AuthController@signUp');
        Route::post('sign-in', 'AuthController@signIn');
        Route::post('verify-email', 'AuthController@verifyEmail');
        Route::post('forgot-password', 'AuthController@forgotPassword');
        Route::post('reset-password', 'AuthController@resetPassword');
        Route::post('g2f-verify', 'AuthController@g2fVerify');
        Route::get('landing', 'LandingController@index');
        Route::get('banner-list/{id?}', 'LandingController@bannerList');
        Route::get('announcement-list/{id?}', 'LandingController@announcementList');
        Route::get('feature-list/{id?}', 'LandingController@featureList');
        Route::get('social-media-list/{id?}', 'LandingController@socialMediaList');
        Route::get('recaptcha-settings', 'LandingController@reCaptchaSettings');
        Route::get('custom-pages/{type?}', 'LandingController@getCustomPageList');
        Route::get('pages-details/{slug}', 'LandingController@getCustomPageDetails');

    });
    Route::group(['namespace'=>'Api\User', 'middleware' => []], function () {
        Route::get('get-exchange-all-orders-app', 'ExchangeController@getExchangeAllOrdersApp')->name('getExchangeAllOrdersApp');
        Route::get('app-get-pair', 'ExchangeController@appExchangeGetAllPair')->name('appExchangeGetAllPair');
        Route::get('app-dashboard/{pair?}', 'ExchangeController@appExchangeDashboard')->name('appExchangeDashboard');
        Route::get('get-exchange-market-trades-app', 'ExchangeController@getExchangeMarketTradesApp')->name('getExchangeMarketTradesApp');
        Route::get('get-exchange-chart-data-app', 'ExchangeController@getExchangeChartDataApp')->name('getExchangeChartDataApp');

    });

    Route::group(['namespace'=>'Api', 'middleware' => ['auth:api']], function () {
        //logout
        Route::post('log-out-app','AuthController@logOutApp')->name('logOutApp');
    });

    Route::group(['namespace'=>'Api\User', 'middleware' => ['auth:api','api-user']], function () {
        // profile
        Route::get('profile', 'ProfileController@profile');
        Route::get('notifications', 'ProfileController@userNotification');
        Route::post('notification-seen', 'ProfileController@userNotificationSeen');
        Route::get('activity-list', 'ProfileController@activityList');
        Route::post('update-profile', 'ProfileController@updateProfile');
        Route::post('change-password', 'ProfileController@changePassword');

        // kyc
        Route::post('send-phone-verification-sms', 'ProfileController@sendPhoneVerificationSms');
        Route::post('phone-verify', 'ProfileController@phoneVerifyProcess');
        Route::post('upload-nid', 'ProfileController@uploadNid');
        Route::post('upload-passport', 'ProfileController@uploadPassport');
        Route::post('upload-driving-licence', 'ProfileController@uploadDrivingLicence');
        Route::get('kyc-details', 'ProfileController@kycDetails');
        Route::get('user-setting', 'ProfileController@userSetting');
        Route::get('language-list', 'ProfileController@languageList');
        Route::post('google2fa-setup', 'ProfileController@google2faSetup');
        Route::post('language-setup', 'ProfileController@languageSetup');
        Route::get('setup-google2fa-login', 'ProfileController@setupGoogle2faLogin');
        Route::post('update-currency', 'ProfileController@updateFiatCurrency');

        // coin
        Route::get('get-coin-list','CoinController@getCoinList');
        Route::get('get-coin-pair-list','CoinController@getCoinPairList');

        // wallet
        Route::get('wallet-list','WalletController@walletList');
        Route::get('wallet-deposit-{id}','WalletController@walletDeposit');
        Route::get('wallet-withdrawal-{id}','WalletController@walletWithdrawal');
        Route::post('wallet-withdrawal-process','WalletController@walletWithdrawalProcess');
        Route::post('get-wallet-network-address','WalletController@getWalletNetworkAddress');

        //Dashboard and reports
        Route::get('get-all-buy-orders-app', 'ExchangeController@getExchangeAllBuyOrdersApp')->name('getExchangeAllBuyOrdersApp');
        Route::get('get-all-sell-orders-app', 'ExchangeController@getExchangeAllSellOrdersApp')->name('getExchangeAllSellOrdersApp');

        Route::get('get-my-all-orders-app', 'ExchangeController@getMyExchangeOrdersApp')->name('getMyExchangeOrdersApp');
        Route::get('get-my-trades-app', 'ExchangeController@getMyExchangeTradesApp')->name('getMyExchangeTradesApp');
        Route::post('cancel-open-order-app', 'ExchangeController@deleteMyOrderApp')->name('deleteMyOrderApp');
        Route::get('all-buy-orders-history-app', 'ReportController@getAllOrdersHistoryBuyApp')->name('getAllOrdersHistoryBuyApp');
        Route::get('all-sell-orders-history-app', 'ReportController@getAllOrdersHistorySellApp')->name('getAllOrdersHistorySellApp');
        Route::get('all-transaction-history-app', 'ReportController@getAllTransactionHistoryApp')->name('getAllTransactionHistoryApp');

        Route::get('wallet-history-app', 'WalletController@walletHistoryApp')->name('walletHistoryApp');
        Route::group(['middleware' => ['checkSwap']], function () {
            Route::get('swap-coin-details-app', 'WalletController@getCoinSwapDetailsApp')->name('getCoinSwapDetailsApp');
            Route::get('get-rate-app', 'WalletController@getRateApp')->name('getRateApp');
            Route::get('coin-swap-app', 'WalletController@coinSwapApp')->name('coinSwapApp');
            Route::post('swap-coin-app', 'WalletController@swapCoinApp')->name('swapCoinApp');
            Route::get('coin-convert-history-app', 'WalletController@coinSwapHistoryApp')->name('coinSwapHistoryApp');
        });

        Route::get('referral-app', 'ProfileController@myReferralApp')->name('myReferralApp');

        Route::post('buy-limit-app', "BuyOrderController@placeBuyLimitOrderApp")->name('placeBuyLimitOrderApp');
        Route::post('buy-market-app', "BuyOrderController@placeBuyMarketOrderApp")->name('placeBuyMarketOrderApp');
        Route::post('buy-stop-limit-app', "BuyOrderController@placeBuyStopLimitOrderApp")->name('placeBuyStopLimitOrderApp');
        Route::post('sell-limit-app', "SellOrderController@placeSellLimitOrderApp")->name('placeSellLimitOrderApp');
        Route::post('sell-market-app', "SellOrderController@placeSellMarketOrderApp")->name('placeSellMarketOrderApp');
        Route::post('sell-stop-limit-app', "SellOrderController@placeStopLimitSellOrderApp")->name('placeStopLimitSellOrderApp');

        Route::group(['middleware' => ['checkCurrencyDeposit']], function () {
            Route::get('deposit-bank-details/{id}', 'DepositController@depositBankDetails')->name('depositBankDetails');
            Route::get('currency-deposit', 'DepositController@currencyDepositInfo')->name('currencyDepositInfo');
            Route::post('get-currency-deposit-rate', 'DepositController@currencyDepositRate')->name('currencyDepositRate');
            Route::post('currency-deposit-process', 'DepositController@currencyDepositProcess')->name('currencyDepositProcess');
            Route::get('currency-deposit-history', 'DepositController@currencyDepositHistory')->name('currencyDepositHistory');
        });
    });
});

