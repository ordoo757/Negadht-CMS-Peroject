<?php

namespace App\Modules\ModuleMaker\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleMakerService
{
    public function generateModule(array $config): array
    {
        $name = $config['name'];
        $slug = Str::slug($name);
        $studly = Str::studly($name);
        $namespace = 'App\\Modules\\' . $studly;
        $path = app_path('Modules/' . $studly);

        if (File::isDirectory($path)) {
            return ['success' => false, 'error' => 'Module already exists'];
        }

        File::makeDirectory($path, 0755, true);
        File::makeDirectory($path . '/Controllers/Admin', 0755, true);
        File::makeDirectory($path . '/Services', 0755, true);
        File::makeDirectory($path . '/Models', 0755, true);
        File::makeDirectory($path . '/Database/Migrations', 0755, true);
        File::makeDirectory($path . '/Resources/views', 0755, true);
        File::makeDirectory($path . '/Resources/lang/fa', 0755, true);
        File::makeDirectory($path . '/Resources/lang/ar', 0755, true);
        File::makeDirectory($path . '/Resources/lang/en', 0755, true);
        File::makeDirectory($path . '/Routes', 0755, true);
        File::makeDirectory($path . '/Config', 0755, true);

        File::put($path . '/Module.php', $this->getModuleTemplate($name, $slug, $studly, $namespace));
        File::put($path . '/Controllers/Admin/DashboardController.php', $this->getControllerTemplate($namespace, $studly));
        File::put($path . '/Services/' . $studly . 'Service.php', $this->getServiceTemplate($namespace, $studly));
        File::put($path . '/Routes/admin.php', $this->getRoutesTemplate($slug, $studly));
        File::put($path . '/Config/config.php', "<?php

return [
    'name' => '{$name}',
    'version' => '1.0.0',
];");
        File::put($path . '/manifest.json', json_encode([
            'name' => $name,
            'slug' => $slug,
            'type' => 'module',
            'version' => '1.0.0',
            'author' => $config['author'] ?? 'NeuroCMS',
            'description' => $config['description'] ?? '',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'path' => $path,
            'namespace' => $namespace,
            'message' => 'Module generated successfully',
        ];
    }

    public function generateComponent(array $config): array
    {
        $result = $this->generateModule($config);
        if (!$result['success']) return $result;

        $path = $result['path'];
        $name = $config['name'];
        $slug = Str::slug($name);
        $studly = Str::studly($name);
        $namespace = 'App\\Modules\\' . $studly;

        File::put($path . '/Module.php', $this->getComponentTemplate($name, $slug, $studly, $namespace));

        $manifest = json_decode(File::get($path . '/manifest.json'), true);
        $manifest['type'] = 'component';
        File::put($path . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $result['message'] = 'Component generated successfully';
        return $result;
    }

    public function generatePlugin(array $config): array
    {
        $name = $config['name'];
        $slug = Str::slug($name);
        $studly = Str::studly($name);
        $namespace = 'App\\Plugins\\' . $studly;
        $path = app_path('Plugins/' . $studly);

        if (File::isDirectory($path)) {
            return ['success' => false, 'error' => 'Plugin already exists'];
        }

        File::makeDirectory($path, 0755, true);
        File::makeDirectory($path . '/Services', 0755, true);

        File::put($path . '/Plugin.php', $this->getPluginTemplate($name, $slug, $studly, $namespace));
        File::put($path . '/manifest.json', json_encode([
            'name' => $name,
            'slug' => $slug,
            'type' => 'plugin',
            'version' => '1.0.0',
            'author' => $config['author'] ?? 'NeuroCMS',
            'description' => $config['description'] ?? '',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'path' => $path,
            'namespace' => $namespace,
            'message' => 'Plugin generated successfully',
        ];
    }

    public function exportToZip(string $slug, string $type = 'module'): ?string
    {
        if ($type === 'plugin') {
            $modulePath = app_path('Plugins/' . Str::studly($slug));
        } else {
            $modulePath = app_path('Modules/' . Str::studly($slug));
        }

        if (!File::isDirectory($modulePath)) {
            return null;
        }

        $zipName = "{$slug}-{$type}.zip";
        $zipPath = storage_path("app/exports/{$zipName}");

        if (!File::isDirectory(dirname($zipPath))) {
            File::makeDirectory(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = File::allFiles($modulePath);
        foreach ($files as $file) {
            $relativePath = str_replace($modulePath . '/', '', $file->getPathname());
            $zip->addFile($file->getPathname(), $relativePath);
        }

        $zip->close();

        return $zipPath;
    }

    protected function getModuleTemplate(string $name, string $slug, string $studly, string $namespace): string
    {
        return "<?php

namespace {$namespace};

use App\Core\Foundation\Module;

class Module extends Module
{
    protected string \$name = '{$name}';
    protected string \$slug = '{$slug}';
    protected string \$version = '1.0.0';
    protected array \$dependencies = [];
    
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        \$this->loadRoutes();
        \$this->loadViews();
        \$this->loadTranslations();
        \$this->loadMigrations();
        \$this->loadConfig();
    }
    
    public function install(): bool
    {
        \$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        return true;
    }
    
    public function uninstall(): bool
    {
        return true;
    }
    
    public function update(string \$oldVersion): bool
    {
        return true;
    }
}";
    }

    protected function getComponentTemplate(string $name, string $slug, string $studly, string $namespace): string
    {
        return "<?php

namespace {$namespace};

use App\Core\Foundation\Component;

class Module extends Component
{
    protected string \$name = '{$name}';
    protected string \$slug = '{$slug}';
    protected string \$version = '1.0.0';
    protected string \$icon = 'puzzle-piece';
    protected string \$adminRoute = 'admin.{$slug}.index';
    protected array \$dependencies = [];
    
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        \$this->loadRoutes();
        \$this->loadViews();
        \$this->loadTranslations();
        \$this->loadMigrations();
        \$this->loadConfig();
    }
    
    public function install(): bool
    {
        \$this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        return true;
    }
    
    public function uninstall(): bool
    {
        return true;
    }
    
    public function update(string \$oldVersion): bool
    {
        return true;
    }
}";
    }

    protected function getPluginTemplate(string $name, string $slug, string $studly, string $namespace): string
    {
        return "<?php

namespace {$namespace};

use App\Core\Foundation\Plugin;

class Plugin extends Plugin
{
    protected string \$name = '{$name}';
    protected string \$slug = '{$slug}';
    protected string \$version = '1.0.0';
    
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        //
    }
    
    public function install(): bool
    {
        return true;
    }
    
    public function uninstall(): bool
    {
        return true;
    }
}";
    }

    protected function getControllerTemplate(string $namespace, string $studly): string
    {
        return "<?php

namespace {$namespace}\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('{$studly}::admin.index');
    }
}";
    }

    protected function getServiceTemplate(string $namespace, string $studly): string
    {
        return "<?php

namespace {$namespace}\Services;

class {$studly}Service
{
    public function __construct()
    {
        //
    }
}";
    }

    protected function getRoutesTemplate(string $slug, string $studly): string
    {
        return "<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Modules\{$studly}\Controllers\Admin\DashboardController::class, 'index'])->name('admin.{$slug}.index');
";
    }
}
