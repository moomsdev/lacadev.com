<?php

namespace App\Module;

/**
 * Contract for all theme modules.
 */
interface ModuleInterface
{
    public function boot(): void;
}
