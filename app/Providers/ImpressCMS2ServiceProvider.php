<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ImpressCMS2ServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */

   protected $defer = true;

   public function register() {
      $this->app->singleton("ImpressCMS2",function($app){

         // here are the contents of the legacy index.php:
         require_once '../../htdocs/icmsindex.php';

      });
   }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
