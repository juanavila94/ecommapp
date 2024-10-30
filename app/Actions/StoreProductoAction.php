<?php

namespace App\Actions;

use App\DTO\ProductoDTO;
use App\Models\Producto;

class StoreProductoAction
{
     public function execute(ProductoDTO $dto)
     {
          
     $data = [
          'title' => $dto->title,
          'price' => $dto->price,
          'created_at' => now(),
     ];
 
     $stored = Producto::storeJson($data);
      
      return $stored;

     }
}
