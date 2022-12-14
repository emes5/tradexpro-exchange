<?php


// User Role Type

const USER_ROLE_ADMIN = 1;
const USER_ROLE_USER = 2;



// Status
const STATUS_PENDING = 0;
const STATUS_ACCEPTED = 1;
const STATUS_REJECTED = 2;
const STATUS_SUCCESS = 1;
const STATUS_SUSPENDED = 4;
const STATUS_DELETED = 5;
const STATUS_ALL = 6;

const STATUS_ACTIVE = 1;
const STATUS_DEACTIVE = 0;

const BTC = 1;
const CARD = 2;
const PAYPAL = 3;
const BANK_DEPOSIT = 4;
const STRIPE = 5;
const WALLET_DEPOSIT = 6;


const  SEND_FEES_FIXED  = 1;
const  SEND_FEES_PERCENTAGE  = 2;

//Varification send Type
const Mail = 1;
const PHONE = 2;


const IOS = 1;
const ANDROIND = 2;

// User Activity
const ADDRESS_TYPE_EXTERNAL = 1;
const ADDRESS_TYPE_INTERNAL = 2;

const IMG_PATH = 'uploaded_file/uploads/';
const IMG_VIEW_PATH = 'uploaded_file/uploads/';

const IMG_OTHER_PATH = 'uploaded_file/others/';
const IMG_USER_PATH = 'uploaded_file/users/';
const IMG_ICON_PATH = 'uploaded_file/uploads/coin/';
const IMG_SLEEP_PATH = 'uploaded_file/sleep/';
const IMG_USER_VIEW_PATH = 'uploaded_file/users/';
const IMG_SLEEP_VIEW_PATH = 'uploaded_file/sleep/';
const IMG_USER_VERIFICATION_PATH = 'users/verifications/';

const DISCOUNT_TYPE_FIXED = 1;
const DISCOUNT_TYPE_PERCENTAGE = 2;

const DEPOSIT = 1;
const WITHDRAWAL = 2;

const PAYMENT_TYPE_BTC = 1;
const PAYMENT_TYPE_USD = 2;
const PAYMENT_TYPE_ETH = 3;
const PAYMENT_TYPE_LTC = 4;
const PAYMENT_TYPE_LTCT = 5;
const PAYMENT_TYPE_DOGE = 6;
const PAYMENT_TYPE_BCH = 7;
const PAYMENT_TYPE_DASH = 8;
const PAYMENT_TYPE_USDT = 9;
// plan bonus
const PLAN_BONUS_TYPE_FIXED = 1;
const PLAN_BONUS_TYPE_PERCENTAGE = 2;

//
const CREDIT = 1;
const DEBIT = 2;

//User Activity
const USER_ACTIVITY_LOGIN=1;
const USER_ACTIVITY_MOVE_COIN=2;
const USER_ACTIVITY_WITHDRAWAL=3;
const USER_ACTIVITY_CREATE_WALLET=4;
const USER_ACTIVITY_CREATE_ADDRESS=5;
const USER_ACTIVITY_MAKE_PRIMARY_WALLET=6;
const USER_ACTIVITY_PROFILE_IMAGE_UPLOAD=7;
const USER_ACTIVITY_UPDATE_PASSWORD=8;
const USER_ACTIVITY_UPDATE_EMAIL=12;
const USER_ACTIVITY_ACTIVE=9;
const USER_ACTIVITY_HALF_ACTIVE=10;
const USER_ACTIVITY_INACTIVE=11;
const USER_ACTIVITY_LOGOUT=12;
const USER_ACTIVITY_PROFILE_UPDATE=13;

const DEFAULT_COIN_TYPE="Default";

//wallet types
const PERSONAL_WALLET = 1;
const CO_WALLET = 2;
const COMMAND_TYPE_CACHE = 1;
const COMMAND_TYPE_CONFIG = 2;
const COMMAND_TYPE_ROUTE = 3;
const COMMAND_TYPE_VIEW = 4;
const COMMAND_TYPE_WALLET = 5;
const COMMAND_TYPE_TRADE_FEES = 6;
const COMMAND_TYPE_MIGRATE = 7;
const COMMAND_TYPE_COIN_PAIR = 8;
const COMMAND_TYPE_TOKEN_DEPOSIT = 10;
const COMMAND_TYPE_ADJUST_TOKEN_DEPOSIT = 11;
const COMMAND_TYPE_ERC20_TOKEN_DEPOSIT = 12;


//co wallet feature's admin settings
const CO_WALLET_FEATURE_ACTIVE_SLUG = 'co_wallet_feature_active';
const MAX_CO_WALLET_USER_SLUG = 'max_co_wallet_user';
const CO_WALLET_WITHDRAWAL_USER_APPROVAL_PERCENTAGE_SLUG = 'co_wallet_withdrawal_user_approval_percentage';

const CHECK_STATUS = 1;
const CHECK_WITHDRAWAL_STATUS = 2;
const CHECK_WITHDRAWAL_FEES = 3;
const CHECK_MINIMUM_WITHDRAWAL = 4;
const CHECK_MAXIMUM_WITHDRAWAL = 5;

const NOT_DEFINED = 0;
const COIN_PAYMENT = 1;
const BITCOIN_API = 2;
const BITGO_API = 3;
const ERC20_TOKEN = 4;
const BEP20_TOKEN = 5;

const ORDER_TYPE_BUY = 'buy';
const ORDER_TYPE_SELL = 'sell';
const ORDER_TYPE_BUY_SELL = 'buy_sell';

const DASHBOARD_TYPE = 'dashboard';

const GENDER_MALE = 1;
const GENDER_FEMALE = 2;
const GENDER_OTHERS = 3;

const CODE_TYPE_EMAIL = 1;
const CODE_TYPE_PHONE = 2;

const KYC_NOT_SUBMITTED = '';
const KYC_PENDING = 0;
const KYC_APPROVED = 1;
const KYC_REJECTED = 2;

const KYC_NID_REQUIRED = 1;
const KYC_PASSPORT_REQUIRED = 2;
const KYC_DRIVING_REQUIRED = 3;

const PAGE_TYPE_PRODUCT = 1;
const PAGE_TYPE_SERVICE = 2;
const PAGE_TYPE_SUPPORT = 3;

const STRONG_KEY = 'aBf5HIk4aD2lW0Q';

const USDT_OMNILAYER = 'USDT';
const USDT_SOLANA = 'USDT.SOL';
const USDT_TRC20 = 'USDT.TRC20';
const USDT_ERC20 = 'USDT.ERC20';
const USDT_BEP20 = 'USDT.BEP20';

const COIN_USDT = 'USDT';
