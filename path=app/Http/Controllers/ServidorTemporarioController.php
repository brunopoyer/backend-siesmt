<?php

namespace App\Http\Controllers;

use App\Models\ServidorTemporario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ServidorTemporarioController extends Controller
{
    public function update(Request $request, ServidorTemporario $servidorTemporario): JsonResponse
    {
        $validated = $request->validate([
            // Validações existentes...
            'endereco' => 'required|array',
            'endereco.tipo_logradouro' => 'required|string|max:20',
            'endereco.logradouro' => 'required|string|max:200',
            'endereco.numero' => 'required|string|max:10',
            'endereco.complemento' => 'nullable|string|max:100',
            'endereco.bairro' => 'required|string|max:100',
            'endereco.cep' => 'required|string|max:10',
            'endereco.cidade_id' => 'required|exists:cidades,cid_id',
            'endereco.tipo' => 'required|in:residencial,comercial,outro'
        ]);

        try {
            DB::beginTransaction();

            // Atualizações existentes...

            // Atualizar endereço
            $endereco = $servidorTemporario->pessoa->enderecos()->first(); // Supondo que cada pessoa tem apenas um endereço
            $endereco->update($validated['endereco']);

            DB::commit();

            return response()->json(
                $servidorTemporario->load(['pessoa.fotos', 'pessoa.enderecos.cidade', 'pessoa.lotacoes.unidade'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
