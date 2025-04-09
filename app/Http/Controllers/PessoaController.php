<?php

namespace App\Http\Controllers;

use App\Models\Pessoa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PessoaController extends Controller
{
    public function index()
    {
        return Pessoa::with(['enderecos', 'fotos'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:200',
            'cpf' => 'required|string|max:11|unique:pessoas',
            'data_nascimento' => 'required|date',
            'email' => 'required|email|max:100',
            'telefone' => 'required|string|max:20',
        ]);

        return DB::transaction(function () use ($validated) {
            return Pessoa::create($validated);
        });
    }

    public function show(Pessoa $pessoa)
    {
        return $pessoa->load(['enderecos', 'fotos']);
    }

    public function update(Request $request, Pessoa $pessoa)
    {
        $validated = $request->validate([
            'nome' => 'sometimes|string|max:200',
            'cpf' => 'sometimes|string|max:11|unique:pessoas,cpf,' . $pessoa->id,
            'data_nascimento' => 'sometimes|date',
            'email' => 'sometimes|email|max:100',
            'telefone' => 'sometimes|string|max:20',
        ]);

        return DB::transaction(function () use ($pessoa, $validated) {
            $pessoa->update($validated);
            return $pessoa->fresh();
        });
    }

    public function destroy(Pessoa $pessoa)
    {
        return DB::transaction(function () use ($pessoa) {
            $pessoa->delete();
            return response()->noContent();
        });
    }
}
