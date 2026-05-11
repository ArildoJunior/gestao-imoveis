<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    /**
     * Lista o histórico de backups.
     */
    public function index()
    {
        $backups = Backup::orderByDesc('created_at')->paginate(20);

        return view('backups.index', compact('backups'));
    }

    /**
     * Dispara um backup manual e registra com o usuário autenticado.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $status = 'SUCESSO';
        $mensagemErro = null;

        try {
            Artisan::call('backup:run');
            $output = Artisan::output();
        } catch (\Throwable $e) {
            $status = 'FALHA';
            $mensagemErro = $e->getMessage();
        }

        Backup::create([
            'tipo'                  => 'MANUAL',
            'caminho_arquivo'       => null,
            'tamanho_bytes'         => null,
            'status'                => $status,
            'mensagem_erro'         => $mensagemErro,
            'realizado_por_user_id' => $user?->id,
        ]);

        if ($status === 'SUCESSO') {
            return redirect()
                ->route('backups.index')
                ->with('success', 'Backup manual executado com sucesso.');
        }

        return redirect()
            ->route('backups.index')
            ->with('error', 'Falha ao executar backup manual: ' . $mensagemErro);
    }
}