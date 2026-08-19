<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;

Artisan::command('finance:about', function (): void {
    $this->info('Finance Pro API');
    $this->line('Gestion financière multi-organisation pour ONG.');
})->purpose('Display Finance Pro information');
