<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public $table = 'sanpham';
    public $timestamps = false;
    protected $fillable = ['id','name', 'price', 'quantity','unit','short_description','image','total'];


}
