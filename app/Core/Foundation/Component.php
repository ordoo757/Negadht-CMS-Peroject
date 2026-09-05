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

abstract class Component extends Module
{
    protected string $type = 'component';
    protected string $icon = 'puzzle-piece';
    protected string $adminRoute = '';
    protected array $permissions = [];
    protected bool $hasAdminPanel = true;
    protected bool $hasFrontend = true;

    public function getType(): string { return $this->type; }
    public function getIcon(): string { return $this->icon; }
    public function getAdminRoute(): string { return $this->adminRoute; }
    public function getPermissions(): array { return $this->permissions; }
    public function hasAdminPanel(): bool { return $this->hasAdminPanel; }
    public function hasFrontend(): bool { return $this->hasFrontend; }

    public function registerAdminMenu(): array
    {
        return [];
    }

    public function registerWidgets(): array
    {
        return [];
    }

    public function registerShortcodes(): array
    {
        return [];
    }
}
