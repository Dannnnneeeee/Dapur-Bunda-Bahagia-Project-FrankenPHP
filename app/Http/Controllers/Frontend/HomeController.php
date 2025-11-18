<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
   public function index()
    {
        $categories = Category::active()->ordered()->get();

        $featuredProducts = Product::with('category')
            ->available()
            ->featured()
            ->take(8)
            ->get();

        $newProducts = Product::with('category')
            ->available()
            ->latest()
            ->take(8)
            ->get();

        return view('frontend.home', compact('categories', 'featuredProducts', 'newProducts'));
    }
}
