<?php

namespace Ahmedzu\ArtisanGui;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ArtisanGuiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__.'/resources/views', 'artisan-gui');
    }

    public function register()
    {

    }
}
