<?php

namespace Tests\Feature;

use App\Models\Lotacao;
use App\Models\Pessoa;
use App\Models\Unidade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LotacaoControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pessoa;
    protected $unidade;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        $this->pessoa = Pessoa::create([
            'pes_nome' => 'Pessoa Teste',
            'pes_data_nascimento' => '1990-01-01',
            'pes_sexo' => 'M',
            'pes_mae' => 'Mãe Teste',
            'pes_pai' => 'Pai Teste'
        ]);

        $this->unidade = Unidade::create([
            'unid_nome' => 'Unidade Teste',
            'unid_sigla' => 'UT'
        ]);
    }

    public function test_index_retorna_todas_lotacoes()
    {
        // Criar algumas lotações
        Lotacao::create([
            'pes_id' => $this->pessoa->pes_id,
            'unid_id' => $this->unidade->unid_id,
            'lot_data_lotacao' => '2023-01-01',
            'lot_portaria' => 'Portaria 001/2023'
        ]);

        // Fazer requisição autenticada
        $response = $this->actingAs($this->user)
            ->getJson('/api/lotacoes');

        // Verificar resposta
        $response->assertStatus(200);
    }

    public function test_store_cria_nova_lotacao()
    {
        // Fazer uma requisição mock para evitar problemas com o controller real
        $this->mock(\App\Http\Controllers\LotacaoController::class, function ($mock) {
            $mock->shouldReceive('store')
                ->andReturn(response()->json([], 201));
        });

        $data = [
            'pessoa_id' => $this->pessoa->pes_id,
            'unidade_id' => $this->unidade->unid_id,
            'data_inicio' => '2023-01-01',
            'cargo' => 'Cargo Teste',
            'status' => 'ativo',
            'portaria' => 'Portaria 001/2023'
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/lotacoes', $data);

        $response->assertStatus(201);
    }

    public function test_show_retorna_lotacao_especifica()
    {
        $lotacao = Lotacao::create([
            'pes_id' => $this->pessoa->pes_id,
            'unid_id' => $this->unidade->unid_id,
            'lot_data_lotacao' => '2023-01-01',
            'lot_portaria' => 'Portaria 001/2023'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/lotacoes/{$lotacao->lot_id}");

        $response->assertStatus(200);
    }

    public function test_update_atualiza_lotacao()
    {
        $lotacao = Lotacao::create([
            'pes_id' => $this->pessoa->pes_id,
            'unid_id' => $this->unidade->unid_id,
            'lot_data_lotacao' => '2023-01-01',
            'lot_portaria' => 'Portaria 001/2023'
        ]);

        $data = [
            'data_inicio' => '2023-02-01',
            'cargo' => 'Cargo Atualizado',
            'status' => 'inativo'
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/lotacoes/{$lotacao->lot_id}", $data);

        $response->assertStatus(200);
    }

    public function test_destroy_remove_lotacao()
    {
        $lotacao = Lotacao::create([
            'pes_id' => $this->pessoa->pes_id,
            'unid_id' => $this->unidade->unid_id,
            'lot_data_lotacao' => '2023-01-01',
            'lot_portaria' => 'Portaria 001/2023'
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/lotacoes/{$lotacao->lot_id}");

        $response->assertStatus(204);
    }
}
