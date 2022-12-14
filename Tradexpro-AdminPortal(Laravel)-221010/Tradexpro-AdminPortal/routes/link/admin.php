<?php

Route::group(['prefix'=>'admin','namespace'=>'admin','middleware'=> ['auth','admin','default_lang']],function () {

    // Logs
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->name('adminLogs');


    Route::get('dashboard', 'DashboardController@adminDashboard')->name('adminDashboard');

    // user management
    Route::get('profile', 'DashboardController@adminProfile')->name('adminProfile');
    Route::post('user-profile-update', 'DashboardController@UserProfileUpdate')->name('UserProfileUpdate');
    Route::post('upload-profile-image', 'DashboardController@uploadProfileImage')->name('uploadProfileImage');
    Route::get('users', 'UserController@adminUsers')->name('adminUsers');
    Route::get('user-profile', 'UserController@adminUserProfile')->name('adminUserProfile');
    Route::get('user-add', 'UserController@UserAddEdit')->name('admin.UserAddEdit');
    Route::get('user-edit', 'UserController@UserEdit')->name('admin.UserEdit');
    Route::get('user-delete-{id}', 'UserController@adminUserDelete')->name('admin.user.delete');
    Route::get('user-suspend-{id}', 'UserController@adminUserSuspend')->name('admin.user.suspend');
    Route::get('user-active-{id}', 'UserController@adminUserActive')->name('admin.user.active');
    Route::get('user-remove-gauth-set-{id}', 'UserController@adminUserRemoveGauth')->name('admin.user.remove.gauth');
    Route::get('user-email-verify-{id}', 'UserController@adminUserEmailVerified')->name('admin.user.email.verify');
    Route::get('user-phone-verify-{id}', 'UserController@adminUserPhoneVerified')->name('admin.user.phone.verify');
    Route::get('deleted-users', 'UserController@adminDeletedUser')->name('adminDeletedUser');

    // ID Varification
    Route::get('verification-details-{id}', 'UserController@VerificationDetails')->name('adminUserDetails');
    Route::get('pending-id-verified-user', 'UserController@adminUserIdVerificationPending')->name('adminUserIdVerificationPending');
    Route::get('verification-active-{id}-{type}', 'UserController@adminUserVerificationActive')->name('adminUserVerificationActive');
    Route::get('verification-reject', 'UserController@varificationReject')->name('varificationReject');

    // coin
    Route::get('coin-list', 'CoinController@adminCoinList')->name('adminCoinList');
    Route::get('add-new-coin', 'CoinController@adminAddCoin')->name('adminAddCoin');
    Route::post('save-new-coin', 'CoinController@adminSaveCoin')->name('adminSaveCoin');
    Route::get('coin-delete/{id}', 'CoinController@adminCoinDelete')->name('adminCoinDelete');
    Route::get('coin-edit/{id}', 'CoinController@adminCoinEdit')->name('adminCoinEdit');
    Route::get('coin-settings/{id}', 'CoinController@adminCoinSettings')->name('adminCoinSettings');
    Route::post('save-coin-settings', 'CoinController@adminSaveCoinSetting')->name('adminSaveCoinSetting');
    Route::post('coin-save-process', 'CoinController@adminCoinSaveProcess')->name('adminCoinSaveProcess');
    Route::post('change-coin-status', 'CoinController@adminCoinStatus')->name('adminCoinStatus');
    Route::get('adjust-bitgo-wallet/{id}', 'CoinController@adminAdjustBitgoWallet')->name('adminAdjustBitgoWallet');
    Route::get('change-coin-rate', 'CoinController@adminCoinRate')->name('adminCoinRate');

    // Wallet management
    Route::get('wallet-list', 'WalletController@adminWalletList')->name('adminWalletList');
    Route::get('send-coin-list', 'WalletController@adminWalletSendList')->name('adminWalletSendList');
    Route::get('send-wallet-balance', 'WalletController@adminSendWallet')->name('adminSendWallet');
    Route::post('admin-send-balance-process', 'WalletController@adminSendBalanceProcess')->name('adminSendBalanceProcess');

    // deposit withdrawal
    Route::get('transaction-history', 'TransactionController@adminTransactionHistory')->name('adminTransactionHistory');
    Route::get('withdrawal-history', 'TransactionController@adminWithdrawalHistory')->name('adminWithdrawalHistory');
    Route::get('pending-withdrawal', 'TransactionController@adminPendingWithdrawal')->name('adminPendingWithdrawal');
    Route::get('rejected-withdrawal', 'TransactionController@adminRejectedWithdrawal')->name('adminRejectedWithdrawal');
    Route::get('active-withdrawal', 'TransactionController@adminActiveWithdrawal')->name('adminActiveWithdrawal');
    Route::get('pending-withdrawal-accept-process', 'TransactionController@adminPendingWithdrawalAcceptProcess')->name('adminPendingWithdrawalAcceptProcess');
    Route::get('accept-pending-withdrawal-{id}', 'TransactionController@adminAcceptPendingWithdrawal')->name('adminAcceptPendingWithdrawal');
    Route::get('reject-pending-withdrawal-{id}', 'TransactionController@adminRejectPendingWithdrawal')->name('adminRejectPendingWithdrawal');

    // pending deposit report and action
    Route::get('gas-send-history', 'DepositController@adminGasSendHistory')->name('adminGasSendHistory');
    Route::get('token-receive-history', 'DepositController@adminTokenReceiveHistory')->name('adminTokenReceiveHistory');
    Route::get('pending-token-deposit-history', 'DepositController@adminPendingDepositHistory')->name('adminPendingDepositHistory');
    Route::get('pending-token-deposit-accept-{id}', 'DepositController@adminPendingDepositAccept')->name('adminPendingDepositAccept');
    Route::get('pending-token-deposit-reject-{id}', 'DepositController@adminPendingDepositReject')->name('adminPendingDepositReject');

    //FAQ
    Route::get('faq-list', 'SettingsController@adminFaqList')->name('adminFaqList');
    Route::get('faq-add', 'SettingsController@adminFaqAdd')->name('adminFaqAdd');
    Route::post('faq-save', 'SettingsController@adminFaqSave')->name('adminFaqSave');
    Route::get('faq-edit-{id}', 'SettingsController@adminFaqEdit')->name('adminFaqEdit');
    Route::get('faq-delete-{id}', 'SettingsController@adminFaqDelete')->name('adminFaqDelete');

    // admin setting
    Route::get('general-settings', 'SettingsController@adminSettings')->name('adminSettings');
    Route::get('api-settings', 'SettingsController@adminCoinApiSettings')->name('adminCoinApiSettings');
    Route::post('common-settings', 'SettingsController@adminCommonSettings')->name('adminCommonSettings');
    Route::post('save-payment-settings', 'SettingsController@adminSavePaymentSettings')->name('adminSavePaymentSettings');
    Route::post('save-bitgo-settings', 'SettingsController@adminSaveBitgoSettings')->name('adminSaveBitgoSettings');
    Route::post('email-save-settings', 'SettingsController@adminSaveEmailSettings')->name('adminSaveEmailSettings');
    Route::post('sms-save-settings', 'SettingsController@adminSaveSmsSettings')->name('adminSaveSmsSettings');
    Route::post('referral-fees-settings', 'SettingsController@adminReferralFeesSettings')->name('adminReferralFeesSettings');
    Route::post('withdrawal-settings', 'SettingsController@adminWithdrawalSettings')->name('adminWithdrawalSettings');
    Route::post('recaptcha-settings', 'SettingsController@adminCapchaSettings')->name('adminCapchaSettings');
    Route::post('node-settings', 'SettingsController@adminNodeSettings')->name('adminNodeSettings');
    Route::post('order-settings', 'SettingsController@adminOrderSettings')->name('adminOrderSettings');
    Route::post('admin-other-api-settings', 'SettingsController@adminSaveOtherApiSettings')->name('adminSaveOtherApiSettings');
    Route::post('admin-stripe-api-settings', 'SettingsController@adminSaveStripeApiSettings')->name('adminSaveStripeApiSettings');
    Route::post('admin-erc20-api-settings', 'SettingsController@adminSaveERC20ApiSettings')->name('adminSaveERC20ApiSettings');
    Route::get('admin-feature-settings', 'SettingsController@adminFeatureSettings')->name('adminFeatureSettings');
    Route::get('admin-chat-settings', 'SettingsController@adminChatSettings')->name('adminChatSettings');
    Route::post('admin-cookie-settings-save', 'SettingsController@adminCookieSettingsSave')->name('adminCookieSettingsSave');

   // notification
    Route::get('send-email', 'DashboardController@sendEmail')->name('sendEmail');
    Route::get('clear-email', 'DashboardController@clearEmailRecord')->name('clearEmailRecord');
    Route::get('send-notification', 'DashboardController@sendNotification')->name('sendNotification');
    Route::post('send-notification-process', 'DashboardController@sendNotificationProcess')->name('sendNotificationProcess');
    Route::post('send-email-process', 'DashboardController@sendEmailProcess')->name('sendEmailProcess');

    // custom page
    Route::get('custom-page-slug-check', 'LandingController@customPageSlugCheck')->name('customPageSlugCheck');
    Route::get('custom-page-list', 'LandingController@adminCustomPageList')->name('adminCustomPageList');
    Route::get('custom-page-add', 'LandingController@adminCustomPageAdd')->name('adminCustomPageAdd');
    Route::get('custom-page-edit/{id}', 'LandingController@adminCustomPageEdit')->name('adminCustomPageEdit');
    Route::get('custom-page-delete/{id}', 'LandingController@adminCustomPageDelete')->name('adminCustomPageDelete');
    Route::get('custom-page-order', 'LandingController@customPageOrder')->name('customPageOrder');
    Route::post('custom-page-save', 'LandingController@adminCustomPageSave')->name('adminCustomPageSave');

    // landing setting
    Route::get('landing-page-setting', 'LandingController@adminLandingSetting')->name('adminLandingSetting');
    Route::post('landing-page-setting-save', 'LandingController@adminLandingSettingSave')->name('adminLandingSettingSave');
    Route::post('landing-api-link-setting-save', 'LandingController@adminLandingApiLinkSave')->name('adminLandingApiLinkSave');

    Route::get('admin-config', 'ConfigController@adminConfiguration')->name('adminConfiguration');
    Route::get('run-admin-command/{type}', 'ConfigController@adminRunCommand')->name('adminRunCommand');

    // trade
    Route::get('trade/coin-pairs', 'TradeSettingController@coinPairs')->name('coinPairs');
    Route::get('trade/coin-pairs-delete/{id}', 'TradeSettingController@coinPairsDelete')->name('coinPairsDelete');
    Route::get('trade/coin-pairs-chart-update/{id}', 'TradeSettingController@coinPairsChartUpdate')->name('coinPairsChartUpdate');
    Route::post('trade/save-coin-pair', 'TradeSettingController@saveCoinPairSettings')->name('saveCoinPairSettings');
    Route::post('trade/change-coin-pair-status', 'TradeSettingController@changeCoinPairStatus')->name('changeCoinPairStatus');
    Route::get('trade/trade-fees-settings', 'TradeSettingController@tradeFeesSettings')->name('tradeFeesSettings');
    Route::post('trade/save-trade-fees-settings', 'TradeSettingController@tradeFeesSettingSave')->name('tradeFeesSettingSave');
    Route::post('trade/remove-trade-limit', 'TradeSettingController@removeTradeLimit')->name('removeTradeLimit');

    // trade reports
    Route::get('all-buy-orders-history', 'ReportController@adminAllOrdersHistoryBuy')->name('adminAllOrdersHistoryBuy');
    Route::get('all-sell-orders-history', 'ReportController@adminAllOrdersHistorySell')->name('adminAllOrdersHistorySell');
    Route::get('all-stop-limit-orders-history', 'ReportController@adminAllOrdersHistoryStopLimit')->name('adminAllOrdersHistoryStopLimit');
    Route::get('all-transaction-history', 'ReportController@adminAllTransactionHistory')->name('adminAllTransactionHistory');

    // landing banner
    Route::get('landing-banner-list', 'BannerController@adminBannerList')->name('adminBannerList');
    Route::get('landing-banner-add', 'BannerController@adminBannerAdd')->name('adminBannerAdd');
    Route::post('landing-banner-save', 'BannerController@adminBannerSave')->name('adminBannerSave');
    Route::get('landing-banner-edit-{id}', 'BannerController@adminBannerEdit')->name('adminBannerEdit');
    Route::get('landing-banner-delete-{id}', 'BannerController@adminBannerDelete')->name('adminBannerDelete');

    // landing announcement
    Route::get('landing-announcement-list', 'AnnouncementController@adminAnnouncementList')->name('adminAnnouncementList');
    Route::get('landing-announcement-add', 'AnnouncementController@adminAnnouncementAdd')->name('adminAnnouncementAdd');
    Route::post('landing-announcement-save', 'AnnouncementController@adminAnnouncementSave')->name('adminAnnouncementSave');
    Route::get('landing-announcement-edit-{id}', 'AnnouncementController@adminAnnouncementEdit')->name('adminAnnouncementEdit');
    Route::get('landing-announcement-delete-{id}', 'AnnouncementController@adminAnnouncementDelete')->name('adminAnnouncementDelete');

    // landing feature
    Route::get('landing-feature-list', 'LandingController@adminFeatureList')->name('adminFeatureList');
    Route::get('landing-feature-add', 'LandingController@adminFeatureAdd')->name('adminFeatureAdd');
    Route::post('landing-feature-save', 'LandingController@adminFeatureSave')->name('adminFeatureSave');
    Route::get('landing-feature-edit-{id}', 'LandingController@adminFeatureEdit')->name('adminFeatureEdit');
    Route::get('landing-feature-delete-{id}', 'LandingController@adminFeatureDelete')->name('adminFeatureDelete');

    // landing social media
    Route::get('landing-social-media-list', 'LandingController@adminSocialMediaList')->name('adminSocialMediaList');
    Route::get('landing-social-media-add', 'LandingController@adminSocialMediaAdd')->name('adminSocialMediaAdd');
    Route::post('landing-social-media-save', 'LandingController@adminSocialMediaSave')->name('adminSocialMediaSave');
    Route::get('landing-social-media-edit-{id}', 'LandingController@adminSocialMediaEdit')->name('adminSocialMediaEdit');
    Route::get('landing-social-media-delete-{id}', 'LandingController@adminSocialMediaDelete')->name('adminSocialMediaDelete');

    // currency list
    Route::get('currency-list', 'CurrencyController@adminCurrencyList')->name('adminCurrencyList');
    Route::get('currency-add', 'CurrencyController@adminCurrencyAdd')->name('adminCurrencyAdd');
    Route::get('currency-edit-{id}', 'CurrencyController@adminCurrencyEdit')->name('adminCurrencyEdit');
    Route::post('currency-save-process', 'CurrencyController@adminCurrencyAddEdit')->name('adminCurrencyStore');
    Route::post('currency-status-change', 'CurrencyController@adminCurrencyStatus')->name('adminCurrencyStatus');
    Route::get('currency-rate-change', 'CurrencyController@adminCurrencyRate')->name('adminCurrencyRate');
    Route::post('currency-all', 'CurrencyController@adminAllCurrency')->name('adminAllCurrency');

    // landing social media
    Route::get('lang-list', 'AdminLangController@adminLanguageList')->name('adminLanguageList');
    Route::get('lang-add', 'AdminLangController@adminLanguageAdd')->name('adminLanguageAdd');
    Route::post('lang-save', 'AdminLangController@adminLanguageSave')->name('adminLanguageSave');
    Route::get('lang-edit-{id}', 'AdminLangController@adminLanguageEdit')->name('adminLanguageEdit');
    Route::get('lang-delete-{id}', 'AdminLangController@adminLanguageDelete')->name('adminLanguageDelete');
    Route::post('lang-status-change', 'AdminLangController@adminLangStatus')->name('adminLangStatus');

    //Bank settings
    Route::get('bank-list', 'BankController@bankList')->name('bankList');
    Route::get('bank-add', 'BankController@bankAdd')->name('bankAdd');
    Route::post('bank-save', 'BankController@bankStore')->name('bankStore');
    Route::post('bank-status-change', 'BankController@bankStatusChange')->name('bankStatusChange');
    Route::get('bank-delete-{id}', 'BankController@bankDelete')->name('bankDelete');
    Route::get('bank-edit-{id}', 'BankController@bankEdit')->name('bankEdit');

    //currency deposit Payment payment method
    Route::get('currency-payment-method','PaymentMethodController@currencyPaymentMethod')->name('currencyPaymentMethod');
    Route::get('currency-payment-method-add','PaymentMethodController@currencyPaymentMethodAdd')->name('currencyPaymentMethodAdd');
    Route::post('currency-payment-method-store','PaymentMethodController@currencyPaymentMethodStore')->name('currencyPaymentMethodStore');
    Route::post('currency-payment-method-status','PaymentMethodController@currencyPaymentMethodStatus')->name('currencyPaymentMethodStatus');
    Route::get('currency-payment-method-delete-{id}','PaymentMethodController@currencyPaymentMethodDelete')->name('currencyPaymentMethodDelete');
    Route::get('currency-payment-method-edit-{id}','PaymentMethodController@currencyPaymentMethodEdit')->name('currencyPaymentMethodEdit');

    // currency deposit
    Route::get('currency-deposit-list','CurrencyDepositController@currencyDepositList')->name('currencyDepositList');
    Route::get('currency-deposit-pending-list','CurrencyDepositController@currencyDepositPendingList')->name('currencyDepositPendingList');
    Route::get('currency-deposit-accept-list','CurrencyDepositController@currencyDepositAcceptList')->name('currencyDepositAcceptList');
    Route::get('currency-deposit-reject-list','CurrencyDepositController@currencyDepositRejectList')->name('currencyDepositRejectList');
    Route::get('currency-deposit-accept-{id}','CurrencyDepositController@currencyDepositAccept')->name('currencyDepositAccept');
    Route::post('currency-deposit-reject','CurrencyDepositController@currencyDepositReject')->name('currencyDepositReject');
    
    //country
    Route::get('country-list','CountryController@countryList')->name('countryList');
    Route::post('country-status-change','CountryController@countryStatusChange')->name('countryStatusChange');

    Route::post('send_test_mail','SettingsController@testMail')->name('testmailsend');

});
Route::group(['middleware'=> ['auth', 'lang']], function () {
    Route::post('/upload-profile-image', 'user\ProfileController@uploadProfileImage')->name('uploadProfileImage');
    Route::post('/user-profile-update', 'user\ProfileController@userProfileUpdate')->name('userProfileUpdate');
    Route::post('/phone-verify', 'user\ProfileController@phoneVerify')->name('phoneVerify');
    Route::get('/send-sms-for-verification', 'user\ProfileController@sendSMS')->name('sendSMS');
    Route::post('change-password-save', 'user\ProfileController@changePasswordSave')->name('changePasswordSave');


});
