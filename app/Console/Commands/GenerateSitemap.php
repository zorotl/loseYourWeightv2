<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.';

    public function handle()
    {
        Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0))
            ->add(Url::create('/login')->setPriority(0.5))
            ->add(Url::create('/register')->setPriority(0.5))
            ->add(Url::create('/terms')->setPriority(0.3))
            ->add(Url::create('/privacy')->setPriority(0.3))
            ->add(Url::create('/imprint')->setPriority(0.3))
            ->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');

        return 0;
    }
}