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

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'module', 'action', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission')
            ->withPivot('conditions');
    }

    public static function seedDefaultPermissions(): void
    {
        $modules = [
            'template' => ['create', 'read', 'update', 'delete', 'publish', 'export', 'import', 'settings'],
            'user' => ['create', 'read', 'update', 'delete', 'roles', 'permissions'],
            'menu' => ['create', 'read', 'update', 'delete', 'publish'],
            'component' => ['create', 'read', 'update', 'delete', 'export', 'import'],
            'module' => ['install', 'uninstall', 'activate', 'deactivate', 'export'],
            'plugin' => ['install', 'uninstall', 'activate', 'deactivate'],
            'form' => ['create', 'read', 'update', 'delete', 'responses'],
            'table' => ['create', 'read', 'update', 'delete', 'export'],
            'report' => ['create', 'read', 'update', 'delete', 'export'],
            'language' => ['create', 'read', 'update', 'delete', 'set_default'],
            'ai' => ['use', 'configure', 'train'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                self::firstOrCreate(
                    ['slug' => "{$module}.{$action}"],
                    [
                        'name' => __("permissions.{$module}.{$action}"),
                        'module' => $module,
                        'action' => $action,
                    ]
                );
            }
        }
    }
}
