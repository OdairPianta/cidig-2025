<?php

namespace Database\Seeders;

use App\Models\University;
use Illuminate\Database\Seeder;

class UniversitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = [
            'Universidade de São Paulo (USP)',
            'Universidade Estadual de Campinas (Unicamp)',
            'Instituto Federal de Santa Catarina (IFSC)',
            'Atopos - USP',
            'Universidade do Vale do Rio dos Sinos (Unisinos)',
            'Universidade Anhembi Morumbi (UAM)',
            'Universidade Federal de Ouro Preto (UFOP)',
            'Universidade Estadual da Paraíba (UEPB)',
            'Universidade Federal de São Paulo (Unifesp)',
            'Faculdade Paulus de Comunicação (Fapcom)',
            'Università degli Studi Firenzi (UniFI)',
            'The Oxford Blockchain Foundation (OXBF) e The Global Strategy',
            'Universidade Federal de Juiz de Fora (UFJF)',
            'Universidade Federal de Goiás',
            'Universidade Nova de Lisboa (UNL)',
            'National University of Ireland Galway (NUI Galway)',
            'Universidade Lusófona do Porto (ULP)',
            'MIT',
            'Université d’Ottawa (uOttawa)',
            'Université de Paris (Sorbonne)',
            'Sapienza Università di Roma (Sapienza)',
            'Università degli Studi della Tuscia (Unitus)',
            'University of Toronto (UofT)',
            'Universidad Autónoma Metropolitana-Xochimilco (UAM-X)',
            'Unico'
        ];

        foreach ($names as $name) {
            University::create(['name' => $name]);
        }
    }
}
