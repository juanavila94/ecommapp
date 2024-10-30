<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ecomm-App Challenge</title>

    <link href="{{ asset('/styles.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
        <section class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Precio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="product-list">
                    @foreach ($productos['productos'] as $producto)
                        <tr>
                            <td>{{ $producto['id'] }}</td>
                            <td>{{ $producto['title'] }}</td>
                            <td>{{ $producto['price'] }}</td>
                            <td>
                                <button class="edit-btn" data-id="{{ $producto['id'] }}" data-title="{{ $producto['title'] }}" data-price="{{ $producto['price'] }}">Editar</button>
                                <button class="delete-btn" data-id="{{ $producto['id'] }}">Eliminar</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
        <section class="form-container">
            <h2>Crear producto</h2>
            <form id="product-form">
                @csrf
                <input type="hidden" id="product-id" value="">
                <input type="text" id="product-title" placeholder="Título" required>
                <input type="number" id="product-price" placeholder="Precio" required>
                <button type="submit">Guardar</button>
            </form>
            <h3>Filtros</h3>
            <div class="filters">
                <input type="text" id="filter-title" placeholder="Filtrar por título">
                <input type="number" id="filter-price" placeholder="Filtrar por precio">
                <input type="date" id="filter-created-at">
                <button id="apply-filters">Aplicar Filtros</button>
                <button id="clear-filters">Limpiar Filtros</button>
            </div>
        </section> 
    </div>

    <script type="module">
        $(document).ready(function() {
            // OPERACIONES CRUD //
            
            // Submit
            $('#product-form').on('submit', function(e) {
                e.preventDefault();
                const title = $('#product-title').val();
                const price = $('#product-price').val();

                $.ajax({
                    url: '/productos',
                    method: 'POST',
                    data: {
                        title: title,
                        price: price,
                        "_token": $("meta[name='csrf-token']").attr("content")
                    },
                    success: function(data) {
                        if (data.success) {
                            $('#product-list').append(`
                                <tr>
                                    <td>${data.data.id}</td>
                                    <td>${data.data.title}</td>
                                    <td>${data.data.price}</td>
                                    <td>
                                        <button class="edit-btn" data-id="${data.data.id}" data-title="${data.data.title}" data-price="${data.data.price}">Editar</button>
                                        <button class="delete-btn" data-id="${data.data.id}">Eliminar</button>
                                    </td>
                                </tr>
                            `);
                            $('#product-form')[0].reset();
                        }
                    },
                    error: function() {
                        alert('Error al crear el producto');
                    }
                });
            });

            // Editar
            $('#product-list').on('click', '.edit-btn', function() {
                const product_id = $(this).data('id');
                const product_title = $(this).data('title');
                const product_price = $(this).data('price');
                const edit_form = $('#product-form');

                edit_form.find('#product-title').val(product_title);
                edit_form.find('#product-price').val(product_price);
                edit_form.find('#product-id').val(product_id);

                // .on/.off manejan el handler para el submit.
                edit_form.off('submit').on('submit', function(e) {
                    e.preventDefault();

                    $.ajax({
                        url: `/producto/${product_id}`,
                        method: 'PUT',
                        data: {
                            title: $('#product-title').val(),
                            price: $('#product-price').val(),
                            "_token": $("meta[name='csrf-token']").attr("content")
                        },
                        success: function(response) {
                            const row = $(`button[data-id="${product_id}"]`).closest('tr');
                            row.find('td:eq(1)').text($('#product-title').val());
                            row.find('td:eq(2)').text($('#product-price').val());
                            row.find('.edit-btn')
                                .data('title', $('#product-title').val())
                                .data('price', $('#product-price').val());

                            edit_form[0].reset();
                            edit_form.off('submit').on('submit', $('#product-form').data('originalSubmit'));
                        },
                        error: function() {
                            alert('Error al actualizar el producto');
                        }
                    });
                });
            });

            // Borrado
            $('#product-list').on('click', '.delete-btn', function() {
                const product_id = $(this).data('id');
                const row = $(this).closest('tr');

                $.ajax({
                    url: `/${product_id}`,
                    method: 'DELETE',
                    data: {
                        "_token": $("meta[name='csrf-token']").attr("content")
                    },
                    success: function() {
                        row.remove();
                    },
                    error: function() {
                        alert('Error al eliminar el producto');
                    }
                });
            });

            $('#product-form').data('originalSubmit', $('#product-form').submit);
   
            // FILTROS Y PAGINACION //
            
            let currentFilters = {};

            function loadProducts(filters = {}) {
                let queryParams = new URLSearchParams();
    
                Object.keys(filters).forEach(key => {
                    if (filters[key]) {
                        queryParams.append(key, filters[key]);
                    }
                });

                $.ajax({
                    url: `/productos?${queryParams.toString()}`,
                    method: 'GET',
                    success: function(response) {
                        if (typeof response === 'object') {
                            updateProductTable(response.data);
                        } else {
                            const tempDiv = $('<div>').html(response);
                            const productRows = tempDiv.find('#product-list tr');
                            $('#product-list').empty().append(productRows);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los productos:', error);
                        alert('Error al cargar los productos');
                    }
                });
            }

            function updateProductTable(products) {
                const tbody = $('#product-list');
                tbody.empty();
                
                products.forEach(product => {
                    tbody.append(`
                        <tr>
                            <td>${product.id}</td>
                            <td>${product.title}</td>
                            <td>${product.price}</td>
                            <td>
                                <button class="edit-btn" data-id="${product.id}" 
                                        data-title="${product.title}" 
                                        data-price="${product.price}">Editar</button>
                                <button class="delete-btn" data-id="${product.id}">Eliminar</button>
                            </td>
                        </tr>
                    `);
                });
            }

            // aplicar filtros //quedo el created_at
            $('#apply-filters').on('click', function() {
                currentFilters = {
                    title: $('#filter-title').val(),
                    price: $('#filter-price').val(),
                    created_at: $('#filter-created-at').val()
                };
                loadProducts(currentFilters);
            });

            //  limpiar filtros
            $('#clear-filters').on('click', function() {
                $('#filter-title').val('');
                $('#filter-price').val('');
                $('#filter-created-at').val('');
                currentFilters = {};
                loadProducts(currentFilters); 
            });

            loadProducts();
        });
    </script>
</body>
</html>
