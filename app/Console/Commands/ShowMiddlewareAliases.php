<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;

class ShowMiddlewareAliases extends Command
{
    protected $signature = 'debug:middleware-aliases';
    protected $description = 'List all registered route middleware aliases';

    public function handle()
    {
        $kernel = app(Kernel::class);
        $aliases = $kernel->getMiddlewareAliases();

        foreach ($aliases as $alias => $class) {
            $this->line("{$alias} => {$class}");
        }

        return Command::SUCCESS;
    }
}
