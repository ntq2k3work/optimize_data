<div class="p-4">
    <!-- Search Bar -->
    <input type="text" wire:model.live.debounce.300ms="search" class="form-control mb-3 p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Search by product name...">
    <button wire:click="deleteSelectedProducts" type="submit" class="bg-red-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
        Xoá
    </button>

    <!-- Table to display data -->
    <table class="table-auto w-full border-collapse border border-gray-300 shadow-md rounded-lg">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">
                    <input type="checkbox" />
                </th>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">ID</th>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">Name</th>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">Unit</th>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">Description</th>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">Price</th>
                <th class="px-4 py-2 cursor-pointer text-left text-sm text-gray-700 font-semibold">Quantity</th>
                <th class="px-4 py-2 text-left text-sm text-gray-700 font-semibold">Image</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2 text-sm text-gray-700"><input type="checkbox" wire:model="selectedRecords" value="{{ $product->id }}"></td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $product->id }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $product->name }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $product->unit }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $product->short_description }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($product->price, 2) }} VNĐ</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $product->quantity }}</td>
                    <td class="px-4 py-2 text-sm">
                        <img class="object-cover rounded-lg" loading="lazy" src="{{ $product->image }}" alt="Ảnh {{ $product->name }}">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Display Per Page -->
    <div class="mt-4 flex items-center justify-between">
        <div class="text-sm text-gray-700">Hiển thị:</div>
        <select wire:model="perPage" class="p-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="10">10</option>
            <option value="20">20</option>
            <option value="50">50</option>
        </select>
    </div>

    <!-- Pagination links -->
    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>

<script>
    var checkbox = document.querySelectorAll('.checkbox');
    console.log(checkbox);
</script>
