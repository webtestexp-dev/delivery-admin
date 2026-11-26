<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('brand')
            ->when($request->filled('q'), fn ($query) => $query->where('name', 'like', '%'.$request->string('q')->trim().'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        return view('products.form', ['product' => new Product, 'brands' => Brand::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Product::create($this->validated($request));

        return redirect()->route('products.index')->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', ['product' => $product, 'brands' => Brand::where('is_active', true)->orderBy('name')->get()]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.index')->with('status', 'Product and current price updated. Past delivery prices remain unchanged.');
    }

    public function setStatus(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $product->update($data);

        return back()->with('status', $product->is_active ? 'Product activated.' : 'Product deactivated.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'brand_id' => ['nullable', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:40'],
            'packet_size' => ['nullable', 'numeric', 'gt:0', 'max:9999999.999'],
            'packet_size_unit' => ['nullable', 'string', 'max:20'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
