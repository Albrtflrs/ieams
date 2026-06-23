<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\IncomeTransaction;
use App\Models\ExpenseTransaction;
use App\Models\Supplier;
use App\Models\Quotation;
use App\Models\Retainer;
use App\Models\User;
use App\Models\MiscellaneousTransaction; // 👈 ADD THIS
use App\Policies\ClientPolicy;
use App\Policies\IncomePolicy;
use App\Policies\ExpensePolicy;
use App\Policies\SupplierPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\RetainerPolicy;
use App\Policies\UserPolicy;
use App\Policies\MiscellaneousPolicy; // 👈 ADD THIS
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Client::class => ClientPolicy::class,
        IncomeTransaction::class => IncomePolicy::class,
        ExpenseTransaction::class => ExpensePolicy::class,
        Supplier::class => SupplierPolicy::class,
        Quotation::class => QuotationPolicy::class,
        Retainer::class => RetainerPolicy::class,
        User::class => UserPolicy::class,
        MiscellaneousTransaction::class => MiscellaneousPolicy::class, // ✅ already here
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // 🔥 Super-admin bypass – case‑insensitive & trimmed
        Gate::before(function ($user) {
            if (trim(strtolower($user->role)) === 'super_admin') {
                return true;
            }
            return null;
        });

        // Optional: define gates for non‑model permissions
        Gate::define('manage-users', fn($user) => in_array($user->role, ['super_admin', 'admin']));
        Gate::define('view-audit-logs', fn($user) => $user->role === 'super_admin');
        Gate::define('configure-settings', fn($user) => $user->role === 'super_admin');
        Gate::define('delete-any', fn($user) => in_array($user->role, ['super_admin', 'admin', 'manager']));
    }
}