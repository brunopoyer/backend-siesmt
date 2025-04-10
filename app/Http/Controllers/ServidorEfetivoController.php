<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\ServidorEfetivo;
use App\Models\Endereco;
use App\Models\FotoPessoa;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServidorEfetivoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $servidores = ServidorEfetivo::with([
            'pessoa.fotos',
            'pessoa.enderecos.cidade',
            'pessoa.lotacoes.unidade.enderecos'
        ])->paginate($request->per_page ?? 15);

        return response()->json($servidores);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pes_nome' => 'required|string|max:255',
            'pes_data_nascimento' => 'required|date',
            'pes_sexo' => 'required|string|max:9',
            'pes_mae' => 'required|string|max:200',
            'pes_pai' => 'required|string|max:200',
            'se_matricula' => 'required|string|max:20|unique:servidor_efetivo,se_matricula',
            'se_data_admissao' => 'required|date',
            'unid_id' => 'required|exists:unidades,unid_id',
            'foto' => 'nullable|image|max:5120',
            // Campos do endereço
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

        return DB::transaction(function () use ($request, $validated) {
            // Criar pessoa
            $pessoa = Pessoa::create([
                'pes_nome' => $validated['pes_nome'],
                'pes_data_nascimento' => $validated['pes_data_nascimento'],
                'pes_sexo' => $validated['pes_sexo'],
                'pes_mae' => $validated['pes_mae'],
                'pes_pai' => $validated['pes_pai']
            ]);

            // Criar servidor efetivo
            $servidor = ServidorEfetivo::create([
                'pes_id' => $pessoa->pes_id,
                'se_matricula' => $validated['se_matricula'],
                'se_data_admissao' => $validated['se_data_admissao']
            ]);

            // Criar lotação
            $pessoa->lotacoes()->create([
                'unid_id' => $validated['unid_id'],
                'lot_data_lotacao' => $validated['se_data_admissao'],
                'lot_status' => 'ativo'
            ]);

            // Criar endereço
            $endereco = Endereco::create([
                'end_tipo_logradouro' => $validated['endereco']['tipo_logradouro'],
                'end_logradouro' => $validated['endereco']['logradouro'],
                'end_numero' => $validated['endereco']['numero'],
                'end_complemento' => $validated['endereco']['complemento'] ?? null,
                'end_bairro' => $validated['endereco']['bairro'],
                'end_cep' => $validated['endereco']['cep'],
                'cid_id' => $validated['endereco']['cidade_id']
            ]);

            // Associar endereço à pessoa
            $pessoa->enderecos()->attach($endereco->end_id, [
                'pend_tipo' => $validated['endereco']['tipo']
            ]);

            // Upload da foto se fornecida
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('fotos', 'minio');
                FotoPessoa::create([
                    'pes_id' => $pessoa->pes_id,
                    'fp_data' => now(),
                    'fp_bucket' => config('filesystems.disks.minio.bucket'),
                    'fp_hash' => $path
                ]);
            }

            return response()->json(
                $servidor->load(['pessoa.fotos', 'pessoa.enderecos.cidade', 'pessoa.lotacoes.unidade']),
                201
            );
        });
    }

    public function show(ServidorEfetivo $servidorEfetivo): JsonResponse
    {
        $servidorEfetivo->load('lotacao.unidade');

        if ($servidorEfetivo->pes_foto) {
            $servidorEfetivo->foto_url = Storage::disk('minio')
                ->temporaryUrl($servidorEfetivo->pes_foto, now()->addMinutes(5));
        }

        return response()->json($servidorEfetivo);
    }

    public function update(Request $request, ServidorEfetivo $servidorEfetivo): JsonResponse
    {
        $validated = $request->validate([
            'pes_nome' => 'required|string|max:255',
            'pes_data_nascimento' => 'required|date',
            'lot_id' => 'required|exists:lotacoes,lot_id',
            'foto' => 'nullable|image|max:5120'
        ]);

        if ($request->hasFile('foto')) {
            if ($servidorEfetivo->pes_foto) {
                Storage::disk('minio')->delete($servidorEfetivo->pes_foto);
            }
            $path = $request->file('foto')->store('fotos', 'minio');
            $validated['pes_foto'] = $path;
        }

        $servidorEfetivo->update($validated);
        return response()->json($servidorEfetivo);
    }

    public function destroy(ServidorEfetivo $servidorEfetivo): JsonResponse
    {
        if ($servidorEfetivo->pes_foto) {
            Storage::disk('minio')->delete($servidorEfetivo->pes_foto);
        }
        $servidorEfetivo->delete();
        return response()->json(null, 204);
    }

    public function porUnidade(Request $request, $unidId): JsonResponse
    {
        $servidores = ServidorEfetivo::whereHas('pessoa.lotacoes', function ($query) use ($unidId) {
            $query->where('unid_id', $unidId)
                  ->where('lot_status', 'ativo');
        })
        ->with(['pessoa.fotos', 'pessoa.lotacoes.unidade'])
        ->get()
        ->map(function ($servidor) {
            $pessoa = $servidor->pessoa;
            $idade = $pessoa->pes_data_nascimento->age;
            $lotacao = $pessoa->lotacoes->where('lot_status', 'ativo')->first();
            $foto = $pessoa->fotos->first();

            return [
                'nome' => $pessoa->pes_nome,
                'idade' => $idade,
                'unidade_lotacao' => $lotacao ? $lotacao->unidade->unid_nome : null,
                'foto_url' => $foto ? Storage::disk('minio')->temporaryUrl($foto->fp_hash, now()->addMinutes(5)) : null
            ];
        });

        return response()->json($servidores);
    }

    public function buscarPorNome(Request $request): JsonResponse
    {
        $nome = $request->query('nome');

        $servidores = ServidorEfetivo::where('pes_nome', 'like', "%{$nome}%")
            ->with('lotacao.unidade')
            ->paginate($request->per_page ?? 15);

        return response()->json($servidores);
    }

    public function buscarEnderecoPorNome(Request $request): JsonResponse
    {
        $request->validate(['nome' => 'required|string|min:3']);

        $servidores = ServidorEfetivo::whereHas('pessoa', function ($query) use ($request) {
            $query->where('pes_nome', 'ilike', "%{$request->nome}%");
        })
        ->with(['pessoa.lotacoes.unidade.enderecos.cidade'])
        ->get()
        ->map(function ($servidor) {
            $pessoa = $servidor->pessoa;
            $lotacao = $pessoa->lotacoes->where('lot_status', 'ativo')->first();

            if (!$lotacao) {
                return null;
            }

            $unidade = $lotacao->unidade;
            $endereco = $unidade->enderecos->first();

            return [
                'servidor' => $pessoa->pes_nome,
                'unidade' => $unidade->unid_nome,
                'endereco' => $endereco ? [
                    'logradouro' => $endereco->end_logradouro,
                    'numero' => $endereco->end_numero,
                    'complemento' => $endereco->end_complemento,
                    'bairro' => $endereco->end_bairro,
                    'cidade' => $endereco->cidade->cid_nome,
                    'estado' => $endereco->cidade->cid_uf,
                    'cep' => $endereco->end_cep
                ] : null
            ];
        })
        ->filter()
        ->values();

        return response()->json($servidores);
    }
}
