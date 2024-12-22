<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResumeRepositoryInterface;
use App\Repositories\ResumeRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResumeRepositoryInterface::class, ResumeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
