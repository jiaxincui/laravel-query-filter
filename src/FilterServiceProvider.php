<?php

namespace Jiaxincui\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Jiaxincui\QueryFilter\Console\FilterMakeCommand;
use Jiaxincui\QueryFilter\BaseFilter;

class FilterServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                FilterMakeCommand::class,
            ]);
        }
        BaseFilter::setQuery($this->app->make('request')->query());
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}
