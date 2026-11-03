<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ItemCategoriesController extends Controller
{
    public function index()
    {
        return view('item_categories.index.index');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['nullable', 'string', 'max:255'],
            'nama' => ['nullable', 'string', 'max:255'],
        ]);

        $categories = ItemCategory::query()
            ->withCount('masterItems')
            ->when(
                isset($validated['kode']) && $validated['kode'] !== '',
                fn ($query) => $query->where('kode', $validated['kode'])
            )
            ->when(
                isset($validated['nama']) && $validated['nama'] !== '',
                fn ($query) => $query->where('nama', 'LIKE', '%' . $validated['nama'] . '%')
            )
            ->orderBy('id')
            ->get(['id', 'nama', 'kode']);

        return response()->json([
            'status' => 200,
            'data' => $categories,
        ]);
    }

    public function options(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);
        $term = trim($validated['q'] ?? '');

        if ($term === '') {
            return response()->json([
                'status' => 200,
                'data' => [],
            ]);
        }

        $categories = ItemCategory::query()
            ->where(function ($query) use ($term) {
                $query->where('nama', 'LIKE', '%' . $term . '%')
                    ->orWhere('kode', 'LIKE', '%' . $term . '%');
            })
            ->orderBy('nama')
            ->limit(20)
            ->get(['id', 'nama', 'kode']);

        return response()->json([
            'status' => 200,
            'data' => $categories,
        ]);
    }

    public function formView(string $method, ?int $id = null)
    {
        if ($method === 'new') {
            $category = new ItemCategory();
        } elseif ($method === 'edit') {
            $category = ItemCategory::findOrFail($id);
        } else {
            abort(404);
        }

        return view('item_categories.form.index', [
            'category' => $category,
            'method' => $method,
        ]);
    }

    public function singleView(int $id)
    {
        $category = ItemCategory::with(['masterItems' => function ($query) {
            $query->orderBy('nama');
        }])
            ->findOrFail($id);

        return view('item_categories.single.index', [
            'category' => $category,
        ]);
    }

    public function downloadPdf(int $id)
    {
        $category = ItemCategory::with(['masterItems' => function ($query) {
            $query->orderBy('nama');
        }])
            ->findOrFail($id);

        return Pdf::loadView('item_categories.single.pdf', [
            'category' => $category,
            'printedAt' => now()->format('d-m-Y H:i:s'),
        ])
            ->setPaper('a4', 'portrait')
            ->download('kategori-' . $category->kode . '.pdf');
    }

    public function formSubmit(Request $request, string $method, ?int $id = null)
    {
        if (!in_array($method, ['new', 'edit'], true)) {
            abort(404);
        }

        $category = $method === 'edit'
            ? ItemCategory::findOrFail($id)
            : new ItemCategory();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_categories', 'kode')->ignore($category->id),
            ],
        ]);

        $category->fill($validated)->save();

        return redirect('/item-categories');
    }

    public function delete(int $id)
    {
        ItemCategory::findOrFail($id)->delete();

        return redirect('/item-categories');
    }
}
