<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\DocumentBlock;
use App\Models\Person;
use App\Models\PersonalAccessToken;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDetail;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Observers\TaskAutoExecuteObserver;
use App\Services\MarkdownRenderer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MarkdownRenderer::class);
    }

    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        Blade::directive('markdown', function ($expression) {
            return "<?php echo app(\App\Services\MarkdownRenderer::class)->toHtml($expression ?? ''); ?>";
        });

        $auditable = [
            Project::class,
            Document::class,
            DocumentBlock::class,
            Task::class,
            TaskDetail::class,
            Person::class,
            User::class,
        ];

        foreach ($auditable as $model) {
            $model::observe(AuditableObserver::class);
        }

        Task::observe(TaskAutoExecuteObserver::class);
    }
}
