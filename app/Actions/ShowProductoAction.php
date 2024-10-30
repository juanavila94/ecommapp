<?php

namespace App\Actions;

use App\Models\Producto;
use Exception;

class ShowProductoAction
{
     public function execute(int $id)
     {
          if (!isset($id)) {
               throw new Exception('ID de producto inválido');
           }
   
           $producto = Producto::getById($id);
   
           if ($producto === null) {
               throw new Exception("No se encontró el producto con ID: {$id}");
           }
   
           return $producto;
     }
}