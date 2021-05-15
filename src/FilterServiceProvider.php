<?php

namespace Jiaxincui\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Jiaxincui\QueryFilter\Console\FilterMakeCommand;
use Jiaxincui\QueryFilter\BaseFilter;
use Jiaxincui\QueryFilter\QueryFilter;

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        BaseFilter::resolveFilter(new QueryFilter($this->app->make('request')->query()));
    }
}
