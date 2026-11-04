<?php

namespace App\Providers;

use App\Models\ExpenseReport;
use App\Models\Payment;
use App\Models\Requisition;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureMorphMap();
        $this->configureAuthorization();
    }

    /**
     * Store short aliases instead of class names in polymorphic columns
     * (`approvals.approvable_type`, `model_has_roles.model_type`).
     */
    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'requisition' => Requisition::class,
            'expense_report' => ExpenseReport::class,
            'payment' => Payment::class,
        ]);
    }

    /**
     * Grant every ability to administrators before any permission or policy is checked.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(fn (User $user, string $ability): ?bool => $user->hasRole('admin') ? true : null);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
