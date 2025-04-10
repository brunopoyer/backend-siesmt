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
        $request->validate([
            'pessoa_id' => 'required|exists:pessoas,pes_id',
            'unidade_id' => 'required|exists:unidades,unid_id',
            'data_inicio' => 'required|date',
            'cargo' => 'required|string',
            'status' => 'required|in:ativo,inativo'
        ]);

        $lotacao = new Lotacao();
        $lotacao->pes_id = $request->pessoa_id;
        $lotacao->unid_id = $request->unidade_id;
        $lotacao->lot_data_lotacao = $request->data_inicio;
        $lotacao->lot_portaria = $request->portaria ?? null;
        $lotacao->save();

        return response()->json($lotacao, 201);
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
