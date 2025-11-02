<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Mostra o formulário (já fizemos no Blade)
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Trata o POST do registo
     */
    public function store(Request $request)
    {
        // 1) validar o que vem do formulário
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'        => ['nullable', 'string', 'max:50'],
            'company_name' => ['required', 'string', 'max:255'],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 2) criar o utilizador
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role'     => 'user', // todos os registos são user
        ]);

        // 3) criar a empresa dele
        $company = Company::create([
            'owner_id' => $user->id,
            'name'     => $validated['company_name'],
            'slug'     => 'empresa-' . $user->id, // simples para já
        ]);

        // 4) ligar o user à empresa
        $user->company_id = $company->id;
        $user->save();

        // 5) garantir que existe um plano "trial" na BD
        $trialPlan = Plan::firstOrCreate(
            ['slug' => 'trial'],
            [
                'name'        => 'Trial',
                'price_cents' => 0,
                'interval'    => 'trial',
                'features'    => null,
            ]
        );

        // 6) criar a subscrição trial de 7 dias
        Subscription::create([
            'company_id' => $company->id,
            'plan_id'    => $trialPlan->id,
            'status'     => 'active',
            'renews_at'  => now()->addDays(7),
        ]);

        // 7) disparar evento de registo (para emails, etc.)
        event(new Registered($user));

        // 8) fazer login automático
        Auth::login($user);

        // 9) mandar o gajo logo para a app
        return redirect()->route('app');
    }
}
