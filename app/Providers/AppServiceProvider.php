<?php

namespace App\Providers;

use App\Services\JogosService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   *
   * @return void
   */
  public function register()
  {
    $this->app->bind(JogosService::class, function ($app) {
      return new JogosService();
    });
  }
}
