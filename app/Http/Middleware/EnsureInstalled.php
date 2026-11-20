<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

// Runs before sessions start. Applies new migrations after an install or upgrade (no SSH needed),
// then sends everyone to /setup until an owner account exists; afterwards /setup is gone.
class EnsureInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (! app()->runningUnitTests()) {
            $this->migrateIfNeeded();
        }

        $installed = User::query()->exists();

        if (! $installed && ! $request->routeIs('setup*')) {
            return redirect()->route('setup');
        }
        if ($installed && $request->routeIs('setup*')) {
            abort(404);
        }

        return $next($request);
    }

    private function migrateIfNeeded(): void
    {
        $marker = storage_path('app/migrated');
        $current = implode(',', array_map('basename', glob(database_path('migrations/*.php'))));

        if (@file_get_contents($marker) !== $current) {
            Artisan::call('migrate', ['--force' => true]);
            file_put_contents($marker, $current, LOCK_EX);
        }
    }
}
