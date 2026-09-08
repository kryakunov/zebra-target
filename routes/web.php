<?php

use Illuminate\Support\Facades\Route;
use App\Payment;
use App\Withdraw;
use App\Token;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\authtoken;
use App\User;
use App\Http\Exec\Test;
use Illuminate\Http\Request;
use App\mywork;

use App\Jobs\EmailJob;
use App\Jobs\SubJob;
use App\Jobs\TestJob;
use App\Chain;

//Route::get('/url', [\App\Http\Controllers\PaymentController::class, 'index']);

//Route::get('/job', 'AdminController@job');

Route::get('/exec', 'WhenOnlineController@exec');


Route::get('/gettoken', function(){ var_dump(session('token'));});

Route::view('/testpage', 'testpage');

Route::get('/getall', 'AdminController@getAllUsers');

Route::get('/', 'AuthController@index')->name('/');
Route::view('guest', '_guest')->name('guest');

Route::view('free-parser', 'free-parser')->name('free-parser');
Route::get('/sitemap.xml', 'SitemapController@index')->name('sitemap');
Route::get('/robots.txt', function () {
    return response()->file(public_path('robots.txt'), [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ]);
})->name('robots');

Route::post('/webhook', 'webhook@index')->name('webhook');
Route::get('/payment-success', 'webhook@success')->name('payment-success');
Route::get('/gift/{id}', 'webhook@gift')->name('gift');

Route::get('/updates', 'AdminController@updates')->name('updates');
Route::post('/updates', 'AdminController@updatesStore')->name('updates');
Route::get('/updates/{id}', 'AdminController@updatesDelete')->name('deleteudpate');

Route::get('/getpaymentusers', 'AdminController@getPaymentUsers');
Route::post('/getpaymentusers', 'AdminController@getPaymentUsersPost')->name('getpaymentusers');
Route::post('/storepaymentusers', 'AdminController@storePaymentUsers')->name('storepaymentusers');

Route::get('/worktester', 'WorkTester@handler');

Route::get('/products/admin', 'hh@admin')->name('products.admin');
Route::resource('/products', hh::class);

Route::get('/clearworks', 'MyWorksController@clearWorks')->name('clearworks');



Route::get('/token-check', function() {
    Token::check();
});

Route::get('/cron', 'NewMembersController@cron');


Route::get('/zero', 'VkApiController@zero')->name('zero');
Route::get('/zero-pay', 'VkApiController@zeroPay')->name('zero-pay');

Route::get('/twodays', 'VkApiController@twodays')->name('two-days');
Route::get('/twodayssuccess', 'VkApiController@twodayssuccess')->name('two-day-success');

Route::get('/vk', 'AuthController@code');
Route::get('/vkLogin', 'AuthController@vkLogin')->name('vkLogin');


Route::get('/price', 'VkApiController@price')->name('price');
Route::post('/price', 'VkApiController@price')->name('price');


// Route::get('/price', 'VkApiController@price')->name('price');
// Route::post('/price', 'VkApiController@promo')->name('price');

/*
| Публичные SEO-лендинги инструментов (только GET).
| Запуск задач и остальные действия остаются в группе authVk.
*/
Route::get('/filtergroups', 'WorksController@show')->name('filtergroups');
Route::get('/getallmembers', 'WorksController@show')->name('getallmembers');
Route::get('/getactivitygroups', 'WorksController@show')->name('getactivitygroups');
Route::get('/getgroupcontacts', 'WorksController@show')->name('getgroupcontacts');
Route::get('/userstargetgroups', 'WorksController@show')->name('userstargetgroups');
Route::get('/getpromoposts', 'WorksController@show')->name('getpromoposts');
Route::get('/getactivityposts', 'GetActivityPostsController@show')->name('getactivityposts');
Route::get('/usersfilter', 'WorksController@show')->name('usersfilter');
Route::get('/getactivityuser', 'WorksController@show')->name('getactivityuser');
Route::get('/getrelatives', 'WorksController@show')->name('getrelatives');
Route::get('/getfriends', 'WorksController@show')->name('getfriends');
Route::get('/usersgroups', 'WorksController@show')->name('usersgroups');
Route::get('/usersallgroups', 'WorksController@show')->name('usersallgroups');
Route::get('/opinionliders', 'WorksController@show')->name('opinionliders');
Route::get('/getposts', 'GetPostsController@show')->name('getposts');
Route::get('/topfollowers', 'TopFollowersController@show')->name('topfollowers');
Route::get('/socialnetworks', 'SocialNetworksController@show')->name('socialnetworks');
Route::get('/gettopics', 'GetTopicsController@show')->name('gettopics');
Route::get('/searchgroups', 'SearchGroupsController@show')->name('searchgroups');
Route::get('/extfilter', 'FilterController@extfilter')->name('extfilter');
Route::get('/analiz', 'VkApiController@analiz')->name('analiz');
Route::get('/tool1', 'ToolsController@tool1')->name('tool1');
Route::get('/tool2', 'ToolsController@tool2')->name('tool2');
Route::get('/tool3', 'ToolsController@tool3')->name('tool3');
Route::get('/tool4', 'ToolsController@tool4')->name('tool4');
Route::get('/tool5', 'ToolsController@tool5')->name('tool5');
Route::get('/showgroups', 'ShowGroupsController@show')->name('showgroups');
Route::get('/showposts', 'ToolsController@showPosts')->name('showposts');

Route::group(['middleware' => 'authVk'], function()
{
    Route::get('/logout', 'VkApiController@logout')->name('logout');

    Route::get('/group-viewer/{id?}', 'GroupViewerController@show')->name('groupviewer');
    Route::get('/when-onlines', 'WhenOnlineController@show');
    Route::get('/when-onlines-delete', 'WhenOnlineController@delete');
    Route::get('/when-online-check', 'WhenOnlineController@index');

    Route::get('/ajax', 'MyWorksController@ajax');
    Route::get('/ajaxsamples/{id?}', 'MyWorksController@ajaxSamples');
    Route::get('/ajaxleft', 'MyWorksController@ajaxleft');
    Route::get('/ajaxgetworks', 'MyWorksController@ajaxgetworks');
    Route::get('/ajaxgetcity', 'MyWorksController@ajaxgetcity');
    Route::get('/ajaxgetcountries', 'MyWorksController@ajaxgetcountries');
    Route::get('/ajaxgetuser/{name}', 'MyWorksController@ajaxgetuser');
    Route::get('/ajaxgetcloud', 'MyWorksController@ajaxgetcloud');
    //Route::get('/profile', 'ProfileController@index')->name('profile');

    Route::get('/profile', function () {
        return redirect('/myworks');
    });

    // Менеджер задач
    Route::get('/myworks', 'MyWorksController@index')->name('myworks');
    Route::get('/myworks/{id}', 'CloudController@workStore')->name('workstore');

    Route::get('/download/{id}', 'MyWorksController@download')->name('download');
    Route::get('/downloadlog/{id}', 'AdminController@downloadlog')->name('downloadlog');
    Route::get('/downloadsourcefile/{id}', 'AdminController@downloadsourcefile')->name('downloadsourcefile');
    Route::get('/downloadexecfile/{id}', 'AdminController@downloadexecfile')->name('downloadexecfile');

    Route::get('/getworkhistory/{id}', 'AdminController@getworkhistory')->name('getworkhistory');

    Route::get('/savesample/{id}', 'SampleController@create')->name('savesample');
    Route::get('/mysamples', 'SampleController@index')->name('mysamples');
    Route::get('/sampleshow/{id}', 'SampleController@sampleshow')->name('sampleshow');
    Route::post('/savesamplerequest', 'SampleController@savesamplerequest')->name('savesamplerequest');
    Route::post('/savesampledatafrom', 'SampleController@savesampledatafrom')->name('savesampledatafrom');
    Route::get('/sampledelete/{id}', 'SampleController@sampledelete')->name('sampledelete');
    Route::get('/sharesample/{id}', 'SampleController@sharesample')->name('sharesample');
    Route::post('/sharesamplesave', 'SampleController@sharesamplesave')->name('sharesamplesave');
    Route::get('/sample/{id}', 'SampleController@sharedsampleshow');
    Route::get('/sampledownload/{id}', 'SampleController@SampleDownload')->name('SampleDownload');
    Route::get('/samplestoreincloud/{id}', 'CloudController@sampleStore')->name('SampleStoreInCloud');
    Route::get('/savesampletimer/{id}', 'SampleController@SaveSampleTimer')->name('SaveSampleTimer');
    Route::get('/deletestep/{id}', 'SampleController@deletestep')->name('deletestep');
    Route::get('/createsample/{uri?}', 'SampleController@createsample')->name('createsample');


    Route::get('/cronstartworks', 'WorksController@cronStartWorks')->name('CronStartWorks');
    Route::get('/saveworktimer/{id}', 'WorksController@SaveWorkTimer')->name('SaveWorkTimer');


    Route::get('/samplesstart/{id?}', 'SampleController@startCron');


    Route::get('/run/{id}', 'WorksController@WorkRun')->name('workrun');
    Route::get('/samplerun/{id}', 'WorksController@SampleRun')->name('samplerun');
    Route::get('/sampleruncron', 'WorksController@SampleRunCron')->name('sampleruncron');


    Route::get('/getworkshare/{id}', 'MyWorksController@getWorkShare')->name('getworkshare');
    Route::get('/getchainshare/{id}', 'MyWorksController@getChainShare')->name('getchainshare');

    // Расшаренные задачи
    Route::get('/getworkchain/{id}', 'MyWorksController@getWorkChain')->name('getworkchain');

    Route::get('/sharework/{id}', 'MyWorksController@sharework')->name('sharework');
    Route::get('/sharechain/{id}', 'MyWorksController@sharechain')->name('sharechain');
    Route::get('/works/{id}', 'MyWorksController@works')->name('works');


    Route::get('/getworkallmembers/{id}', 'MyWorksController@getWorkAllMembers')->name('getworkallmembers');
    Route::get('/getworkusersfilter/{id}', 'MyWorksController@getWorkUsersFilter')->name('getworkusersfilter');
    Route::get('/getworkseachgroups/{id}', 'MyWorksController@getWorkSearchGroups')->name('getworkseachgroups');

    Route::get('/getworkliders/{id}', 'MyWorksController@getWorkLiders')->name('getworkliders');
    Route::get('/getworkusersgroups/{id}', 'MyWorksController@getWorkUsersGroups')->name('getworkusersgroups');
    Route::get('/getworksocialnetworks/{id}', 'MyWorksController@getWorkSocialNetworks')->name('getworksocialnetworks');



    Route::get('/movework/{id}/{work}', 'MyWorksController@moveWork')->name('movework');

    Route::get('/workdelete/{id}', 'MyWorksController@delete')->name('workdelete');
    Route::get('/deleteallworks', 'MyWorksController@deleteallworks')->name('deleteallworks');

    Route::get('killmywork/{id}', 'MyWorksController@kill')->name('killmywork');

    Route::get('/getworkgetmembers/{id}', 'ProfileController@getWorkGetMembers')->name('getworkgetmembers');
    Route::get('/getworkfilterusers/{id}', 'ProfileController@getWorkFilterUsers')->name('getworkfilterusers');
   // Route::get('/getworkusersfilter/{id}', 'ProfileController@getWorkUsersFilter')->name('getworkusersfilter');
    Route::get('/workusersfilterrepeat/{id}', 'ProfileController@workUsersFilterRepeat')->name('workusersfilterrepeat');
    Route::get('/getworktopfollowers/{id}', 'ProfileController@getWorkTopFollowers')->name('getworktopfollowers');
    Route::get('/deletework/{id}', 'ProfileController@deleteliders')->name('deletework');

    Route::delete('/deletework/{id}', 'ProfileController@deletework')->name('deletework');
    Route::get('/partner', 'PartnerController@partner')->name('partner');
    Route::get('withdraw', 'PartnerController@withdraw')->name('withdraw');
    Route::post('withdraw', 'PartnerController@withdrawStore')->name('withdrawStore');
    Route::get('withdrawsuccess/{id}', 'PartnerController@withdrawSuccess')->name('withdrawSuccess');

    Route::resource('/cloud', CloudController::class);
    Route::post('/showusers', 'CloudController@showusers')->name('showusers');
    Route::post('/showusersdelete', 'CloudController@showUsersDelete')->name('showusersdelete');

    Route::get('/cloudshowusers', 'CloudController@cloudshowusers')->name('cloudshowusers');
    Route::get('/cloudshowgroups', 'CloudController@cloudshowgroups')->name('cloudshowgroups');
    Route::get('/cloudshowposts', 'CloudController@cloudshowposts')->name('cloudshowposts');

    Route::post('/showgroups', 'ShowGroupsController@handler')->name('showgroups');
    Route::post('/filtergroups', 'WorksController@handler')->name('filtergroups');
    Route::post('/getallmembers', 'WorksController@handler')->name('getallmembers');
    Route::post('/getactivitygroups', 'WorksController@handler')->name('getactivitygroups');
    Route::post('/getgroupcontacts', 'WorksController@handler')->name('getgroupcontacts');
    Route::post('/userstargetgroups', 'WorksController@handler')->name('userstargetgroups');
    Route::post('/getpromoposts', 'WorksController@handler')->name('getpromoposts');
    Route::post('/getactivityposts', 'GetActivityPostsController@handler')->name('postactivityposts');
    Route::post('/usersfilter', 'WorksController@handler')->name('usersfilter');
    Route::post('/getactivityuser', 'WorksController@handler')->name('getactivityuser');
    Route::post('/getrelatives', 'WorksController@handler')->name('getrelatives');
    Route::post('getfriends', 'WorksController@handler')->name('getfriends');
    Route::post('/usersgroups', 'WorksController@handler')->name('usersgroups');
    Route::post('/usersallgroups', 'WorksController@handler')->name('usersallgroups');
    Route::post('/opinionliders', 'WorksController@handler')->name('opinionliders');
    Route::post('/getposts', 'GetPostsController@handler')->name('getposts');
    Route::post('/topfollowers', 'TopFollowersController@handler')->name('topfollowers');
    Route::post('/socialnetworks', 'SocialNetworksController@handler')->name('socialnetworks');
    Route::post('/gettopics', 'GetTopicsController@handler')->name('gettopics');
    Route::post('/searchgroups', 'SearchGroupsController@store')->name('searchgroupsstore');
    Route::post('/searchgroupscity', 'SearchGroupsController@city')->name('searchgroupscity');
    Route::get('/getlikes', 'GetLikesController@show')->name('getlikes');
    Route::post('/getlikes', 'GetLikesController@handler')->name('getlikes');
    Route::post('/extfilter', 'FilterController@extfilterStore')->name('extfilterStore');
    Route::post('/analiz', 'VkApiController@analizStore')->name('analizStore');
    Route::post('/tool1', 'ToolsController@tool1Post')->name('tool1Post');
    Route::post('/tool2', 'ToolsController@tool2Post')->name('tool2Post');
    Route::post('/tool3', 'ToolsController@tool3Post')->name('tool3Post');
    Route::post('/tool4', 'ToolsController@tool4Post')->name('tool4Post');
    Route::post('/tool5', 'ToolsController@tool5Post')->name('tool5Post');

   // Route::resource('/toprofile', toProfileController::class);
   Route::get('/toprofile','toProfileController@index')->name('toprofile');
   Route::post('/toprofile','toProfileController@store')->name('toprofilestore');
   Route::post('/toprofiledestroy', 'toProfileController@destroy')->name('toprofiledestroy');
   Route::get('/getblacklist', 'toProfileController@getBlackList')->name('getblacklist');
   // Route::post('toprofile', 'ToolsController@toProfileStore')->name('toprofile');

    Route::get('pay-access', 'PartnerController@payAccess')->name('pay-access');
    Route::post('pay-access', 'PartnerController@payAccessStore')->name('pay-access');

    Route::get('/support', 'SupportController@index')->name('support');
    Route::post('/support', 'SupportController@store')->name('supportStore');
    Route::post('/support/{id}', 'SupportController@questionStore')->name('questionStore');
    Route::get('/support/{id}', 'SupportController@show')->name('supportShow');
    Route::post('/supportreply/{id}', 'SupportController@supportReply')->name('supportReply');

    Route::get('/newfriends', 'FriendsController@show')->name('NewFriendsShow');
    Route::post('/newfriends', 'FriendsController@store')->name('NewFriendsStore');
    Route::get('/newfriendscreate', 'FriendsController@create')->name('NewFriendsCreate');
    Route::delete('/newfriendsdelete/{id}', 'FriendsController@destroy')->name('NewFriendsDelete');
    Route::get('/newfriendsupdate', 'FriendsController@update')->name('NewFriendsUpdate');
    Route::get('/newfriendsget/{id}', 'FriendsController@getNewFriends')->name('NewFriendsGet');
    Route::get('/delfriendsget/{id}', 'FriendsController@getDelFriends')->name('DelFriendsGet');

    Route::get('/newmembers', 'NewMembersController@show')->name('NewMembersShow');
    Route::post('/newmembers', 'NewMembersController@store')->name('NewMembersStore');
    Route::get('/newmemberscreate', 'NewMembersController@create')->name('NewMembersCreate');
    Route::delete('/newmembersdelete/{id}', 'NewMembersController@destroy')->name('NewMembersDelete');
    Route::get('/newmembersshowdelete/{id}', 'NewMembersController@newmembersshowdelete')->name('NewMembersShowDelete');
    Route::get('/newmembersupdate/{id}', 'NewMembersController@update')->name('NewMembersUpdate');
    Route::get('/newmembersget/{id}', 'NewMembersController@getNewMembers')->name('NewMembersGet');

   // Route::post('/usersgroups', 'UsersGroupsController@getIds')->name('usersgroupsgetids');

    Route::get('/chains', 'ChainController@index')->name('chains');
    Route::get('/storechain/{id}', 'ChainController@storeChain')->name('storechain');
    Route::get('/chainshow/{id}', 'ChainController@show')->name('chainshow');
    Route::post('/chainrun/{id}', 'ChainController@run')->name('chainrun');

    Route::get('/post', 'PostsController@index')->name('post.index');
    Route::get('/post/{post}', 'PostsController@show')->name('post.show');
    Route::get('/create', 'PostsController@create')->name('post.create');
    Route::post('/store', 'PostsController@store')->name('post.store');
    Route::get('/post/{post}/edit', 'PostsController@edit')->name('post.edit');
    Route::patch('/post/{post}', 'PostsController@update')->name('post.update');
    Route::delete('/post/{post}', 'PostsController@destroy')->name('post.delete');

});

Route::get('/ayaz', 'VkApiController@ayaz')->name('ayaz');
/*

Route::get('/posts', 'PostsController@index')->name('posts.index');
Route::get('/create', 'PostsController@create')->name('posts.create');
Route::get('/posts/{post}', 'PostsController@show')->name('posts.show');
Route::post('/posts', 'PostsController@store')->name('posts.store');
Route::get('/posts/{post}/edit', 'PostsController@edit')->name('posts.edit');
Route::patch('/posts/{post}', 'PostsController@update')->name('posts.update');
Route::delete('/posts/{post}', 'PostsController@destroy')->name('posts.delete');

*/

Route::get('/checkstatuswork/{pid}', 'AdminController@checkStatusWork')->name('checkstatuswork');

// Админка
Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function() {
    Route::get('/', 'AdminController@index')->name('admin');
    Route::get('/admin2', 'AdminController@index2')->name('admin2');
    Route::get('/admin3/{errors?}', 'AdminController@index3')->name('admin3');
    Route::get('/getsampleworks/{errors?}', 'AdminController@GetSampleWorks')->name('getsampleworks');
    Route::get('/getsamplelog/{id?}', 'AdminController@GetSampleLog')->name('getsamplelog');
    Route::get('/stats', 'AdminController@getAllStats')->name('stats');
    Route::post('/getbyid', 'AdminController@getById');
    Route::get('/getbyid', 'AdminController@index');
    Route::post('/store', 'AdminController@store');
    Route::post('/getpayments', 'AdminController@getpayments');
    Route::get('/getpartners', 'AdminController@getpartners')->name('getpartners');


    Route::post('/newpayment', 'AdminController@newPayment');


    Route::get('/getall', 'AdminController@getAllUsers')->name('getall');
   //Route::get('/store', 'AdminController@index');
});


