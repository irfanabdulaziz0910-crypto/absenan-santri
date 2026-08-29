<?php

namespace App\Providers;

use App\Models\Admin;
use Illuminate\Support\Facades\App;
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
        if (! App::runningInConsole()) {
            $admin = Admin::where('username', 'admin')->first();

            if (! $admin) {
                Admin::create([
                    'name' => 'Admin Utama',
                    'username' => 'admin',
                    'password' => 'admin',
                ]);

                return;
            }

            $legacyPasswords = ['admin', 'admin123', 'password123'];
            $isLegacyDefault = in_array($admin->password, $legacyPasswords, true)
                || $admin->validatePassword('admin123')
                || $admin->validatePassword('password123');

            if ($isLegacyDefault && ! $admin->validatePassword('admin')) {
                $admin->password = 'admin';
                $admin->save();
            }
        }
    }
}
