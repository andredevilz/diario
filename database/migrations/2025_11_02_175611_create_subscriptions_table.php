<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyHasSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // não está logado → manda para login
        if (! $user) {
            return redirect()->route('login');
        }

        // não tem empresa → podes redirecionar para criar
        $company = $user->company;
        if (! $company) {
            return redirect('/')
                ->with('error', 'Cria primeiro uma empresa.');
        }

        // não tem subscrição → manda para página de planos
        $sub = $company->currentSubscription;
        if (! $sub || $sub->status !== 'active') {
            return redirect()->route('plans.index')
                ->with('error', 'Precisas de um plano ativo.');
        }

        return $next($request);
    }
}
