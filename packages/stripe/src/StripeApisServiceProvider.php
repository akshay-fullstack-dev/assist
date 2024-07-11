<?php
namespace intersoft\stripe;

use Illuminate\Support\ServiceProvider;

class StripeApisServiceProvider extends ServiceProvider {
    public function boot(){
        $this->loadRoutesFrom(__DIR__.'/routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
?>