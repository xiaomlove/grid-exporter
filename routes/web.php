<?php

use Chenyulingxi\LaravelAdmin\GridExporter\Http\Controllers\GridExporterController;

Route::get('grid-exporter', GridExporterController::class.'@index');