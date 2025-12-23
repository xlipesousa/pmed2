<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            $isAdmin = $user->role === 'admin';
            Log::info('Gate admin check', ['user' => $user->email, 'role' => $user->role, 'isAdmin' => $isAdmin]);
            return $isAdmin;
        });

        // Permissão para administradores e usuários com papel pagamento (acesso total)
        Gate::define('admin-or-pagamento', function ($user) {
            return $user->role === 'admin' || $user->role === 'pagamento';
        });
        
        // Nova permissão para visualização (admin, pagamento e auditores)
        Gate::define('mapas-view', function ($user) {
            return $user->role === 'admin' || $user->role === 'pagamento' || $user->role === 'auditor';
        });
        
        // Nova permissão para ações de modificação (apenas admin e pagamento)
        Gate::define('mapas-manage', function ($user) {
            return $user->role === 'admin' || $user->role === 'pagamento';
        });

        // ADICIONAR: Gate para anulação de pacotes (apenas admin)
        Gate::define('anular-pacotes', function (User $user) {
            return $user->role === 'admin';
        });
    }
}