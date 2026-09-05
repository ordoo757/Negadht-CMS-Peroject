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

namespace App\Modules\ComponentMaker;

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string $name = 'سازنده کامپوننت';
    protected string $slug = 'component-maker';
    protected string $version = '2.0.0';
    protected string $icon = 'box';
    protected string $adminRoute = 'admin.component-maker.index';
    protected array $dependencies = ['User', 'ModuleMaker'];

    public function registerModule(): void
    {
        //
    }

    public function bootModule(): void
    {
        $this->loadRoutes();
        $this->loadViews();
        $this->loadTranslations();
    }

    public function install(): bool
    {
        return true;
    }

    public function uninstall(): bool
    {
        return true;
    }

    public function update(string $oldVersion): bool
    {
        return true;
    }
}
