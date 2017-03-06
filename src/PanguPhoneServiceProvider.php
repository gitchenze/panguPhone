<?php

namespace Aze\panguPhone;

use Illuminate\Support\ServiceProvider;

class PanguPhoneServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('pgPhone',function (){
            return new PanguPhone();
        });
    }
}
