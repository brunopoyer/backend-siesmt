<?php

namespace App\Http\Controllers;

use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnidadeController extends Controller
{
    public function index()
    {
        return Unidade::with(['enderecos'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:200',
            'sigla' => 'required|string|max:20|unique:unidades',
        ]);

        return DB::transaction(function () use ($validated) {
            return Unidade::create($validated);
        });
    }

    public function show(Unidade $unidade)
    {
        return $unidade->load(['enderecos']);
    }

    public function update(Request $request, Unidade $unidade)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:200',
            'sigla' => 'sometimes|string|max:20|unique:unidades,sigla,' . $unidade->id,
        ]);

        return DB::transaction(function () use ($unidade, $validated) {
            $unidade->update($validated);
            return $unidade->fresh();
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
