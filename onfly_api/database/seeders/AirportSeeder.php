<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirportSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $airports = [
            ['iata_code' => 'CGH', 'name' => 'Congonhas–Deputado Freitas Nobre Airport', 'city' => 'São Paulo', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'GRU', 'name' => 'São Paulo/Guarulhos–Governor André Franco Montoro International Airport', 'city' => 'São Paulo', 'state' => 'SP', 'country' => 'Brasil'],
            ['iata_code' => 'GIG', 'name' => 'Rio Galeão – Tom Jobim International Airport', 'city' => 'Rio de Janeiro', 'state' => 'RJ', 'country' => 'Brasil'],
            ['iata_code' => 'SDU', 'name' => 'Santos Dumont Airport', 'city' => 'Rio de Janeiro', 'state' => 'RJ', 'country' => 'Brasil'],
            ['iata_code' => 'CNF', 'name' => 'Tancredo Neves International Airport', 'city' => 'Belo Horizonte', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'GVR', 'name' => 'Coronel Altino Machado Airport', 'city' => 'Governador Valadares', 'state' => 'MG', 'country' => 'Brasil'],
            ['iata_code' => 'POA', 'name' => 'Porto Alegre-Salgado Filho International Airport', 'city' => 'Porto Alegre', 'state' => 'RS', 'country' => 'Brasil'],
            ['iata_code' => 'CXJ', 'name' => 'Hugo Cantergiani Regional Airport', 'city' => 'Caxias do Sul', 'state' => 'RS', 'country' => 'Brasil'],
            ['iata_code' => 'IGU', 'name' => 'Cataratas International Airport', 'city' => 'Foz do Iguaçu', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'CWB', 'name' => 'Curitiba-Afonso Pena International Airport', 'city' => 'Curitiba', 'state' => 'PR', 'country' => 'Brasil'],
            ['iata_code' => 'SSA', 'name' => 'Deputado Luiz Eduardo Magalhães International Airport', 'city' => 'Salvador', 'state' => 'BA', 'country' => 'Brasil'],
            ['iata_code' => 'BPS', 'name' => 'Porto Seguro International Airport', 'city' => 'Porto Seguro', 'state' => 'BA', 'country' => 'Brasil'],
            ['iata_code' => 'REC', 'name' => 'Recife/Guararapes - Gilberto Freyre International Airport', 'city' => 'Recife', 'state' => 'PE', 'country' => 'Brasil'],
            ['iata_code' => 'FEN', 'name' => 'Fernando de Noronha Airport', 'city' => 'Fernando de Noronha', 'state' => 'PE', 'country' => 'Brasil'],
            ['iata_code' => 'FOR', 'name' => 'Pinto Martins International Airport', 'city' => 'Fortaleza', 'state' => 'CE', 'country' => 'Brasil'],
            ['iata_code' => 'JJD', 'name' => 'Comandante Ariston Pessoa Airport', 'city' => 'Cruz', 'state' => 'CE', 'country' => 'Brasil'],
            ['iata_code' => 'FLN', 'name' => 'Hercílio Luz International Airport', 'city' => 'Florianópolis', 'state' => 'SC', 'country' => 'Brasil'],
            ['iata_code' => 'NVT', 'name' => 'Ministro Victor Konder International Airport', 'city' => 'Navegantes', 'state' => 'SC', 'country' => 'Brasil'],
            ['iata_code' => 'CGR', 'name' => 'Campo Grande Airport', 'city' => 'Campo Grande', 'state' => 'MS', 'country' => 'Brasil'],
            ['iata_code' => 'CMG', 'name' => 'Corumbá International Airport', 'city' => 'Corumbá', 'state' => 'MS', 'country' => 'Brasil'],
        ];

        $payload = array_map(fn ($a) => array_merge($a, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $airports);

        DB::table('airports')->upsert(
            $payload,
            ['iata_code'],
            ['name', 'city', 'state', 'country', 'updated_at']
        );
    }
}
