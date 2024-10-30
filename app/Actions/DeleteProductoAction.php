<?php

namespace App\Actions;

use App\Models\Producto;
use Exception;

class DeleteProductoAction
{
     public function execute(int $id)
     {
          if (!isset($id)) {
               throw new Exception('ID de producto inválido');
           }
   
           $producto = Producto::delete($id);
   
           if ($producto === null) {
               throw new Exception("No se encontró el producto con ID: {$id}");
           }
   
           return $producto;
     }
}