<?php

namespace App\Http\Controllers;

use App\Actions\DeleteProductoAction;
use App\Actions\ListProductoAction;
use App\Actions\ShowProductoAction;
use App\Actions\StoreProductoAction;
use App\Actions\UpdateProductoAction;
use App\DTO\ProductoDTO;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use App\Responses\DataResponse;
use JustSteveKing\StatusCode\Http;


class ProductoController extends Controller
{

    public function index(ListProductoAction $action)
    {
        $page = request()->get('page', 1); 
        $limit = request()->get('limit', 5); 

        $filters = [];
        if (request()->has('title')) {
        $filters['title'] = request()->get('title');
        }
        if (request()->has('price')) {
        $filters['price'] = request()->get('price');
        }
        if (request()->has('created_at')) {
        $filters['created_at'] = request()->get('created_at');
        }
     
        $productos = $action->execute($page, $limit, $filters);

        return view('index', compact('productos'));
    }

    public function show(ShowProductoAction $action, int $id)
    {
        try {

            $producto = $action->execute($id);

            return new DataResponse(
                data: $producto,
                errors: [],
                status: Http::ACCEPTED
            );
        } catch (\Exception $e) {
            return new DataResponse(
                data: [],
                errors: [$e->getMessage()],
                status: Http::NOT_FOUND
            );
        }
    }

    public function store(StoreProductoRequest $request, StoreProductoAction $action): DataResponse
    {

        try {
            $dto = ProductoDTO::fromRequest($request);
            $producto = $action->execute($dto);

            return new DataResponse(
                data: $producto,
                errors: [],
                status: Http::CREATED
            );
        } catch (\Exception $e) {
            return new DataResponse(
                data: [],
                errors: [$e->getMessage()],
                status: Http::INTERNAL_SERVER_ERROR
            );
        }
    }

    public function update(UpdateProductoRequest $request, UpdateProductoAction $action, int $id): DataResponse
    {

        try {
            $dto = ProductoDTO::fromRequest($request);
            $producto = $action->execute($dto, $id);

            return new DataResponse(
                data: $producto,
                errors: [],
                status: Http::CREATED
            );
        } catch (\Exception $e) {
            return new DataResponse(
                data: [],
                errors: [$e->getMessage()],
                status: Http::INTERNAL_SERVER_ERROR
            );
        }
    }

    public function destroy(DeleteProductoAction $action, int $id)
    {
        try {
            $action->execute($id);

            return new DataResponse(
                data: ['message' => 'Producto eliminado correctamente'],
                errors: [],
                status: Http::OK
            );
        } catch (\Exception $e) {
            return new DataResponse(
                data: [],
                errors: [$e->getMessage()],
                status: Http::NOT_FOUND
            );
        }
    }
}
