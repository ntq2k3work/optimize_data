<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Component;
use Livewire\WithPagination;
use Intervention\Image\Facades\Image;

class Datatable extends Component
{
    use WithPagination;
    public $sortBy = 'sanpham.id';
    public $sortDirection = 'asc';
    public $perPage = '20';
    public $search = '';
    public $status = false;
    public $records = [];
    public $selectedRecords = [];
    protected $listeners = ['refreshData' => '$refresh'];


    public function deleteSelected()
    {
        Product::whereIn('id', $this->selectedRecords)->delete();
        $this->emit('refreshData'); // Phát sự kiện để cập nhật lại dữ liệu trên các trang khác
    }


    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }


    public function updateStatusProducts()
    {

    }

    // public function editSize($image,$width,$height)
    // {
    //         $manager = new ImageManager(new Driver());
    //         $image = $manager->read('uploads/pexels-zhangkaiyv-1138369.jpg');
    //         $image->scale(width: 300,height:200);
    //         $name = rand();
    //         $image->toWebp();
    // }

    function modifyImageUrl($originalUrl, $width, $height, $mode = 'fill') {
        // Tách URL thành các phần
        $urlParts = explode('/image/upload/', $originalUrl); // Chia URL thành mảng
        $cloudinaryBase = $urlParts[0]; // Lấy phần cơ bản
        $publicId = $urlParts[1]; // Lấy public_id

        // Tạo URL mới với các tham số biến đổi
        return $cloudinaryBase . "/image/upload/f_auto/q_auto/w_{$width},h_{$height},c_{$mode}/" . $publicId;
    }

    public function render()
    {
        $products = DB::table('sanpham')
        ->join('tonkho', 'sanpham.id', '=', 'tonkho.id_product')
        ->where('sanpham.name', 'like', '%' . $this->search . '%') // Tìm kiếm theo tên sản phẩm
        ->orderBy($this->sortBy, $this->sortDirection)
        ->paginate($this->perPage, [
            'sanpham.id',
            'name',
            'unit',
            'short_description',
            'price',
            'quantity',
            'image'
        ]);

        foreach ($products as $product) {
            $product->image = $this->modifyImageUrl($product->image,100,100);
        }

        return view('livewire.datatable', compact('products'));
    }
}
