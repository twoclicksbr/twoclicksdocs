<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RestoreSandboxFromProdDumpJob;
use App\Models\SandboxDump;
use Illuminate\Http\Request;

class ManutencaoController extends Controller
{
    public function index()
    {
        $last      = SandboxDump::orderByDesc('id')->first();
        $running   = SandboxDump::where('status', 'running')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->orderByDesc('id')
            ->first();
        $history   = SandboxDump::with('executedBy')
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        $isProd = app()->environment('production');

        return view('admin.manutencao.index', compact('last', 'running', 'history', 'isProd'));
    }

    public function dumpSandbox(Request $request)
    {
        if (app()->environment('production')) {
            return redirect()->route('admin.manutencao.index')
                ->with('error', 'Operação não permitida em produção.');
        }

        $request->validate([
            'confirmation' => 'required|string|in:ATUALIZAR',
        ], [
            'confirmation.in' => 'Digite exatamente ATUALIZAR para confirmar.',
        ]);

        // Bloqueio: se já tem um running recente, não permite outro
        $alreadyRunning = SandboxDump::where('status', 'running')
            ->where('created_at', '>=', now()->subMinutes(30))
            ->exists();

        if ($alreadyRunning) {
            return redirect()->route('admin.manutencao.index')
                ->with('error', 'Já há um dump em execução. Aguarde a conclusão.');
        }

        $dump = SandboxDump::create([
            'executed_by_person_id' => optional($request->user()?->person)->id,
            'status'                => 'running',
        ]);

        RestoreSandboxFromProdDumpJob::dispatch($dump->id);

        return redirect()->route('admin.manutencao.index')
            ->with('success', "Dump #{$dump->id} disparado. Acompanhe o status nesta página.");
    }
}
