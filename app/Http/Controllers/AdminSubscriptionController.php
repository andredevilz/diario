<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Plan;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    private function assertAdmin(): void
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Acesso reservado a administradores.');
        }
    }

    public function index(Request $request)
    {
        $this->assertAdmin();

        // filtros vindos do GET
        $statusFilter = $request->query('status');      // active | expired | trial | canceled
        $planFilter   = $request->query('plan');        // id do plano
        $renewsTo     = $request->query('renews_to');   // data YYYY-MM-DD

        // base query
        $query = Subscription::with([
            'company',
            'company.owner',
            'plan',
        ])->orderBy('created_at', 'desc');

        // aplicar filtros
        if ($statusFilter === 'active') {
            $query->where('status', 'active')
                  ->where(function ($q) {
                      $q->whereNull('renews_at')->orWhere('renews_at', '>', now());
                  });
        } elseif ($statusFilter === 'expired') {
            // expirado = renews_at passado OU status canceled
            $query->where(function ($q) {
                $q->whereNotNull('renews_at')->where('renews_at', '<', now())
                  ->orWhere('status', 'canceled');
            });
        } elseif ($statusFilter === 'trial') {
            $query->whereHas('plan', function ($q) {
                $q->where('slug', 'trial');
            });
        } elseif ($statusFilter === 'canceled') {
            $query->where('status', 'canceled');
        }

        if (!empty($planFilter)) {
            $query->where('plan_id', $planFilter);
        }

        if (!empty($renewsTo)) {
            $query->whereDate('renews_at', '<=', $renewsTo);
        }

        // paginação
        $subs = $query->paginate(20)->appends($request->query());

        // métricas (sem filtros – visão geral)
        $allSubs = Subscription::with('plan')->get();

        $metrics = [
            'total'     => $allSubs->count(),
            'active'    => $allSubs->filter(function ($s) {
                return $s->status === 'active'
                    && (
                        $s->renews_at === null
                        || $s->renews_at->isFuture()
                    );
            })->count(),
            'trial'     => $allSubs->filter(function ($s) {
                return $s->plan && $s->plan->slug === 'trial';
            })->count(),
            'expired'   => $allSubs->filter(function ($s) {
                return ($s->renews_at && $s->renews_at->isPast()) || $s->status === 'canceled';
            })->count(),
        ];

        // para o select de planos
        $plans = Plan::orderBy('price_cents')->get();

        return view('admin.subscriptions.index', [
            'subs'    => $subs,
            'metrics' => $metrics,
            'plans'   => $plans,
            'filters' => [
                'status'    => $statusFilter,
                'plan'      => $planFilter,
                'renews_to' => $renewsTo,
            ],
        ]);
    }
}
