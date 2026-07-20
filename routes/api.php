<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\InventorySyncController;
use App\Http\Controllers\Api\ErpUserApiController;
use App\Http\Controllers\Api\MasterDataAPIController;
use App\Http\Controllers\Api\InventoryAdjustmentController;

Route::get('purchase_request/detail/{id}', ['uses' =>'Logistic\PurchaseRequestController@detail','as' => 'api.purchase_request.detail']);
Route::get('inventory/detail/{id}', ['uses' =>'Logistic\InventoryController@detail','as' => 'api.inventory.detail']);

Route::middleware('auth:api')->get('/user', 'UserController@AuthRouteAPI');


// API For Stock Opname APP
Route::middleware('erp.key')->group(function () {
    Route::get('/location_company', [InventorySyncController::class, 'location_company']);
    Route::post('/sync/inventories', [InventorySyncController::class, 'pushByLocation']);
    Route::get('/inventories', [InventorySyncController::class, 'inventoriesByLocation']);
    Route::get('/users/lookup', [ErpUserApiController::class, 'lookup']);

    // Master Data
    Route::get('products', [MasterDataAPIController::class, 'products']);
    Route::get('companies', [MasterDataAPIController::class, 'companies']);
    Route::get('measures', [MasterDataAPIController::class, 'measures']);
    Route::get('locations', [MasterDataAPIController::class, 'locations']);
    Route::get('item-details', [MasterDataAPIController::class, 'getItemDetails']);
    Route::get('get-product-by-location', [MasterDataAPIController::class, 'getProductByLocation']);
    
    // Push SO Ke ERP
    Route::post('/inventory-adjustments', [InventoryAdjustmentController::class, 'store']);
    Route::post('/inventory-adjustments/bulk', [InventoryAdjustmentController::class, 'storeBulk']);
    Route::post('/inventory-adjustments/apply-soh', [InventoryAdjustmentController::class, 'applySoh']);
    
});
