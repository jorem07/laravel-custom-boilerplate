<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\RequestMakeCommand;
use Illuminate\Support\Str;

class MakeRequestCommand extends RequestMakeCommand
{
    /**
     * Replace the original `make:request` command.
     */
    protected $name = 'make:request';

    /**
     * Customize the class building process.
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $parts = explode('\\', $name);

        $subname = count($parts) > 1 ? $parts[count($parts) - 2] : $parts[count($parts) - 1];

        $subname = $subname = Str::plural(Str::snake($subname));

        return str_replace('{{ subname }}', $subname, $stub);
    }
}
