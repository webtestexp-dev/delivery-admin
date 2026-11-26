<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        return view('brands.index', ['brands' => Brand::withCount('products')->orderBy('name')->paginate(15)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);
        Brand::create($data);

        return back()->with('status', 'Brand created successfully.');
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brand)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $brand->update($data);

        return back()->with('status', 'Brand updated successfully.');
    }

    public function setStatus(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $brand->update($data);

        return back()->with('status', $brand->is_active ? 'Brand activated.' : 'Brand deactivated.');
    }
}
