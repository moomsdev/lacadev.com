<?php

namespace App\Module;

/**
 * Resolves and boots registered modules via the service container.
 */
class ModuleLoader
{
    /** @var class-string<ModuleInterface>[] */
    private array $modules = [];

    /** @var ModuleInterface[] */
    private array $resolved = [];

    public function __construct(private readonly mixed $container)
    {
    }

    /** @param class-string<ModuleInterface>[] $modules */
    public function register(array $modules): static
    {
        foreach ($modules as $class) {
            if (!in_array($class, $this->modules, true)) {
                $this->modules[] = $class;
            }
        }
        return $this;
    }

    public function boot(): void
    {
        foreach ($this->modules as $class) {
            if (isset($this->resolved[$class])) {
                continue;
            }
            $module = $this->resolve($class);
            $module->boot();
            $this->resolved[$class] = $module;
        }
    }

    private function resolve(string $class): ModuleInterface
    {
        if (is_object($this->container) && method_exists($this->container, 'make')) {
            return $this->container->make($class);
        }
        if (isset($this->container[$class])) {
            return $this->container[$class];
        }
        if (class_exists($class)) {
            return new $class($this->container);
        }
        throw new \RuntimeException("Unable to resolve module: {$class}");
    }

    /** @param class-string<ModuleInterface> $class */
    public function get(string $class): ?ModuleInterface
    {
        return $this->resolved[$class] ?? null;
    }
}
