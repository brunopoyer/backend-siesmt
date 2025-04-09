<?php

namespace App\Http\Controllers;

use App\Models\Lotacao;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LotacaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lotacoes = Lotacao::with(['pessoa', 'unidade.enderecos.cidade'])
            ->paginate($request->per_page ?? 15);
        return response()->json($lotacoes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pes_id' => 'required|exists:pessoas,pes_id',
            'unid_id' => 'required|exists:unidades,unid_id',
            'lot_data_lotacao' => 'required|date',
            'lot_data_remocao' => 'nullable|date|after:lot_data_lotacao',
            'lot_portaria' => 'nullable|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            // Verificar se já existe uma lotação ativa para a pessoa
            $lotacaoAtiva = Lotacao::where('pes_id', $validated['pes_id'])
                ->whereNull('lot_data_remocao')
                ->first();

            if ($lotacaoAtiva) {
                // Finalizar a lotação atual
                $lotacaoAtiva->update([
                    'lot_data_remocao' => $validated['lot_data_lotacao']
                ]);
            }

            $lotacao = Lotacao::create($validated);

            DB::commit();

            return response()->json(
                $lotacao->load(['pessoa', 'unidade.enderecos.cidade']),
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Lotacao $lotacao): JsonResponse
    {
        return response()->json(
            $lotacao->load(['pessoa', 'unidade.enderecos.cidade'])
        );
    }

    public function update(Request $request, Lotacao $lotacao): JsonResponse
    {
        $validated = $request->validate([
            'lot_data_lotacao' => 'required|date',
            'lot_data_remocao' => 'nullable|date|after:lot_data_lotacao',
            'lot_portaria' => 'nullable|string|max:100'
        ]);

        try {
            DB::beginTransaction();

            $lotacao->update($validated);

            DB::commit();

            return response()->json(
                $lotacao->load(['pessoa', 'unidade.enderecos.cidade'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Lotacao $lotacao): JsonResponse
    {
        try {
            DB::beginTransaction();

            $lotacao->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
