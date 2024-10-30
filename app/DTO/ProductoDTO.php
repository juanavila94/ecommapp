<?php 

namespace App\DTO;

use Illuminate\Foundation\Http\FormRequest;

class ProductoDTO
{
     protected array $requiredFields = [
          'title',
          'price',
     ];

     public function __construct(
          public ?string $title,
          public ?float $price,
     )
     {     
     }

     public static function fromRequest(FormRequest $request): ProductoDTO
     {
          return new self(
               title: $request->validated('title'),
               price: (float) $request->validated('price')
          );
     }
}