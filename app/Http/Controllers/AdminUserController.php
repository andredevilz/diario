<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    private function assertAdmin(): void
    {
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Acesso reservado a administradores.');
        }
    }

    public function index()
    {
        $this->assertAdmin();

        $users = User::with(['company', 'company.currentSubscription.plan'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user)
    {
        $this->assertAdmin();

        $plans = Plan::orderBy('price_cents')->get();
        $user->load(['company', 'company.currentSubscription.plan']);

        return view('admin.users.edit', [
            'user'  => $user,
            'plans' => $plans,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:50'],
            'role'     => ['required', Rule::in(['user', 'admin'])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'plan_id'  => ['nullable', 'exists:plans,id'],
        ]);

        // atualizar dados básicos
        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->role  = $data['role'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // garantir empresa
        $company = $user->company;
        if (! $company) {
            $company = Company::create([
                'owner_id' => $user->id,
                'name'     => $user->name . ' — Empresa',
                'slug'     => 'empresa-' . $user->id,
            ]);
            $user->company_id = $company->id;
            $user->save();
        }

        // se o admin escolheu um plano → criar nova subscrição
        if (! empty($data['plan_id'])) {
            // cancelar antigas
            $company->subscriptions()->update(['status' => 'canceled']);

            Subscription::create([
                'company_id' => $company->id,
                'plan_id'    => $data['plan_id'],
                'status'     => 'active',
                'renews_at'  => now()->addMonth(),
            ]);
        }

        return redirect()
            ->route('admin.users')
            ->with('success', 'Utilizador atualizado.');
    }
}
