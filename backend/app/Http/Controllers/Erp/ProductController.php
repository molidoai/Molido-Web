<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Product::where('organization_id', $user->organization_id)
            ->with('inventory')
            ->latest();

        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'track_inventory' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'initial_quantity' => 'nullable|numeric|min:0',
        ]);

        $product = Product::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'barcode' => $validated['barcode'] ?? null,
            'description' => $validated['description'] ?? null,
            'unit' => $validated['unit'] ?? 'pcs',
            'price' => $validated['price'] ?? 0,
            'cost' => $validated['cost'] ?? 0,
            'currency' => $validated['currency'] ?? 'IRR',
            'track_inventory' => $validated['track_inventory'] ?? true,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Initial inventory
        if (($validated['track_inventory'] ?? true) && isset($validated['initial_quantity'])) {
            Inventory::create([
                'organization_id' => $user->organization_id,
                'product_id' => $product->id,
                'warehouse' => 'main',
                'quantity' => $validated['initial_quantity'],
            ]);
        }

        return response()->json([
            'message' => 'محصول ایجاد شد',
            'product' => $product->load('inventory'),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $product = Product::where('organization_id', $user->organization_id)
            ->with('inventory')
            ->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $product = Product::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string|max:100',
            'barcode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'track_inventory' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'محصول به‌روزرسانی شد',
            'product' => $product->fresh()->load('inventory'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $product = Product::where('organization_id', $user->organization_id)->findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'محصول حذف شد']);
    }
}
