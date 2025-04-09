<?php

namespace App\Http\Controllers;

use App\Models\Lotacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LotacaoController extends Controller
{
    public function index()
    {
        return Lotacao::with(['pessoa', 'unidade'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pessoa_id' => 'required|exists:pessoas,id',
            'unidade_id' => 'required|exists:unidades,id',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'cargo' => 'required|string|max:100',
            'status' => 'required|in:ativo,inativo',
        ]);

        return DB::transaction(function () use ($validated) {
            return Lotacao::create($validated);
        });
    }

    public function show(Lotacao $lotacao)
    {
        return $lotacao->load(['pessoa', 'unidade']);
    }

    public function update(Request $request, Lotacao $lotacao)
    {
        $validated = $request->validate([
            'pessoa_id' => 'sometimes|exists:pessoas,id',
            'unidade_id' => 'sometimes|exists:unidades,id',
            'data_inicio' => 'sometimes|date',
            'data_fim' => 'nullable|date|after:data_inicio',
            'cargo' => 'sometimes|string|max:100',
            'status' => 'sometimes|in:ativo,inativo',
        ]);

        return DB::transaction(function () use ($lotacao, $validated) {
            $lotacao->update($validated);
            return $lotacao->fresh();
        });
    }

    public function destroy(Lotacao $lotacao)
    {
        return DB::transaction(function () use ($lotacao) {
            $lotacao->delete();
            return response()->noContent();
        });
    }
}
