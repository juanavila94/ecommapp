<?php

namespace App\Actions;

use App\DTO\ProductoDTO;
use App\Models\Producto;
use Exception;

class UpdateProductoAction
{
     public function execute(ProductoDTO $dto, $id)
     {

          if ($id <= 0) {
               throw new Exception('ID de producto inválido');
          }

          $data = [

               'title' => $dto->title,
               'price' => $dto->price,
               'created_at' => now(),

          ];

          $updated = Producto::updateJson($data, $id);

          return $updated;
     }
}
