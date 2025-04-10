<?php

namespace App\Http\Controllers;

use App\Models\Cidade;
use Illuminate\Http\Request;

class CidadeController extends Controller
{
    public function index()
    {
        $cidades = Cidade::all();
        return response()->json($cidades);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cid_nome' => 'required|string|max:255',
            'cid_uf' => 'required|string|max:2'
        ]);

        $cidade = Cidade::create($validated);
        return response()->json($cidade, 201);
    }

    public function show(Cidade $cidade)
    {
        return response()->json($cidade);
    }

    public function update(Request $request, Cidade $cidade)
    {
        $validated = $request->validate([
            'cid_nome' => 'sometimes|string|max:255',
            'cid_uf' => 'sometimes|string|max:2'
        ]);

        $cidade->update($validated);
        return response()->json($cidade);
    }

    public function destroy(Cidade $cidade)
    {
        $cidade->delete();
        return response()->json(null, 204);
    }
}
