<?php

namespace App\Models;


use Illuminate\Support\Facades\Storage;

class Producto
{
    protected $connection = 'json';
    protected $table = 'productos.json';

    protected $fillable = [
        'title',
        'price',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

// METODO PARA RECUPERAR TODOS LOS PRODUCTOS //
    public static function getJsonData(int $page = 1, int $limit = 5, array $filters = []): array
    {
    
        $filepath = 'productos.json';

        if (!Storage::disk('local')->exists($filepath)) {
                    return ['productos' => []];
        }
        
        $jsonRaw = Storage::disk('local')->get($filepath);
        
        $json = json_decode($jsonRaw, true);
   
        $productos = $json['productos'] ?? [];

        if (!empty($filters)) {
            $productos = array_filter($productos, function ($producto) use ($filters) {
              
                $matches = false;     

                if (isset($filters['title'])) {
                    $matches = $matches || stripos($producto['title'], $filters['title']) !== false;
                }
                
                if (isset($filters['price'])) {
                    $matches = $matches || $producto['price'] == $filters['price'];
                }
                
                if (isset($filters['created_at'])) {
                    $matches = $matches || $producto['created_at'] == $filters['created_at'];
                }
                
                return $matches;
                
            });
        }
        

    $offset = ($page - 1) * $limit;
    $paginatedProductos = array_slice($productos, $offset, $limit);

    return [
        'productos' => $paginatedProductos,
        'encontrados' => count($productos),
        'pagina' => $page,
        'limite' => $limit,
        'paginas' => ceil(count($productos) / $limit),
    ];
    
}

// METODO DE GUARDADO //
public static function storeJson(array $data): array
{
    
    $productos = self::getJsonData();
    
    do {
        $newId = rand(1, 1000); 
    } while (array_search($newId, array_column($productos['productos'], 'id')) !== false);
    
    $data['id'] = $newId;
    
    $data['created_at'] = now()->format('Y-m-d H:i:s');
    
    $productos['productos'][] = $data;

    $prodTotales = count($productos['productos']);
    
    $productos = [
        'productos' => $productos['productos'],
        'encontrados' => $prodTotales,
        'pagina' => 1,
        'limite' => 5,
        'paginas' => ceil($prodTotales / 5),
    ];
    $jsonContent = json_encode($productos, JSON_PRETTY_PRINT);
    
    Storage::disk('local')->put('productos.json', $jsonContent);
    
        return $data;
    }

//  METODO PARA ACTUALIZAR  //
    public static function updateJson(array $data, int $id): array
    {
        $productos = self::getJsonData();

        if (empty($productos['productos'])) {
            return null;
        }
        
        $updated = false;
        
        foreach ($productos['productos'] as $key => $producto) {
            if ($producto['id'] === $id) {
                $data['id'] = $id;
                $data['created_at'] = $producto['created_at'];
                $productos['productos'][$key] = $data;
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            $jsonContent = json_encode($productos, JSON_PRETTY_PRINT);
            Storage::disk('local')->put('productos.json', $jsonContent);
            
            return $data;
        }
        return null;
    }

// METODO PARA MOSTRAR POR ID //
    public static function getById(int $id)
    {
        $productos = self::getJsonData();

        if (empty($productos['productos'])) {
            return null;
        }
        
        foreach($productos['productos'] as $key => $producto){
            if($producto['id'] === $id){
                return $producto;
            }
        }
        return null;
    }

// METODO PARA BORRAR //
    public static function delete(int $id)
    {
        $productos = self::getJsonData();

        if (empty($productos['productos'])) {
            return null;
        }

        $exists = false;
        foreach($productos['productos'] as $key => $producto) {
            if($producto['id'] === $id) {
                unset($productos['productos'][$key]);
                $exists = true;
                break;
            }
        }

        if ($exists) {
            $productos['productos'] = array_values($productos['productos']);
            $jsonContent = json_encode($productos, JSON_PRETTY_PRINT);
            return Storage::disk('local')->put('productos.json', $jsonContent);
        }

        return false;
    }
}
