<?php

namespace App\Providers;

use App\mywork;
use Illuminate\Support\Facades\Route;
use App\User;
use App\authtoken;
use App\Services\SeoService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::defaultView('vendor.pagination.bootstrap-4');

        Blade::if('vkauth', function () {
            return (bool) session('token');
        });

        view()->composer(['layout', 'layout-min', 'forms.layout', 'partials.seo-meta', 'partials.tool-landing'], function ($view) {
            $view->with('seo', app(SeoService::class)->forCurrentRoute());
            $view->with('isVkAuth', (bool) session('token'));
        });

        if (isset($_GET['r'])) 
        {
            setcookie('zebra-target', $_GET['r'], time()+2592000); 
        } 

        if (isset($_COOKIE['zebra_auth']) and ($_COOKIE['zebra_auth'] > time()))
        {
            $authtoken = authtoken::
                    where('token', '=', $_COOKIE['zebra_auth'])
                    ->where('user_agent', '=', $_SERVER["HTTP_USER_AGENT"])
                    ->first();

            if ($authtoken) 
            {
                $user = User::where('vk_id', '=', $authtoken->vk_id)->first();

                session([
                    'id' => $user->vk_id,
                    'photo' => $user->photo,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'access' => $user->access,
                    'package' => $user->package,
                    'token' => $authtoken->vk_token,
                ]);
    
                $user->update([
                    'last_seen' => time(),
                ]);
            }
        } 

        view()->composer('_left-profile', function($view){
            $view->with('myworks', mywork::where('vk_id', '=', session('id'))->orderBy('id', 'DESC')->take(3)->get()->toArray()
            );
        });


        

        if (isset($_GET['utm_source']))
        {
            setcookie('zebra-utm_source', $_GET['utm_source'], time()+2592000);  
        }

        if (isset($_GET['utm_medium']))
        {
            setcookie('zebra-utm_medium', $_GET['utm_medium'], time()+2592000);  
        }

        if (isset($_GET['utm_campaign']))
        {
            setcookie('zebra-utm_campaign', $_GET['utm_campaign'], time()+2592000);  
        }

        if (isset($_GET['utm_content']))
        {
            setcookie('zebra-utm_content', $_GET['utm_content'], time()+2592000);  
        }

        if (isset($_GET['utm_term']))
        {
            setcookie('zebra-utm_term', $_GET['utm_term'], time()+2592000);  
        }
    }
}
