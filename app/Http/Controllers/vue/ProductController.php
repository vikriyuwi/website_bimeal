<?php

namespace App\Http\Controllers\vue;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::get();
        // return Inertia::render('Home/Index',['products' => $products]);
    }
}
