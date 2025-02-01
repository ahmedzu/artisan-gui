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

// to publish the package give it a tag (version)
// git tag
// git tag v1.0.0
// git push origin v1.0.0

//to develop the package locally, you can add the following to your composer.json file
// "repositories": [
//     {
//         "type": "path",
//         "url": "./packages/ahmedzu/artisan-gui"
//     }
//     ],
