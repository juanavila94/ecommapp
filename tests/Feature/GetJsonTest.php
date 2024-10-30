<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GetJsonTest extends TestCase
{
    public function test_can_list_products()
    {
        // Preparar datos de prueba
        $testData = [
            'productos' => [
                [
                    'id' => 1,
                    'title' => 'Producto 1',
                    'price' => 1000,
                    'created_at' => '2024-12-12 00:00:00'
                ],
            ]
        ];

        Storage::disk('local')->put('productos.json', json_encode($testData));

        $response = $this->getJson('/api');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'productos' => [
                    '*' => [
                        'id',
                        'title',
                        'price',
                        'created_at'
                    ]
                ],
                'encontrados',
                'pagina',
                'limite',
                'paginas'
            ]);
    }



    public function test_can_delete_products()
    {
 
    $testData = [
        'productos' => [
            [
                'id' => 1,
                'title' => 'Producto 1',
                'price' => 1000,
                'created_at' => '2024-01-01 01:01:00'
            ],
            [
                'id' => 2,
                'title' => 'Producto 2',
                'price' => 2000,
                'created_at' => '2024-02-02 02:02:00'
            ]
        ]
    ];

    Storage::disk('local')->put('productos.json', json_encode($testData));

    $response = $this->deleteJson('/api/2');

    $response->assertStatus(200);

    $jsonContent = Storage::disk('local')->get('productos.json');
    $updatedData = json_decode($jsonContent, true);

    $this->assertCount(1, $updatedData['productos']);
    $this->assertNotContains(2, array_column($updatedData['productos'], 'id'));

    }
}
