<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/master-items', [App\Http\Controllers\MasterItemsController::class, 'index'])->name('master-items.index');
    Route::get('/master-items/search', [App\Http\Controllers\MasterItemsController::class, 'search']);
    Route::get('/master-items/export', [App\Http\Controllers\MasterItemsController::class, 'exportExcel'])->name('master-items.export');
    Route::get('/master-items/form/{method}/{id?}', [App\Http\Controllers\MasterItemsController::class, 'formView']);
    Route::post('/master-items/form/{method}/{id?}', [App\Http\Controllers\MasterItemsController::class, 'formSubmit']);

    Route::get('/master-items/view/{kode}', [App\Http\Controllers\MasterItemsController::class, 'singleView']);
    Route::get('/master-items/delete/{id}', [App\Http\Controllers\MasterItemsController::class, 'delete']);


    Route::get('/master-items/update-random-data', [App\Http\Controllers\MasterItemsController::class, 'updateRandomData']);

    Route::get('/item-categories', [App\Http\Controllers\ItemCategoriesController::class, 'index'])->name('item-categories.index');
    Route::get('/item-categories/search', [App\Http\Controllers\ItemCategoriesController::class, 'search'])->name('item-categories.search');
    Route::get('/item-categories/options', [App\Http\Controllers\ItemCategoriesController::class, 'options'])->name('item-categories.options');
    Route::get('/item-categories/form/{method}/{id?}', [App\Http\Controllers\ItemCategoriesController::class, 'formView'])->name('item-categories.form');
    Route::post('/item-categories/form/{method}/{id?}', [App\Http\Controllers\ItemCategoriesController::class, 'formSubmit'])->name('item-categories.submit');
    Route::get('/item-categories/{id}/pdf', [App\Http\Controllers\ItemCategoriesController::class, 'downloadPdf'])->name('item-categories.pdf');
    Route::get('/item-categories/view/{id}', [App\Http\Controllers\ItemCategoriesController::class, 'singleView'])->name('item-categories.view');
    Route::delete('/item-categories/{id}', [App\Http\Controllers\ItemCategoriesController::class, 'delete'])->name('item-categories.delete');
});
