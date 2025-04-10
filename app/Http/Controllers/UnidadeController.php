<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnidadeController extends Controller
{
    public function index()
    {
        return Unidade::with(['enderecos.cidade'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'sigla' => 'required|string|max:10|unique:unidades,unid_sigla',
            'enderecos' => 'required|array',
            'enderecos.*.tipo_logradouro' => 'required|string|max:20',
            'enderecos.*.logradouro' => 'required|string|max:200',
            'enderecos.*.numero' => 'required|string|max:10',
            'enderecos.*.complemento' => 'nullable|string|max:100',
            'enderecos.*.bairro' => 'required|string|max:100',
            'enderecos.*.cep' => 'required|string|max:10',
            'enderecos.*.cidade_id' => 'required|exists:cidades,cid_id'
        ]);

        return DB::transaction(function () use ($validated) {
            $unidade = Unidade::create([
                'unid_nome' => $validated['nome'],
                'unid_sigla' => $validated['sigla'],
            ]);

            foreach ($validated['enderecos'] as $enderecoData) {
                $endereco = Endereco::create([
                    'end_tipo_logradouro' => $enderecoData['tipo_logradouro'],
                    'end_logradouro' => $enderecoData['logradouro'],
                    'end_numero' => $enderecoData['numero'],
                    'end_complemento' => $enderecoData['complemento'] ?? null,
                    'end_bairro' => $enderecoData['bairro'],
                    'end_cep' => $enderecoData['cep'],
                    'cid_id' => $enderecoData['cidade_id']
                ]);

                $unidade->enderecos()->attach($endereco->end_id);
            }

            return response()->json($unidade->load('enderecos.cidade'), 201);
        });
    }

    public function update(Request $request, Unidade $unidade)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:255',
            'sigla' => 'sometimes|string|max:10|unique:unidades,unid_sigla,' . $unidade->unid_id . ',unid_id',
            'telefone' => 'sometimes|string',
            'enderecos' => 'sometimes|array',
            'enderecos.*.end_id' => 'sometimes|exists:enderecos,end_id',
            'enderecos.*.logradouro' => 'required_without:enderecos.*.end_id|string|max:200',
            'enderecos.*.numero' => 'required_without:enderecos.*.end_id|string|max:10',
            'enderecos.*.complemento' => 'nullable|string|max:100',
            'enderecos.*.bairro' => 'required_without:enderecos.*.end_id|string|max:100',
            'enderecos.*.cep' => 'required_without:enderecos.*.end_id|string|max:10',
            'enderecos.*.cidade_id' => 'required_without:enderecos.*.end_id|exists:cidades,cid_id'
        ]);

        return DB::transaction(function () use ($unidade, $validated, $request) {
            if (isset($validated['nome'])) {
                $unidade->unid_nome = $validated['nome'];
            }
            if (isset($validated['sigla'])) {
                $unidade->unid_sigla = $validated['sigla'];
            }
            if (isset($validated['telefone'])) {
                $unidade->unid_telefone = $validated['telefone'];
            }
            $unidade->save();

            // Atualizar endereços
            if ($request->has('enderecos')) {
                foreach ($validated['enderecos'] as $enderecoData) {
                    if (isset($enderecoData['end_id'])) {
                        // Atualizar endereço existente
                        $endereco = Endereco::find($enderecoData['end_id']);
                        $endereco->update($enderecoData);
                    } else {
                        // Criar novo endereço
                        $endereco = Endereco::create([
                            'end_logradouro' => $enderecoData['logradouro'],
                            'end_numero' => $enderecoData['numero'],
                            'end_complemento' => $enderecoData['complemento'] ?? null,
                            'end_bairro' => $enderecoData['bairro'],
                            'end_cep' => $enderecoData['cep'],
                            'cid_id' => $enderecoData['cidade_id']
                        ]);
                        $unidade->enderecos()->attach($endereco->end_id);
                    }
                }
            }

            return response()->json($unidade->load('enderecos.cidade'));
        });
    }

    public function destroy(Unidade $unidade)
    {
        return DB::transaction(function () use ($unidade) {
            $unidade->delete();
            return response()->noContent();
        });
    }
}
