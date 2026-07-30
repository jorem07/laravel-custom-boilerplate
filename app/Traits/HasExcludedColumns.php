<?php
namespace App\Traits;

trait HasExcludedColumns
{
    protected function initializeHasExcludedColumns()
    {
        $this->hidden = array_merge($this->hidden, $this->excludedColumn ?? []);
    }
}