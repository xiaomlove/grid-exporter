<?php

namespace Chenyulingxi\LaravelAdmin\GridExporter\Http\Controllers;

use Encore\Admin\Layout\Content;
use Illuminate\Routing\Controller;

class GridExporterController extends Controller
{
    public function index(Content $content)
    {
        return $content
            ->header('Title')
            ->description('Description')
            ->body(view('grid-exporter::index'));
    }
}