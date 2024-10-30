<?php

namespace App\Actions;

use App\Models\Producto;

class ListProductoAction
{
     public function execute(int $page = 1, int $limit = 5, array $filters = []): array
     {
         return Producto::getJsonData($page, $limit, $filters);
     }

}