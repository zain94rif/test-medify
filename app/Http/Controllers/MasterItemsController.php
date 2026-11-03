<?php

namespace App\Http\Controllers;

use App\Models\MasterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MasterItemsController extends Controller
{
    public function index()
    {
        return view('master_items.index.index');
    }

    public function search(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'kode' => ['nullable', 'string', 'max:255'],
            'nama' => ['nullable', 'string', 'max:255'],
            'hargamin' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'hargamax' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ])->validate();

        if (
            isset($validated['hargamin'], $validated['hargamax'])
            && (int) $validated['hargamin'] > (int) $validated['hargamax']
        ) {
            throw ValidationException::withMessages([
                'hargamax' => 'Harga maksimum harus sama dengan atau lebih besar dari harga minimum.',
            ]);
        }

        $data_search = MasterItem::query();

        if (isset($validated['kode']) && $validated['kode'] !== '') {
            $data_search->where('kode', $validated['kode']);
        }
        if (isset($validated['nama']) && $validated['nama'] !== '') {
            $data_search->where('nama', 'LIKE', '%' . $validated['nama'] . '%');
        }
        if (isset($validated['hargamin'])) {
            $data_search->where('harga_beli', '>=', $validated['hargamin']);
        }
        if (isset($validated['hargamax'])) {
            $data_search->where('harga_beli', '<=', $validated['hargamax']);
        }

        $data_search = $data_search
            ->select('kode', 'nama', 'jenis', 'harga_beli', 'laba', 'supplier', 'foto')
            ->orderBy('id')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = [];
        } elseif ($method == 'edit') {
            $item = MasterItem::findOrFail($id);
        } else {
            abort(404);
        }

        $data['item'] = $item;
        $data['method'] = $method;
        return view('master_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = MasterItem::where('kode', $kode)->firstOrFail();
        return view('master_items.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        if (!in_array($method, ['new', 'edit'], true)) {
            abort(404);
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'harga_beli' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'laba' => ['required', 'integer', 'min:0', 'max:100'],
            'supplier' => ['required', 'string', 'in:Tokopaedi,Bukulapuk,TokoBagas,E Commurz,Blublu'],
            'jenis' => ['required', 'string', 'in:Obat,Alkes,Matkes,Umum,ATK'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($method == 'new') {
            $data_item = new MasterItem();
            $kode = MasterItem::count('id');
            $kode = $kode + 1;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
            sleep(3);
        } else {
            $data_item = MasterItem::findOrFail($id);
            $kode = $data_item->kode;
        }

        $previousFoto = $data_item->foto;
        $newFoto = null;

        try {
            if ($request->hasFile('foto')) {
                $storedFoto = $request->file('foto')->store('master-items', 'public');
                if ($storedFoto === false) {
                    throw new RuntimeException('Foto Master Item gagal disimpan.');
                }
                $newFoto = $storedFoto;
            }

            $data_item->nama = $validated['nama'];
            $data_item->harga_beli = $validated['harga_beli'];
            $data_item->laba = $validated['laba'];
            $data_item->supplier = $validated['supplier'];
            $data_item->jenis = $validated['jenis'];
            if ($newFoto !== null) {
                $data_item->foto = $newFoto;
            }

            $data_item->kode = $kode;
            $data_item->save();
        } catch (Throwable $exception) {
            if ($newFoto !== null && !Storage::disk('public')->delete($newFoto)) {
                Log::warning('Gagal membersihkan foto Master Item setelah penyimpanan gagal.', [
                    'path' => $newFoto,
                ]);
            }

            throw $exception;
        }

        if ($newFoto !== null && $previousFoto !== null && !Storage::disk('public')->delete($previousFoto)) {
            Log::warning('Gagal menghapus foto Master Item lama setelah penggantian.', [
                'path' => $previousFoto,
                'master_item_id' => $data_item->id,
            ]);
        }

        return redirect('master-items');
    }

    public function delete($id)
    {
        MasterItem::findOrFail($id)->delete();
        return redirect('master-items');
    }

    public function updateRandomData()
    {
        $data = MasterItem::get();
        foreach($data as $item)
        {
            $kode = $item->id;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);

            $item->harga_beli = rand(100,1000000);
            $item->laba = rand(10,99);
            $item->kode = $kode;
            $item->supplier = $this->getRandomSupplier();
            $item->jenis = $this->getRandomJenis();
            $item->save();
        }
    }

    private function getRandomSupplier()
    {
        $array = ['Tokopaedi','Bukulapuk','TokoBagas','E Commurz','Blublu'];
        $random = rand(0,4);
        return $array[$random];
    }

    private function getRandomJenis()
    {
        $array = ['Obat','Alkes','Matkes','Umum','ATK'];
        $random = rand(0,4);
        return $array[$random];
    }
}
