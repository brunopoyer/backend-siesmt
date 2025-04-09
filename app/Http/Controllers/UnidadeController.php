<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UnidadeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $unidades = Unidade::with(['enderecos.cidade', 'lotacoes.pessoa'])
            ->paginate($request->per_page ?? 15);
        return response()->json($unidades);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'unid_nome' => 'required|string|max:200',
            'unid_sigla' => 'required|string|max:20',
            'enderecos' => 'sometimes|array',
            'enderecos.*.end_tipo_logradouro' => 'required|string|max:30',
            'enderecos.*.end_logradouro' => 'required|string|max:200',
            'enderecos.*.end_numero' => 'required|integer',
            'enderecos.*.end_bairro' => 'required|string|max:100',
            'enderecos.*.cid_id' => 'required|exists:cidades,cid_id'
        ]);

        try {
            DB::beginTransaction();

            $unidade = Unidade::create([
                'unid_nome' => $validated['unid_nome'],
                'unid_sigla' => $validated['unid_sigla']
            ]);

            if (isset($validated['enderecos'])) {
                foreach ($validated['enderecos'] as $endereco) {
                    $unidade->enderecos()->create($endereco);
                }
            }

            DB::commit();

            return response()->json(
                $unidade->load(['enderecos.cidade']),
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Unidade $unidade): JsonResponse
    {
        return response()->json(
            $unidade->load(['enderecos.cidade', 'lotacoes.pessoa'])
        );
    }

    public function update(Request $request, Unidade $unidade): JsonResponse
    {
        $validated = $request->validate([
            'unid_nome' => 'required|string|max:200',
            'unid_sigla' => 'required|string|max:20'
        ]);

        $unidade->update($validated);
        return response()->json($unidade);
    }

    public function destroy(Unidade $unidade): JsonResponse
    {
        try {
            DB::beginTransaction();

            // A exclusão em cascata cuidará das relações
            $unidade->delete();

            DB::commit();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
