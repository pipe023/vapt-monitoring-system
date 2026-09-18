<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensurePublicStorageLink(public_path('storage'), storage_path('app/public'));
    }

    public function ensurePublicStorageLink(string $linkPath, string $targetPath): bool
    {
        if (is_link($linkPath) || file_exists($linkPath)) {
            return true;
        }

        $parentDir = dirname($linkPath);

        if (! is_dir($parentDir) && ! mkdir($parentDir, 0777, true) && ! is_dir($parentDir)) {
            return false;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            $command = sprintf(
                'cmd /c if exist "%s" rmdir /s /q "%s" & mklink /J "%s" "%s"',
                $linkPath,
                $linkPath,
                $linkPath,
                $targetPath,
            );

            exec($command, $output, $exitCode);

            return $exitCode === 0 && (is_link($linkPath) || file_exists($linkPath));
        }

        if (! function_exists('symlink')) {
            return false;
        }

        try {
            return symlink($targetPath, $linkPath);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
