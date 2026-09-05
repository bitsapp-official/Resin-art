<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment('Art is not what you see, but what you make others see.');
})->purpose('Display an inspiring quote');
