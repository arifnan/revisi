<?php

namespace App\Providers;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
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
    app()->register(RouteServiceProvider::class);
     JsonResource::withoutWrapping();
      
      Relation::morphMap([
            'teacher' => \App\Models\Teacher::class,
            'student' => \App\Models\Student::class,
            // Anda juga bisa menambahkan model lain jika perlu, misalnya:
            // 'admin' => \App\Models\Admin::class,
        ]);
	}
}
