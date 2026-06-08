<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ------------------------------------------------------------------
    // Index
    // ------------------------------------------------------------------

    public function index(Request $request)
    {
        $query = Product::with('category')
            ->when(
                $request->filled('q'),
                fn ($q) => $q->where(function ($qb) use ($request) {
                    $qb->where('name', 'like', "%{$request->q}%")
                       ->orWhere('sku', 'like', "%{$request->q}%");
                })
            )
            ->when(
                $request->filled('type'),
                fn ($q) => $q->whereHas('category', fn ($c) => $c->where('type', $request->type))
            )
            ->orderBy('name');

        $products   = $query->paginate(20)->withQueryString();
        $categories = ProductCategory::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    // ------------------------------------------------------------------
    // Create
    // ------------------------------------------------------------------

    public function create()
    {
        $this->middleware('role:gm,manager');

        $categories = ProductCategory::active()->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    // ------------------------------------------------------------------
    // Store
    // ------------------------------------------------------------------

    public function store(Request $request)
    {
        $this->middleware('role:gm,manager');

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => 'required|string|max:100|unique:products,sku',
            'product_category_id' => 'required|exists:product_categories,id',
            'base_price'          => 'required|numeric|min:0',
            'unit'                => 'required|in:pax,unit,trip',
            'min_pax'             => 'nullable|integer|min:1',
            'max_pax'             => 'nullable|integer|min:1',
            'duration_days'       => 'nullable|integer|min:1',
            'description'         => 'nullable|string',
            'is_active'           => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $product = Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    // ------------------------------------------------------------------
    // Show
    // ------------------------------------------------------------------

    public function show(Product $product)
    {
        $product->load('category');

        $activeOpportunities = $product->opportunities()
            ->with('client')
            ->whereNotIn('stage', ['won', 'lost'])
            ->latest()
            ->take(10)
            ->get();

        return view('products.show', compact('product', 'activeOpportunities'));
    }

    // ------------------------------------------------------------------
    // Edit (for completeness; view may reuse create.blade.php)
    // ------------------------------------------------------------------

    public function edit(Product $product)
    {
        $categories = ProductCategory::active()->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    // ------------------------------------------------------------------
    // Update
    // ------------------------------------------------------------------

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'sku'                 => "required|string|max:100|unique:products,sku,{$product->id}",
            'product_category_id' => 'required|exists:product_categories,id',
            'base_price'          => 'required|numeric|min:0',
            'unit'                => 'required|in:pax,unit,trip',
            'min_pax'             => 'nullable|integer|min:1',
            'max_pax'             => 'nullable|integer|min:1',
            'duration_days'       => 'nullable|integer|min:1',
            'description'         => 'nullable|string',
            'is_active'           => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $product->update($validated);

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    // ------------------------------------------------------------------
    // Destroy
    // ------------------------------------------------------------------

    public function destroy(Product $product)
    {
        $activeCount = $product->opportunities()
            ->whereNotIn('stage', ['won', 'lost'])
            ->count();

        if ($activeCount > 0) {
            return back()->withErrors([
                'delete' => "Produk tidak dapat dihapus karena masih digunakan oleh {$activeCount} opportunity aktif.",
            ]);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    // ------------------------------------------------------------------
    // API Search (for Alpine.js autocomplete)
    // GET /api/products/search?q=term
    // ------------------------------------------------------------------

    public function apiSearch(Request $request)
    {
        $term = $request->input('q', '');

        $products = Product::active()
            ->with('category')
            ->when(
                strlen($term) > 0,
                fn ($q) => $q->where(function ($qb) use ($term) {
                    $qb->where('name', 'like', "%{$term}%")
                       ->orWhere('sku', 'like', "%{$term}%");
                })
            )
            ->orderBy('name')
            ->take(15)
            ->get()
            ->map(fn ($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'base_price'     => (float) $p->base_price,
                'formatted_price'=> 'Rp ' . number_format((float) $p->base_price, 0, ',', '.'),
                'unit'           => $p->unit,
                'category'       => $p->category?->name,
            ]);

        return response()->json($products);
    }
}
