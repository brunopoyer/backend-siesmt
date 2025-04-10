<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CidadeSeeder extends Seeder
{
    public function run()
    {
        DB::table('cidades')->insert([
            ['cid_nome' => 'Cidade Alpha', 'cid_uf' => 'CA'],
            ['cid_nome' => 'Cidade Beta', 'cid_uf' => 'CB'],
            ['cid_nome' => 'Cidade Gamma', 'cid_uf' => 'CG'],
            ['cid_nome' => 'Cidade Delta', 'cid_uf' => 'CD'],
            ['cid_nome' => 'Cidade Epsilon', 'cid_uf' => 'CE']
        ]);
    }
}
