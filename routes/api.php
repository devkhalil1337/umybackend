<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\customContactsController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\RefferalsController;
use App\Http\Controllers\SupportAgentController;
use App\Http\Controllers\liveChatController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalController;
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



Route::get('test', function () {
    return 'jooo';
});


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([

    'middleware' => 'api',
    'prefix'     => 'auth'

], function () {

	Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register');
	Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
	Route::post('login', [App\Http\Controllers\AuthController::class, 'login'])->name('login');
	Route::post('refresh', [App\Http\Controllers\AuthController::class, 'refresh'])->name('refresh');
	Route::post('me', [App\Http\Controllers\AuthController::class, 'me'])->name('me');
	Route::post('adminSignIn', [App\Http\Controllers\AuthController::class, 'adminSignIn'])->name('adminSignIn');
});


Route::post('main-category-sport-type', [CardController::class, 'MainCategorySportType'])->name('main-category-sport-type');
Route::post('subcategories-age-type', [CardController::class, 'SubCategoriesAgeType'])->name('subcategories-age-type');
Route::post('subcategory-position', [CardController::class, 'SubcategoryPosition'])->name('subcategory-position');

// Plan
Route::post('create-billing-plan', [PayPalController::class, 'createBillingPlan']);
Route::post('execute-agreement', [PayPalController::class, 'executeAgreement']);
Route::post('cancel-paypal-subscription', [PayPalController::class, 'cancelSubscription']);
Route::post('update-paypal-keys', [PayPalController::class, 'UpdatePaypalKeys']);


	Route::post('checkEmail', [App\Http\Controllers\CardController::class, 'checkEmail'])->name('checkEmail');
	Route::post('getAllUsers', [App\Http\Controllers\CardController::class, 'getAllUsers'])->name('getAllUsers');
	Route::post('resetPassword', [App\Http\Controllers\CardController::class, 'resetPassword'])->name('resetPassword');
	Route::post('updateExpiryDate', [App\Http\Controllers\CardController::class, 'updateExpiryDate'])->name('updateExpiryDate');
	Route::post('saveCard', [App\Http\Controllers\CardController::class, 'saveCard'])->name('saveCard');
	Route::post('getCard', [App\Http\Controllers\CardController::class, 'getCard'])->name('getCard');
	Route::post('getSingleCard', [App\Http\Controllers\CardController::class, 'getSingleCard'])->name('getSingleCard');
	Route::post('deleteCard', [App\Http\Controllers\CardController::class, 'deleteCard'])->name('deleteCard');
	Route::post('updateCard', [App\Http\Controllers\CardController::class, 'updateCard'])->name('updateCard');
	Route::post('incrementSend', [App\Http\Controllers\CardController::class, 'incrementSend'])->name('incrementSend');
	Route::post('incrementTotalViews', [App\Http\Controllers\CardController::class, 'incrementTotalViews'])->name('incrementTotalViews');
	Route::post('getActivity', [App\Http\Controllers\CardController::class, 'getActivity'])->name('getActivity');
	Route::post('deleteActivity', [App\Http\Controllers\CardController::class, 'deleteActivity'])->name('deleteActivity');
	Route::post('editUser', [App\Http\Controllers\CardController::class, 'editUser'])->name('editUser');
	Route::post('changePassword', [App\Http\Controllers\CardController::class, 'changePassword'])->name('changePassword');

	Route::post('all-categories', [App\Http\Controllers\CardController::class, 'allCategories'])->name('all-categories');
	Route::post('sub-categories', [App\Http\Controllers\CardController::class, 'subCategories'])->name('sub-categories');
	Route::post('search-by-categories', [App\Http\Controllers\CardController::class, 'SearchByCategories'])->name('search-by-categories');
	Route::post('categories', [App\Http\Controllers\CardController::class, 'categories'])->name('categories');
	Route::post('add-category', [App\Http\Controllers\CardController::class, 'addCategory'])->name('add-category');
	Route::post('get-search-card-by-dropdown', [App\Http\Controllers\CardController::class, 'getSearchCardByDropdown'])->name('get-search-card-by-dropdown');
	Route::post('get-search-card-by-dropdown-proliving', [App\Http\Controllers\CardController::class, 'getSearchCardByDropdownproliving'])->name('get-search-card-by-dropdown-proliving');

    

	Route::post('searchUser', [App\Http\Controllers\ContactsController::class, 'searchUser'])->name('searchUser');
	Route::post('userData', [App\Http\Controllers\ContactsController::class, 'userData'])->name('userData');
	Route::post('myRequests', [App\Http\Controllers\ContactsController::class, 'myRequests'])->name('myRequests');
	Route::post('sendRequest', [App\Http\Controllers\ContactsController::class, 'sendRequest'])->name('sendRequest');
	Route::post('myContacts', [App\Http\Controllers\ContactsController::class, 'myContacts'])->name('myContacts');
	Route::post('acceptRequest', [App\Http\Controllers\ContactsController::class, 'acceptRequest'])->name('acceptRequest');
	Route::post('deleteRequest', [App\Http\Controllers\ContactsController::class, 'deleteRequest'])->name('deleteRequest');
	Route::post('deleteUser', [App\Http\Controllers\ContactsController::class, 'deleteUser'])->name('deleteUser');

	Route::post('listOfReferals', [App\Http\Controllers\ContactsController::class, 'listOfReferals'])->name('listOfReferals');
	Route::post('changeStatus', [App\Http\Controllers\ContactsController::class, 'changeStatus'])->name('changeStatus');
	Route::post('savePaymentInfo', [App\Http\Controllers\ContactsController::class, 'savePaymentInfo'])->name('savePaymentInfo');

	Route::post('saveCustomContact', [App\Http\Controllers\customContactsController::class, 'saveCustomContact'])->name('saveCustomContact');
	Route::post('getCustomContact', [App\Http\Controllers\customContactsController::class, 'getCustomContact'])->name('getCustomContact');
	Route::post('getCustomContactSingle', [App\Http\Controllers\customContactsController::class, 'getCustomContactSingle'])->name('getCustomContactSingle');
	Route::post('deleteCustomContact', [App\Http\Controllers\customContactsController::class, 'deleteCustomContact'])->name('deleteCustomContact');
	Route::post('updateCustomContact', [App\Http\Controllers\customContactsController::class, 'updateCustomContact'])->name('updateCustomContact');
	

	Route::post('sendMessage', [App\Http\Controllers\MessageController::class, 'sendMessage'])->name('sendMessage');
	Route::post('getMessages', [App\Http\Controllers\MessageController::class, 'getMessages'])->name('getMessages');
	Route::post('deleteMessage', [App\Http\Controllers\MessageController::class, 'deleteMessage'])->name('deleteMessage');
	Route::post('connectedUserMsg', [App\Http\Controllers\MessageController::class, 'connectedUserMsg'])->name('connectedUserMsg');


	Route::post('createPackage', [App\Http\Controllers\PackageController::class, 'createPackage'])->name('createPackage');
	Route::post('updatePackage', [App\Http\Controllers\PackageController::class, 'updatePackage'])->name('updatePackage');
	Route::post('deletePackage', [App\Http\Controllers\PackageController::class, 'deletePackage'])->name('deletePackage');
	Route::post('getPackages', [App\Http\Controllers\PackageController::class, 'getPackages'])->name('getPackages');
	Route::post('getPackageSingle', [App\Http\Controllers\PackageController::class, 'getPackageSingle'])->name('getPackageSingle');
	Route::post('updateUserPackage', [App\Http\Controllers\PackageController::class, 'updateUserPackage'])->name('updateUserPackage');
	Route::post('getAdmin', [App\Http\Controllers\PackageController::class, 'getAdmin'])->name('getAdmin');
	Route::post('updateAdmin', [App\Http\Controllers\PackageController::class, 'updateAdmin'])->name('updateAdmin');
	Route::post('updatePasswordAdmin', [App\Http\Controllers\PackageController::class, 'updatePasswordAdmin'])->name('updatePasswordAdmin');
	Route::post('Adminlogin', [App\Http\Controllers\PackageController::class, 'Adminlogin'])->name('Adminlogin');
	Route::post('countUser', [App\Http\Controllers\PackageController::class, 'countUser'])->name('countUser');
	Route::post('countCard', [App\Http\Controllers\PackageController::class, 'countCard'])->name('countCard');
	Route::post('CountPackageUsage', [App\Http\Controllers\PackageController::class, 'CountPackageUsage'])->name('CountPackageUsage');
	Route::post('Usage', [App\Http\Controllers\PackageController::class, 'Usage'])->name('Usage');


	Route::post('applyForReferral', [App\Http\Controllers\RefferalsController::class, 'applyForReferral'])->name('applyForReferral');
	Route::post('referalAppliedUsersList', [App\Http\Controllers\RefferalsController::class, 'referalAppliedUsersList'])->name('referalAppliedUsersList');
	Route::post('createRefferalCode', [App\Http\Controllers\RefferalsController::class, 'createRefferalCode'])->name('createRefferalCode');
	Route::post('HowMuchUsersByReferalCode', [App\Http\Controllers\RefferalsController::class, 'HowMuchUsersByReferalCode'])->name('HowMuchUsersByReferalCode');
	Route::post('verify_code', [App\Http\Controllers\RefferalsController::class, 'verify_code'])->name('verify_code');
	Route::post('resend_code', [App\Http\Controllers\RefferalsController::class, 'resend_code'])->name('resend_code');	


	Route::post('addSupportAgent', [App\Http\Controllers\SupportAgentController::class, 'addSupportAgent'])->name('addSupportAgent');
	Route::post('updateSupportAgent', [App\Http\Controllers\SupportAgentController::class, 'updateSupportAgent'])->name('updateSupportAgent');
	Route::post('deleteSupportAgent', [App\Http\Controllers\SupportAgentController::class, 'deleteSupportAgent'])->name('deleteSupportAgent');
	Route::post('getSupportAgent', [App\Http\Controllers\SupportAgentController::class, 'getSupportAgent'])->name('getSupportAgent');
	Route::post('admin_text', [App\Http\Controllers\SupportAgentController::class, 'admin_text'])->name('admin_text');
	Route::post('get_admin_text', [App\Http\Controllers\SupportAgentController::class, 'GetAdminText'])->name('get_admin_text');


	Route::post('respondMessage', [App\Http\Controllers\liveChatController::class, 'respondMessage'])->name('respondMessage');
	Route::post('noOfOpenChats', [App\Http\Controllers\liveChatController::class, 'noOfOpenChats'])->name('noOfOpenChats');
	Route::post('emailChats', [App\Http\Controllers\liveChatController::class, 'emailChats'])->name('emailChats');
	Route::post('closedChat', [App\Http\Controllers\liveChatController::class, 'closedChat'])->name('closedChat');

	Route::post('deleteChat', [App\Http\Controllers\liveChatController::class, 'deleteChat'])->name('deleteChat');
	Route::post('startLiveChat', [App\Http\Controllers\liveChatController::class, 'startLiveChat'])->name('startLiveChat');
	Route::post('saveLiveChat', [App\Http\Controllers\liveChatController::class, 'saveLiveChat'])->name('saveLiveChat');
	Route::post('getLiveChat', [App\Http\Controllers\liveChatController::class, 'getLiveChat'])->name('getLiveChat');

	Route::post('closeChat', [App\Http\Controllers\liveChatController::class, 'closeChat'])->name('closeChat');
	Route::post('getOpenChat', [App\Http\Controllers\liveChatController::class, 'getOpenChat'])->name('getOpenChat');
	Route::post('getOpenChatAdmin', [App\Http\Controllers\liveChatController::class, 'getOpenChatAdmin'])->name('getOpenChatAdmin');
	Route::post('getChatRecord', [App\Http\Controllers\liveChatController::class, 'getChatRecord'])->name('getChatRecord');



	Route::post('stripe', [App\Http\Controllers\PaymentController::class, 'stripePost'])->name('stripe.post');
	Route::post('add-customer', [App\Http\Controllers\PaymentController::class, 'addCustomer'])->name('add-customer');
	Route::post('update-customer', [App\Http\Controllers\PaymentController::class, 'updateCustomer'])->name('update-customer');
	Route::post('all-customers', [App\Http\Controllers\PaymentController::class, 'allCustomers'])->name('all-customers');
	Route::post('delete-customer', [App\Http\Controllers\PaymentController::class, 'deleteCustomers'])->name('delete-customer');
	Route::post('update-customer-id', [App\Http\Controllers\PaymentController::class, 'updateCustomerID'])->name('update-customer-id');
	Route::post('add-product', [App\Http\Controllers\PaymentController::class, 'addProduct'])->name('add-product');
	Route::post('update-product', [App\Http\Controllers\PaymentController::class, 'updateProduct'])->name('update-product');

	Route::post('all-products', [App\Http\Controllers\PaymentController::class, 'allProducts'])->name('all-products');
	Route::post('delete-product', [App\Http\Controllers\PaymentController::class, 'deleteProduct'])->name('delete-product');
	Route::post('update-product-id', [App\Http\Controllers\PaymentController::class, 'updateProductID'])->name('update-product-id');

	Route::post('add-price', [App\Http\Controllers\PaymentController::class, 'addPrice'])->name('add-price');
	Route::post('all-prices', [App\Http\Controllers\PaymentController::class, 'allPrices'])->name('all-prices');
	Route::post('update-price-id', [App\Http\Controllers\PaymentController::class, 'updatePriceID'])->name('update-price-id');



	Route::post('add-subscription', [App\Http\Controllers\PaymentController::class, 'addSubscription'])->name('add-subscription');
	Route::post('cancel-subscription', [App\Http\Controllers\PaymentController::class, 'cancelSubscription'])->name('cancel-subscription');
	Route::post('update-subscription-id', [App\Http\Controllers\PaymentController::class, 'updateSubscriptionID'])->name('update-subscription-id');


	Route::post('add-product-price-subscription', [App\Http\Controllers\PaymentController::class, 'addProductPriceSubscription'])->name('add-product-price-subscription');
