<?php

namespace Chenyulingxi\LaravelAdmin\GridExporter;

use Illuminate\Support\ServiceProvider;

class GridExporterServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot(GridExporter $extension)
    {
        if (! GridExporter::boot()) {
            return ;
        }

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'grid-exporter');
        }

        if ($this->app->runningInConsole() && $assets = $extension->assets()) {
            $this->publishes(
                [$assets => public_path('vendor/laravel-admin-ext/grid-exporter')],
                'grid-exporter'
            );
        }

        $this->app->booted(function () {
            GridExporter::routes(__DIR__.'/../routes/web.php');
        });

        $extension->register();
    }
}