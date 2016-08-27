<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\UserInterface', 'App\Repositories\UserRepository');
        $this->app->bind('App\Contracts\UserAccountInterface', 'App\Repositories\UserAccountRepository');
        $this->app->bind('App\Contracts\ContributorInterface', 'App\Repositories\ContributorRepository');
        $this->app->bind('App\Contracts\GoalInterface', 'App\Repositories\GoalRepository');
        $this->app->bind('App\Contracts\TransactionInterface', 'App\Repositories\TransactionRepository');
    }
}
