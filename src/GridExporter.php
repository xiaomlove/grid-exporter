<?php

namespace Chenyulingxi\LaravelAdmin\GridExporter;

use Encore\Admin\Extension;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Exporter as LaravelAdminExporter;

class GridExporter extends Extension
{
    public $name = 'grid-exporter';

    public $views = __DIR__.'/../resources/views';

    public $assets = __DIR__.'/../resources/assets';

    public $menu = [
        'title' => 'Gridexporter',
        'path'  => 'grid-exporter',
        'icon'  => 'fa-gears',
    ];

    public function register()
    {
        LaravelAdminExporter::extend($this->name, Exporter::class);
        Grid::init(function ($grid) {
            $grid->exporter($this->name);
        });
    }
}