<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Company;
use App\Models\DiaryEntry;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DiaryAiController;
use App\Http\Controllers\DiaryReportController;

/*
|--------------------------------------------------------------------------
| Página inicial
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard (já é a app com o gravador)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    // últimos 5 diários (se não existir a tabela, fica vazio)
    $entries = collect();
    if (class_exists(DiaryEntry::class)) {
        $entries = DiaryEntry::query()
            ->when(optional($user->company)->id, fn($q, $cid) => $q->where('company_id', $cid))
            ->latest()
            ->take(5)
            ->get();
    }

    return view('dashboard', compact('entries'));
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Rotas de autenticação (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rotas de perfil (Breeze)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Criar empresa
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // mostrar formulário
    Route::get('/company/create', function () {
        $user = auth()->user();

        // se já tem empresa, vai logo para a app
        if ($user->company) {
            return redirect()->route('app');
        }

        return view('company.create');
    })->name('company.create');

    // guardar empresa
    Route::post('/company', function (Request $request) {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $company = Company::create([
            'owner_id' => $user->id,
            'name'     => $data['name'],
            'slug'     => 'empresa-'.$user->id,
        ]);

        // ligar o user à empresa
        $user->company_id = $company->id;
        $user->save();

        return redirect()->route('plans.index')
            ->with('success', 'Empresa criada! Agora escolhe um plano.');
    })->name('company.store');
});

/*
|--------------------------------------------------------------------------
| Área principal do SaaS
|--------------------------------------------------------------------------
*/
Route::get('/app', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    $company = $user->company;
    if (! $company) {
        return redirect()->route('company.create')
            ->with('error', 'Cria primeiro uma empresa.');
    }

    $sub = $company->currentSubscription;
    if (! $sub || $sub->status !== 'active') {
        return redirect()->route('plans.index')->with('error', 'Precisas de um plano.');
    }

    // se o trial/plano já passou a data -> obriga a escolher plano
    if ($sub->renews_at && $sub->renews_at->isPast()) {
        return redirect()->route('plans.index')
            ->with('error', 'O teu trial terminou. Escolhe um plano.');
    }

    return view('app.home', [
        'company'      => $company,
        'subscription' => $sub,
    ]);
})->middleware('auth')->name('app');

/*
|--------------------------------------------------------------------------
| Planos
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // listar planos
    Route::get('/plans', function () {
        $plans = Plan::orderBy('price_cents')->get();

        return view('plans.index', compact('plans'));
    })->name('plans.index');

    // escolher plano
    Route::post('/plans/{plan}/choose', function (Plan $plan) {
        $user = auth()->user();
        $company = $user->company;

        if (! $company) {
            return redirect()->route('company.create')->with('error', 'Cria primeiro uma empresa.');
        }

        // cancelar antigas (simples)
        $company->subscriptions()->update(['status' => 'canceled']);

        // criar nova
        Subscription::create([
            'company_id' => $company->id,
            'plan_id'    => $plan->id,
            'status'     => 'active',
            'renews_at'  => now()->addMonth(),
        ]);

        return redirect()->route('app')->with('success', 'Plano alterado para '.$plan->name.'.');
    })->name('plans.choose');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // UTILIZADORES
    Route::get('/admin/users', [AdminUserController::class, 'index'])
        ->name('admin.users');

    Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])
        ->name('admin.users.edit');

    Route::put('/admin/users/{user}', [AdminUserController::class, 'update'])
        ->name('admin.users.update');

    // SUBSCRIÇÕES
    Route::get('/admin/subscriptions', [AdminSubscriptionController::class, 'index'])
        ->name('admin.subscriptions');
});

/*
|--------------------------------------------------------------------------
| Diário de obra (UI + API)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('diario')->name('diario.')->group(function () {
    // página principal do diário
    Route::get('/', function () {
        return view('diario.index');
    })->name('index'); // => diario.index

    // upload do áudio
    Route::post('/upload', [DiaryAiController::class, 'store'])->name('upload'); // => diario.upload

    // página de relatórios
    Route::get('/relatorios', [DiaryReportController::class, 'index'])->name('reports');
    
    // ✨ NOVO: EDITAR / GUARDAR
    Route::get('/relatorios/{entry}/edit', [\App\Http\Controllers\DiaryReportController::class, 'edit'])
        ->name('reports.edit');
    Route::put('/relatorios/{entry}', [\App\Http\Controllers\DiaryReportController::class, 'update'])
        ->name('reports.update');
        Route::delete('/relatorios/{entry}', [\App\Http\Controllers\DiaryReportController::class, 'destroy'])->name('reports.destroy');


});

// Alias antigo opcional (mantém se tens front a chamar isto)
Route::middleware('auth')->post('/ai/diary', [DiaryAiController::class, 'store'])->name('ai.diary');
