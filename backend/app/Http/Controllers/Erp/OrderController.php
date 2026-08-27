<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Order::where('organization_id', $user->organization_id)
            ->with(['customer:id,first_name,last_name,email', 'items'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'type' => 'nullable|in:sale,purchase',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.name' => 'required_without:items.*.product_id|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        // Tenant check customer
        if (!empty($validated['customer_id'])) {
            $ok = \App\Models\Customer::where('id', $validated['customer_id'])
                ->where('organization_id', $user->organization_id)
                ->exists();
            if (!$ok) {
                return response()->json(['message' => 'مشتری متعلق به سازمان شما نیست'], 403);
            }
        }

        $subtotal = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $qty = $item['quantity'];
            $price = $item['unit_price'];
            $discount = $item['discount'] ?? 0;
            $lineTotal = ($qty * $price) - $discount;
            $subtotal += $lineTotal;

            $name = $item['name'] ?? null;
            $sku = null;

            if (!empty($item['product_id'])) {
                $product = Product::where('organization_id', $user->organization_id)
                    ->find($item['product_id']);
                if (!$product) {
                    return response()->json(['message' => 'محصول نامعتبر'], 422);
                }
                $name = $product->name;
                $sku = $product->sku;
            }

            $itemsData[] = [
                'product_id' => $item['product_id'] ?? null,
                'name' => $name,
                'sku' => $sku,
                'quantity' => $qty,
                'unit_price' => $price,
                'discount' => $discount,
                'total' => $lineTotal,
            ];
        }

        $order = Order::create([
            'organization_id' => $user->organization_id,
            'customer_id' => $validated['customer_id'] ?? null,
            'created_by' => $user->id,
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'status' => 'draft',
            'type' => $validated['type'] ?? 'sale',
            'subtotal' => $subtotal,
            'discount' => 0,
            'tax' => 0,
            'total' => $subtotal,
            'currency' => 'IRR',
            'notes' => $validated['notes'] ?? null,
            'ordered_at' => now(),
        ]);

        foreach ($itemsData as $itemData) {
            $order->items()->create($itemData);
        }

        return response()->json([
            'message' => 'سفارش ایجاد شد',
            'order' => $order->load(['customer', 'items']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::where('organization_id', $user->organization_id)
            ->with(['customer', 'items.product', 'creator:id,name'])
            ->findOrFail($id);

        return response()->json($order);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|in:draft,confirmed,processing,shipped,completed,cancelled',
            'notes' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $order->update($validated);

        return response()->json([
            'message' => 'سفارش به‌روزرسانی شد',
            'order' => $order->fresh()->load(['customer', 'items']),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $order = Order::where('organization_id', $user->organization_id)->findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'سفارش حذف شد']);
    }
}
