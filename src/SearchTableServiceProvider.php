<?php

namespace SearchTable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SearchTableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('search-table')
            // ->hasConfigFile('searchtable')
            ->hasCommands([
                
            ])
            ->hasViews();
    }
    
    public function boot()
    {
        parent::boot();

        // Load package views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'search-table');
    }

}
