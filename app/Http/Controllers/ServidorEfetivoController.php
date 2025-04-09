<?php

namespace App\Http\Controllers;

use App\Models\ServidorEfetivo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ServidorEfetivoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $servidores = ServidorEfetivo::with('lotacao.unidade')
            ->paginate($request->per_page ?? 15);
        return response()->json($servidores);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pes_nome' => 'required|string|max:255',
            'pes_data_nascimento' => 'required|date',
            'lot_id' => 'required|exists:lotacoes,lot_id',
            'foto' => 'nullable|image|max:5120'
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('fotos', 'minio');
            $validated['pes_foto'] = $path;
        }

        $servidor = ServidorEfetivo::create($validated);
        return response()->json($servidor, 201);
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
        $servidores = ServidorEfetivo::whereHas('lotacao', function ($query) use ($unidId) {
            $query->where('unid_id', $unidId);
        })
        ->with('lotacao.unidade')
        ->select('pes_nome', 'pes_data_nascimento', 'pes_foto')
        ->paginate($request->per_page ?? 15);

        foreach ($servidores as $servidor) {
            if ($servidor->pes_foto) {
                $servidor->foto_url = Storage::disk('minio')
                    ->temporaryUrl($servidor->pes_foto, now()->addMinutes(5));
            }
        }

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
}
