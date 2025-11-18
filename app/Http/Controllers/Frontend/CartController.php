<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
   public function index()
    {
        $cart = session()->get('cart', []);
        $total = $this->calculateTotal($cart);

        return view('frontend.cart.index', compact('cart', 'total'));
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $product->stock,
            'notes' => 'nullable|string|max:255',
        ]);

        // Check stock
        if ($product->stock < $request->quantity) {
            return back()->with('error', 'Stok tidak mencukupi!');
        }

        $cart = session()->get('cart', []);

        $cartId = $product->id;

        if (isset($cart[$cartId])) {
            // Update quantity
            $newQuantity = $cart[$cartId]['quantity'] + $request->quantity;

            if ($newQuantity > $product->stock) {
                return back()->with('error', 'Stok tidak mencukupi!');
            }

            $cart[$cartId]['quantity'] = $newQuantity;
        } else {
            // Add new item
            $cart[$cartId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->final_price,
                'quantity' => $request->quantity,
                'notes' => $request->notes,
                'image' => $product->image,
            ];
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Produk ditambahkan ke keranjang!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            $product = Product::find($cart[$id]['product_id']);

            if ($request->quantity > $product->stock) {
                return back()->with('error', 'Stok tidak mencukupi!');
            }

            $cart[$id]['quantity'] = $request->quantity;
            session()->put('cart', $cart);

            return back()->with('success', 'Keranjang diperbarui!');
        }

        return back()->with('error', 'Item tidak ditemukan!');
    }

    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);

            return back()->with('success', 'Item dihapus dari keranjang!');
        }

        return back()->with('error', 'Item tidak ditemukan!');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Keranjang dikosongkan!');
    }

    private function calculateTotal($cart)
    {
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        $tax = $subtotal * 0.11;
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ];
    }
}
