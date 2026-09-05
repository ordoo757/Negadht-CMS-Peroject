<?php

/**
 * NeuroCMS - Content Management System
 *
 * @author     Hooman Oliaei (هومان اولیائی)
 * @copyright  Copyright (c) 2026 Hooman Oliaei
 * @license    GNU General Public License v3.0
 * @link       https://github.com/ordoo757
 */
<?php

namespace App\Core\Foundation;

use Illuminate\Support\ServiceProvider;

abstract class Plugin extends ServiceProvider
{
    protected string $name = '';
    protected string $slug = '';
    protected string $version = '1.0.0';
    protected string $type = 'plugin';
    protected array $hooks = [];
    protected bool $isActive = true;

    abstract public function registerPlugin(): void;
    abstract public function bootPlugin(): void;
    abstract public function install(): bool;
    abstract public function uninstall(): bool;

    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getVersion(): string { return $this->version; }
    public function getType(): string { return $this->type; }
    public function getHooks(): array { return $this->hooks; }
    public function isActive(): bool { return $this->isActive; }

    public function addHook(string $event, callable $callback, int $priority = 10): void
    {
        $this->hooks[$event][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];
    }

    public function executeHook(string $event, array $params = []): mixed
    {
        if (!isset($this->hooks[$event])) {
            return null;
        }

        $hooks = $this->hooks[$event];
        usort($hooks, fn($a, $b) => $a['priority'] <=> $b['priority']);

        $result = null;
        foreach ($hooks as $hook) {
            $result = call_user_func_array($hook['callback'], $params);
        }

        return $result;
    }
}
