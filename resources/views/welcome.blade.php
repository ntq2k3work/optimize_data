    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Danh sách Sản phẩm</title>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="bg-gray-100 font-sans">

        <div class="container mx-auto p-4">
            <input type="search" id="search" name="search" placeholder="Tìm kiếm sản phẩm..."
                class="w-full max-w-md px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm" />

            <h2 class="text-3xl font-semibold text-gray-800 mb-4">Danh sách Sản phẩm</h2>
            <button id="delete" type="button">Xoá</button>
            <button id="order" type="button">Mua</button>

            <div id="order-form" class="hidden mt-4">
                <h3 class="text-2xl font-semibold mb-2">Thông tin Đặt hàng</h3>
                <div id="order-products"></div>
                <button id="submit-order" class="bg-blue-500 text-white px-4 py-2 rounded">Xác nhận Đặt hàng</button>
            </div>

            <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
                <table class="min-w-full table-auto">
                    <thead class="bg-gray-200 text-gray-600">
                        <tr>
                            <th class="px-4 py-2 text-left">
                                <input type="checkbox" id="select-all" />
                            </th>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Name</th>
                            <th class="px-4 py-2 text-left">Unit</th>
                            <th class="px-4 py-2 text-left">Description</th>
                            <th class="px-4 py-2 text-left">Price</th>
                            <th class="px-4 py-2 text-left">Quantity</th>
                            <th class="px-4 py-2 text-left">Image</th>
                        </tr>
                    </thead>
                    <tbody id="product-list" class="text-gray-800">
                        <!-- Sản phẩm sẽ được hiển thị ở đây -->
                    </tbody>
                </table>
            </div>

            <ul id="pagination" class="flex justify-center items-center space-x-2 mt-4"></ul>
        </div>
    <script>


        let _products = [];
        $(document).ready(function () {
        let searchTimeout;
        let $selectedProduct = JSON.parse(localStorage.getItem('selectedProduct') || '[]');

    // Tìm kiếm
        $('#search').on('input', function () {
            clearTimeout(searchTimeout); // Clear any previous timeout
            const keyword = $(this).val().trim();

            searchTimeout = setTimeout(() => {
                loadProducts(keyword);
            }, 500);
        });

        // Mua hàng
        $('#order').on('click', function () {
            if ($selectedProduct.length > 0) {
                // Hiển thị form đặt hàng
                if($('#order-form').hasClass("hidden")){
                    $('#order-form').removeClass('hidden');
                    $('#order-products').empty();

                    $selectedProduct.forEach(productId => {
                        const product = $('#product-list').find(`.product-row[data-id="${productId}"]`);
                        const productName = product.find('.product-name').text();
                        const productPrice = product.find('.product-price').text();

                        $('#order-products').append(`
                            <div class="mb-4">
                                <label for="quantity-${productId}" class="block text-gray-600">${productName} (Giá: ${productPrice})</label>
                                <input type="number" id="quantity-${productId}" class="w-20 px-2 py-1 border border-gray-300 rounded" value="1" min="1">
                            </div>
                        `);

                        // Lưu thông tin sản phẩm
                        selectedProductsData.push({ id: productId, name: productName, price: productPrice });
                    });
                }else{
                    $('#order-form').addClass('hidden');
                    loadProducts();
                }
            } else {
                alert('Vui lòng chọn ít nhất một sản phẩm!');
            }
        });

        // Xác nhận Đặt hàng
        $('#submit-order').on('click', function () {
            const orderData = selectedProductsData.map(product => {
                const quantity = $(`#quantity-${product.id}`).val();
                return {
                    product_id: product.id,
                    quantity: quantity
                };
            });

            // Gửi yêu cầu đặt hàng (ở đây là ví dụ gửi POST, cần thay đổi URL API và xử lý)
            $.ajax({
                type: 'POST',
                url: '/api/products/order',
                data: JSON.stringify(orderData),
                contentType: 'application/json',
                success: function (response) {
                    $('#order-form').addClass('hidden');
                    selectedProductsData = [];
                    loadProducts(); // Tải lại danh sách sản phẩm
                },
                error: function (error) {
                    console.error('Lỗi:', error);
                }
            });
        });


    async function loadProducts(keyword = '', page = 1) {
        $.ajax({
            url: `api/products?page=${page}&keyword=${keyword}`,
            method: "GET",
        success: async function (response) {
            let products = response.products.data;
            showProduct(products);
            updatePagination(response.page);

            products.forEach(product => {
                loadStatusInventory(product.id);
            });
        },
        error: function (error) {
            console.error("Có lỗi khi tải sản phẩm:", error);
        }
        });
    }

    // async function loadInventory(id) {
    //     return $.ajax({
    //         url: `/api/products/inventory/${id}`,
    //         method: 'GET',
    //         success: function (data) {
    //             console.log(data);
    //             $(`.inventory_${id}`).text(data['message']);

    //         },
    //         error: function (error) {
    //             if (error.statusText !== 'abort') {
    //                 console.error(`Có lỗi xảy ra khi tải tồn kho của sản phẩm ${id}:`, error);
    //             }
    //         }
    //     });
    // }

    async function loadStatusInventory(id) {
        return $.ajax({
            url: `api/products/inventory/status/${id}`,
            method: 'GET',
            success: function (data) {
                console.log(`Trạng thái tồn kho của sản phẩm ${id}:`, data);
                $(`.inventory_${id}`).text(data['quantity']);
                $(`.inventory_${id}`).text(data['status']);
            },
            error: function (error) {
                console.error(`Có lỗi xảy ra khi tải trạng thái tồn kho của sản phẩm ${id}:`, error);
            }
        });
    }
    function showProduct(products) {
        $('#product-list').empty();
        let productRows = '';
        products.forEach(function (product) {
            const isChecked = $selectedProduct.includes(product.id);

            productRows += `
                <tr class="border-b hover:bg-gray-50 product-row" data-id="${product.id}">
                    <td class="px-4 py-2">
                        <input type="checkbox" class="product-checkbox" data-checkbox="${product.id}" ${isChecked ? 'checked' : ''} />
                    </td>
                    <td class="px-4 py-2">${product.id}</td>
                    <td class="px-4 py-2 product-name">${product.name}</td>
                    <td class="px-4 py-2">${product.unit}</td>
                    <td class="px-4 py-2">${product.short_description}</td>
                    <td class="px-4 py-2 product-price">${product.price}</td>
                    <td class="px-4 py-2 inventory_${product.id}">...</td>
                    <td class="px-4 py-2"><img src="${product.image}" alt="${product.name}" class="w-16 h-16 object-cover rounded-lg"></td>
                </tr>
            `;
        });
        $('#product-list').html(productRows);
        addCheckbox();
    }


        // Thêm sự kiện checkbox
        function addCheckbox() {
            $('.product-checkbox').on('change', function () {
                const productId = $(this).data('checkbox');
                if ($(this).is(':checked')) {
                    $selectedProduct.push(productId);
                } else {
                    $selectedProduct = $selectedProduct.filter(id => id !== productId);
                }

                localStorage.setItem('selectedProduct', JSON.stringify($selectedProduct));
            });
        }

        // Phân trang
        function updatePagination(data) {
            const pagination = $('#pagination');
            pagination.empty();

            const currentPage = data.current_page;
            const lastPage = data.last_page;
            if (currentPage > 1) {
                pagination.append(`
                <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" data-page="${currentPage - 1}">Previous</button>
                `);
            }
            if (currentPage > 3) {

                pagination.append(`
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" data-page="1">1</button>
                `);
            }

            if (currentPage > 4 ) {
                pagination.append(`
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" data-page="#">...</button>
                `);

            }


            for (let i = Math.max(1, currentPage - 2); i <= Math.min(lastPage, currentPage + 2); i++) {
                pagination.append(`
                    <button class="px-4 py-2 ${currentPage === i ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} rounded hover:bg-gray-300" data-page="${i}">${i}</button>
                `);
            }

            if (lastPage-4 > currentPage) {
                pagination.append(`
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" data-page="#">...</button>
                `);

            }
            if (currentPage < lastPage-2) {
                pagination.append(`
                        <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" data-page="${lastPage}">${lastPage}</button>
                `);
            }
            if (currentPage < lastPage) {
                pagination.append(`
                <button class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300" data-page="${currentPage + 1}">Next</button>
                `);
            }
        }

        // Phân trang sự kiện
        $('#pagination').on('click', 'button', function () {
            const page = $(this).data('page');
            loadProducts($('#search').val().trim(), page);
        });

        // Chọn tất cả checkbox
        $('#select-all').on('change', function () {
            const isChecked = $(this).is(':checked');
            $('.product-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Xoá tất cả các sản phẩm đã chọn
        $('#delete').on('click', function (e) {
        e.preventDefault();

        // Kiểm tra nếu có sản phẩm nào được chọn
        if ($selectedProduct.length > 0) {
            // Lặp qua danh sách các sản phẩm đã chọn
            $selectedProduct.forEach(id => {
                $.ajax({
                    type: "DELETE",
                    url: `/api/products/destroy/${id}`,
                    success: function (response) {
                        console.log(response.message);

                        // Xóa sản phẩm khỏi selectedProductsData và localStorage
                        selectedProductsData = selectedProductsData.filter(product => product.id !== id);
                        localStorage.setItem('selectedProduct', JSON.stringify(selectedProductsData));

                        // Cập nhật giao diện, loại bỏ các sản phẩm đã xóa
                        $(`#product-list .product-row[data-id="${id}"]`).remove();
                    },
                    error: function (error) {
                        console.error('Có lỗi xảy ra:', error);
                    }
                });
            });

            // Sau khi xoá xong, tải lại danh sách sản phẩm
            loadProducts();
        } else {
            alert('Vui lòng chọn ít nhất một sản phẩm để xoá.');
        }
    });


        loadProducts(); // Initial load
        window.onload = function() {
            localStorage.clear();
            selectedProductsData = []
        };

    });

        </script>

    </body>

    </html>
