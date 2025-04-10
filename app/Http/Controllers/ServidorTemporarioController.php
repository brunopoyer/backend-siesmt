<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use App\Models\ServidorTemporario;
use App\Models\FotoPessoa;
use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServidorTemporarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $servidores = ServidorTemporario::with([
                'pessoa.fotos',
                'pessoa.enderecos.cidade',
                'pessoa.lotacoes.unidade'
            ])
            ->paginate($request->per_page ?? 15);

        return response()->json($servidores);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pes_nome' => 'required|string|max:200',
            'pes_data_nascimento' => 'required|date',
            'pes_sexo' => 'required|string|max:9',
            'pes_mae' => 'required|string|max:200',
            'pes_pai' => 'required|string|max:200',
            'st_data_admissao' => 'required|date',
            'st_data_demissao' => 'nullable|date|after:st_data_admissao',
            'foto' => 'nullable|image|max:5120',
            'unid_id' => 'required|exists:unidades,unid_id',
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

            // Criar servidor temporário
            $servidor = ServidorTemporario::create([
                'pes_id' => $pessoa->pes_id,
                'st_data_admissao' => $validated['st_data_admissao'],
                'st_data_demissao' => $validated['st_data_demissao'] ?? null
            ]);

            // Criar lotação
            $pessoa->lotacoes()->create([
                'unid_id' => $validated['unid_id'],
                'lot_data_lotacao' => $validated['st_data_admissao'],
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

    public function show(ServidorTemporario $servidorTemporario): JsonResponse
    {
        $servidorTemporario->load([
            'pessoa.fotos',
            'pessoa.enderecos.cidade',
            'pessoa.lotacoes.unidade'
        ]);

        // Gerar URLs temporárias para as fotos
        foreach ($servidorTemporario->pessoa->fotos as $foto) {
            $foto->url_temporaria = Storage::disk('minio')
                ->temporaryUrl($foto->fp_hash, now()->addMinutes(5));
        }

        return response()->json($servidorTemporario);
    }

    public function update(Request $request, ServidorTemporario $servidorTemporario): JsonResponse
    {
        $validated = $request->validate([
            'pes_nome' => 'required|string|max:200',
            'pes_data_nascimento' => 'required|date',
            'pes_sexo' => 'required|string|max:9',
            'pes_mae' => 'required|string|max:200',
            'pes_pai' => 'required|string|max:200',
            'st_data_admissao' => 'required|date',
            'st_data_demissao' => 'nullable|date|after:st_data_admissao',
            'foto' => 'nullable|image|max:5120'
        ]);

        try {
            DB::beginTransaction();

            // Atualizar pessoa
            $servidorTemporario->pessoa->update([
                'pes_nome' => $validated['pes_nome'],
                'pes_data_nascimento' => $validated['pes_data_nascimento'],
                'pes_sexo' => $validated['pes_sexo'],
                'pes_mae' => $validated['pes_mae'],
                'pes_pai' => $validated['pes_pai']
            ]);

            // Atualizar servidor temporário
            $servidorTemporario->update([
                'st_data_admissao' => $validated['st_data_admissao'],
                'st_data_demissao' => $validated['st_data_demissao'] ?? null
            ]);

            // Upload da nova foto se fornecida
            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('fotos', 'minio');
                FotoPessoa::create([
                    'pes_id' => $servidorTemporario->pes_id,
                    'fp_data' => now(),
                    'fp_bucket' => config('filesystems.disks.minio.bucket'),
                    'fp_hash' => $path
                ]);
            }

            DB::commit();

            return response()->json(
                $servidorTemporario->load(['pessoa.fotos', 'pessoa.lotacoes.unidade'])
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(ServidorTemporario $servidorTemporario): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Excluir fotos do MinIO
            foreach ($servidorTemporario->pessoa->fotos as $foto) {
                Storage::disk('minio')->delete($foto->fp_hash);
            }

            // A exclusão em cascata cuidará do resto
            $servidorTemporario->pessoa->delete();

            DB::commit();

            return response()->json(null, 204);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
