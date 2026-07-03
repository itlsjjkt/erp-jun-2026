<?php

Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');

/*
|------------------------------------------------------------------------------------
| Master
|------------------------------------------------------------------------------------
*/

Route::group(['prefix' => 'master', 'as' => 'master.', 'middleware'=>['auth']], function () {

    Route::get('generate/{param}', ['uses' => 'Master\MasterController@generate', 'as' => 'generate'] );

    Route::get('setting', ['uses' => 'Master\SettingController@index', 'as' => 'setting.index'] );
    Route::post('setting_spb_store', ['uses' => 'Master\SettingController@spbStore', 'as' => 'setting.spb.store'] );

    Route::resource('items', 'Master\ItemController')->except(['show']);
    Route::post('items_delete', ['uses' => 'Master\ItemController@delete', 'as' => 'items.delete']);
    Route::get('items_datatables', ['uses' => 'Master\ItemController@datatables', 'as' => 'items.datatables'] );
    Route::get('item_export', ['uses' => 'Master\ItemController@export', 'as' => 'items.export'] );

    Route::resource('item_products', 'Master\ItemProductController');
    Route::post('item_products_delete', ['uses' => 'Master\ItemProductController@delete', 'as' => 'item_products.delete']);
    Route::get('item_products_databales', ['uses' => 'Master\ItemProductController@datatables', 'as' => 'item_products.datatables'] );
    Route::get('item_products_search', ['uses' => 'Master\ItemProductController@search', 'as' => 'item_products.search'] );
    Route::get('item_products_upload', ['uses' => 'Master\ItemProductController@upload', 'as' => 'item_products.upload']);
    Route::post('item_products_import', ['uses' => 'Master\ItemProductController@import', 'as' => 'item_products.import']);
    Route::get('item_products_export', ['uses' => 'Master\ItemProductController@export', 'as' => 'item_products.export'] );
    // NEW PRODUCK MULTIPLE
    Route::get('item_products_create_multiple', ['uses' => 'Master\ItemProductController@create_multiple', 'as' => 'item_products.create_multiple']);
    Route::post('item_products_store_multiple', ['uses' => 'Master\ItemProductController@store_multiple', 'as' => 'item_products.store_multiple']);

    Route::resource('measures', 'Master\MeasureController')->except(['show']);
    Route::post('measures_delete', ['uses' => 'Master\MeasureController@delete', 'as' => 'measures.delete']);
    Route::get('measures_datatables', ['uses' => 'Master\MeasureController@datatables', 'as' => 'measures.datatables'] );
    Route::get('measures_export', ['uses' => 'Master\MeasureController@export', 'as' => 'measures.export'] );
    Route::get('measures_upload', ['uses' => 'Master\MeasureController@upload', 'as' => 'measures.upload']);
    Route::post('measures_import', ['uses' => 'Master\MeasureController@import', 'as' => 'measures.import']);

    Route::resource('brands', 'Master\BrandController')->except(['show']);
    Route::post('brands_delete', ['uses' => 'Master\BrandController@delete', 'as' => 'brands.delete']);
    Route::get('brands_datatables', ['uses' => 'Master\BrandController@datatables', 'as' => 'brands.datatables'] );
    Route::get('brands_export', ['uses' => 'Master\BrandController@export', 'as' => 'brands.export'] );
    Route::get('brands_upload', ['uses' => 'Master\BrandController@upload', 'as' => 'brands.upload']);
    Route::post('brands_import', ['uses' => 'Master\BrandController@import', 'as' => 'brands.import']);

    Route::resource('project', 'Master\ProjectController')->except(['show']);
    Route::get('project_datatables', ['uses' => 'Master\ProjectController@datatables', 'as' => 'project.datatables'] );
    Route::get('project_export', ['uses' => 'Master\ProjectController@export', 'as' => 'project.export'] );

    Route::resource('expeditions', 'Master\ExpeditionController')->except(['show']);
    Route::post('expeditions_delete', ['uses' => 'Master\ExpeditionController@delete', 'as' => 'expeditions.delete']);
    Route::get('expeditions_datatables', ['uses' => 'Master\ExpeditionController@datatables', 'as' => 'expeditions.datatables'] );

    Route::get('get_item', ['uses' => 'Master\ItemController@getItem'] );
    Route::get('get_product/{pid?}', ['uses' => 'Master\ItemProductController@loadData','as' => 'get_product' ] );
    Route::get('get_products/{pid?}', ['uses' => 'Master\ItemProductController@getData','as' => 'get_products' ] );
    Route::get('get_product_name/', ['uses' => 'Master\ItemProductController@getProductByName'] );
    Route::get('get_type/{id}', ['uses' => 'Master\ItemProductController@getType'] );
    Route::get('get_item_detail/{id?}', ['uses' => 'Master\ItemController@getItemDetail', 'as' => 'get_item_detail'] );
    Route::get('get_measure', ['uses' => 'Master\MasterController@getMeasure'] );
    Route::get('get_product_part_number', ['uses' => 'Master\ItemProductController@getProductByPartNumber'] ); // Suggestion Product by Part Number

    Route::get('get_city', ['uses' => 'Master\MasterController@getCity'] );
    Route::get('get_province', ['uses' => 'Master\MasterController@getProvince'] );
    Route::get('get_regency/{pid}', ['uses' => 'Master\MasterController@getRegency'] );
    Route::get('get_district/{pid}', ['uses' => 'Master\MasterController@getDistrict'] );
    Route::get('get_districts/{pid}', ['uses' => 'Master\MasterController@getDistricts'] );
    Route::get('get_village/{pid}', ['uses' => 'Master\MasterController@getVillage'] );
    Route::get('get_villages/{pid}', ['uses' => 'Master\MasterController@getVillages'] );
    Route::get('get_employee/{id?}/', ['uses' => 'Hr\EmployeeController@loadData','as' => 'get_employee'] );
    Route::get('get_user/{id?}', ['uses'  => 'Master\MasterController@loadDataUsers','as' => 'get_user'] );
    Route::get('get_operator/{id?}', ['uses'  => 'Master\MasterController@loadDataOperator','as' => 'get_operator'] );
    Route::get('get_expedition/{id?}', ['uses'  => 'Master\MasterController@loadDataExpedition','as' => 'get_expedition'] );
    Route::get('get_handcarry/{id?}', ['uses'  => 'Master\MasterController@loadDataHandCarry','as' => 'get_handcarry'] );
    Route::get('get_user_purchasing', ['uses'  => 'Master\MasterController@loadUsersPurchasing','as' => 'get_user_purchasing'] );
    Route::get('get_checker_pc', ['uses'  => 'Master\MasterController@loadCheckerPc','as' => 'get_checker_pc'] );
    Route::get('search_user/{id}', ['uses'  => 'Master\MasterController@searchUsers'] );
    Route::get('search_department/{id}', ['uses'  => 'Master\MasterController@searchDepartments'] );

    //Get Organisasi
    Route::get('get_department/{id?}', ['uses' => 'Master\DepartmentController@getItem','as' => 'get_department'] );
    Route::get('get_department_coa_id/{id}', ['uses' => 'Master\CostCentreController@getByIdDepartment'] );
    Route::get('get_location/{id?}', ['uses' => 'Master\WorkareaController@getItem','as' => 'get_location'] );

    Route::resource('request_item_products', 'Master\RequestItemProductController');
    Route::post('request_item_products_delete', ['uses' => 'Master\RequestItemProductController@delete', 'as' => 'request_item_products.delete']);
    Route::get('request_item_products_databales', ['uses' => 'Master\RequestItemProductController@datatables', 'as' => 'request_item_products.datatables'] );
    Route::post('request_item_products_mass_destroy', ['uses' => 'Master\RequestItemProductController@massDestroy', 'as' => 'request_item_products.mass_destroy']);
    Route::post('request_item_products_approve', ['uses' => 'Master\RequestItemProductController@approve', 'as' => 'request_item_products.approve']);

});

/*
|------------------------------------------------------------------------------------
| Organisasi
|------------------------------------------------------------------------------------
*/

Route::group(['prefix' => ADMIN, 'as' => ADMIN . '.', 'middleware'=>['auth']], function () {
    Route::get('dashboard', ['uses' => 'DashboardController@index', 'as' => 'dashboard'] );
    Route::get('datatables_dpm', ['uses' => 'DashboardController@datatables_dpm', 'as' => 'datatables_dpm'] );
    Route::resource('permissions', 'Admin\PermissionsController');
    Route::get('permission_datatables', ['uses' => 'Admin\PermissionsController@datatables', 'as' => 'permissions.datatables'] );
    Route::post('permission_delete', ['uses' => 'Admin\PermissionsController@delete', 'as' => 'permissions.delete']);

    Route::resource('roles', 'Admin\RolesController');
    Route::post('roles_mass_destroy', ['uses' => 'Admin\RolesController@massDestroy', 'as' => 'roles.mass_destroy']);
    Route::resource('users', 'Admin\UsersController');
    Route::post('users_delete', ['uses' => 'Admin\UsersController@delete', 'as' => 'users.delete']);
    Route::get('users_datatables', ['uses' => 'Admin\UsersController@datatables', 'as' => 'users.datatables'] );
    Route::get('users_password/{id}', ['uses' => 'Admin\UsersController@password', 'as' => 'users.password'] );
    Route::post('users_password_store/{id}', ['uses' => 'Admin\UsersController@update_password', 'as' => 'users.password.store'] );

});

Route::group(['middleware' => ['auth']],  function () {

    Route::get('get_dpm_js/{id?}', ['uses' => 'Logistic\PurchaseRequestController@getJs', 'as' => 'dpm.js'] );
    Route::get('get_spb_js/{id?}/{val?}/{i?}', ['uses' => 'Logistic\SpbController@getJs', 'as' => 'spb.js'] );
    Route::get('get_ttb_js/{id?}', ['uses' => 'Logistic\TtbController@getJs', 'as' => 'ttb.js'] );

    //Profile
    Route::get('profile', ['uses'  => 'Admin\ProfileController@index', 'as' => 'profile'] );
    Route::match(['get', 'post'], 'profile/change_profile', ['uses' => 'Admin\ProfileController@changeProfile', 'as' => 'profile.change_profile']);
    Route::match(['get', 'post'], 'profile/change_password', ['uses' => 'Admin\ProfileController@changePassword', 'as' => 'profile.change_password']);

    //Logistic
    Route::resource('purchase_request', 'Logistic\PurchaseRequestController');
    Route::get('purchase_item', ['uses' => 'Logistic\PurchaseRequestController@create_item', 'as' => 'purchase_request.item'] );
    Route::get('purchase_request_datatables', ['uses' => 'Logistic\PurchaseRequestController@datatables', 'as' => 'purchase_request.datatables'] );
    Route::post('purchase_request_delete', ['uses' => 'Logistic\PurchaseRequestController@delete', 'as' => 'purchase_request.delete']);
    Route::get('purchase_request_search', ['uses' => 'Logistic\PurchaseRequestController@search', 'as' => 'purchase_request.search'] );
    Route::get('purchase_print/{id}/{type}', ['uses' => 'Logistic\PurchaseRequestController@print', 'as' => 'purchase_request.print'] );
    Route::get('purchase_notes/{id}', ['uses' => 'Logistic\PurchaseRequestController@getPrItemNotes', 'as' => 'purchase_request_item.notes'] );
    Route::get('purchase_export/', ['uses' => 'Logistic\PurchaseRequestController@export', 'as' => 'purchase_request.export'] );
    Route::get('purchase_export_new/', ['uses' => 'Logistic\PurchaseRequestController@export_new', 'as' => 'purchase_request.export_new'] );
    Route::get('purchase_export_sla/', ['uses' => 'Logistic\PurchaseRequestController@export_sla', 'as' => 'purchase_request.export_sla'] );
    Route::get('purchase_export_historical/', ['uses' => 'Logistic\PurchaseRequestController@export_historical', 'as' => 'purchase_request.export_historical'] );
    Route::post('purchase_publish/{id}', ['uses' => 'Logistic\PurchaseRequestController@publish', 'as' => 'purchase_request.publish'] );
    Route::get('purchase_remove_item', ['uses' => 'Logistic\PurchaseRequestController@remove_item', 'as' => 'purchase_request.remove_item'] );
    Route::put('purchase_updatenewdraft/{id}', ['uses' => 'Logistic\PurchaseRequestController@updatenewdraft', 'as' => 'purchase_request.updatenewdraft'] );
    Route::post('purchase_request_publish_approval/{id}', ['uses' => 'Logistic\PurchaseRequestController@publish_approval', 'as' => 'purchase_request.publish_approval']);

    Route::post('purchase_request_reject', ['uses' => 'Logistic\PurchaseRequestController@reject', 'as' => 'purchase_request.reject']);
    Route::get('getDpmRejectById/{id}', ['uses' => 'Logistic\PurchaseRequestController@getDpmRejectById', 'as' => 'purchase_request.getDpmRejectById'] );

    Route::get('purchase_request_list_wti', ['uses' => 'Logistic\PurchaseRequestController@list_wti', 'as' => 'purchase_request.list_wti'] );
    Route::post('purchase_request_create_by_wti', ['uses' => 'Logistic\PurchaseRequestController@create_from_wti', 'as' => 'purchase_request.purchase_request_create_by_wti'] );
    Route::post('purchase_request_store_by_wti', ['uses' => 'Logistic\PurchaseRequestController@store_from_wti', 'as' => 'purchase_request.purchase_request_store_by_wti'] );

    Route::get('purchase_order/{id}', ['uses' => 'Logistic\PurchaseOrderController@show', 'as' => 'purchase_order.show'] );

    Route::get('purchase_revision', ['uses' => 'Logistic\PurchaseRequestRevisionController@index', 'as' => 'purchase_revision.index'] );
    Route::get('purchase_revision_datatables', ['uses' => 'Logistic\PurchaseRequestRevisionController@datatables', 'as' => 'purchase_revision.datatables'] );
    Route::get('purchase_revision_edit/{id}', ['uses' => 'Logistic\PurchaseRequestRevisionController@edit', 'as' => 'purchase_revision.edit'] );
    Route::post('purchase_revision_publish', ['uses' => 'Logistic\PurchaseRequestRevisionController@publish', 'as' => 'purchase_revision.publish'] );

    Route::get('logistic/ctp', ['uses' => 'Logistic\ChangeTypePoController@index', 'as' => 'logistic.ctp.index']);
    Route::get('logistic/ctp_datatables', ['uses' => 'Logistic\ChangeTypePoController@datatables', 'as' => 'logistic.ctp.datatables'] );
    Route::post('logistic/ctp/change-type', ['uses' => 'Logistic\ChangeTypePoController@changeType', 'as' => 'logistic.ctp.changeType'] );
    Route::post('logistic/ctp/change-type-multiple', ['uses' => 'Logistic\ChangeTypePoController@changeTypeMultiple', 'as' => 'logistic.ctp.changeTypeMultiple'] );

    //Master Company
    Route::resource('company', 'Master\CompanyController');
    Route::resource('workarea', 'Master\WorkareaController', ['parameters' => [
        'index' => 'id',
        'create' => 'id',
        'edit' => 'id',
        'destroy' => 'id'
    ]]);
    Route::get('workarea/datatables/{company_id}', ['uses' => 'Master\WorkareaController@datatables', 'as' => 'workarea.datatables'] );
    Route::get('workarea_approval/{id}', ['uses' => 'Master\WorkareaController@approval', 'as' => 'workarea.approval']);
    Route::post('workarea_approval_store', ['uses' => 'Master\WorkareaController@approval_store', 'as' => 'workarea.approval.store']);

    Route::resource('department', 'Master\DepartmentController', ['parameters' => [
        'index' => 'id',
        'create' => 'id',
        'edit' => 'id',
        'destroy' => 'id'
    ]]);
    Route::get('department/datatables/{company_id}', ['uses' => 'Master\DepartmentController@datatables', 'as' => 'department.datatables'] );
    Route::match(['get', 'post'], 'department_import', ['uses' => 'Master\DepartmentController@import', 'as' => 'department.import']);
    Route::get('department_export', ['uses' => 'Master\DepartmentController@export', 'as' => 'department.export'] );

    Route::resource('cost_centre', 'Master\CostCentreController', ['parameters' => [
        'index' => 'id',
        'create' => 'id',
        'edit' => 'id',
        'destroy' => 'id'
    ]]);
    Route::get('cost_centre/datatables/{company_id}', ['uses' => 'Master\CostCentreController@datatables', 'as' => 'cost_centre.datatables'] );
    Route::get('cost_centre_export', ['uses' => 'Master\CostCentreController@export', 'as' => 'cost_centre.export'] );
    Route::get('cost_centre_upload', ['uses' => 'Master\CostCentreController@upload', 'as' => 'cost_centre.upload']);
    Route::post('cost_centre_import', ['uses' => 'Master\CostCentreController@import', 'as' => 'cost_centre.import']);

    Route::get('announcement_read', ['uses' => 'Hr\AnnouncementController@read', 'as' => 'announcement.read'] );
    Route::post('announcement_read', ['uses' => 'Hr\AnnouncementController@search', 'as' => 'announcement.search'] );

    Route::get('notifications', ['uses' => 'Admin\NotificationController@index', 'as' => 'notifications'] );
    Route::get('notifications/{id}', ['uses' => 'Admin\NotificationController@show', 'as' => 'notifications.show'] );
    Route::get('notifications_clear', ['uses' => 'Admin\NotificationController@clear', 'as' => 'notifications.clear'] );

});


/*
|------------------------------------------------------------------------------------
| Purchasing
|------------------------------------------------------------------------------------
*/

Route::group(['prefix' => 'purchasing', 'as' => 'purchasing.', 'middleware'=>['auth']], function () {

    Route::get('receipt-po',            ['uses' => 'Purchasing\ReceiptPoController@index',      'as' => 'receipt-po.index']);
    Route::get('receipt-po/datatables', ['uses' => 'Purchasing\ReceiptPoController@datatables', 'as' => 'receipt-po.datatables']);
    Route::get('receipt-po/{id}',       ['uses' => 'Purchasing\ReceiptPoController@show',       'as' => 'receipt-po.show']);

    //DPH
    Route::resource('dph', 'Purchasing\DphController');
    Route::get('dph_datatables', ['uses' => 'Purchasing\DphController@datatables', 'as' => 'dph.datatables'] );
    Route::post('dph_store', ['uses' => 'Purchasing\DphController@store', 'as' => 'dph.store'] );
    Route::get('dph_create', ['uses' => 'Purchasing\DphController@create', 'as' => 'dph.create'] );
    Route::get('dph_print', ['uses' => 'Purchasing\DphController@print', 'as' => 'dph.print'] );
    Route::get('dph_create_list_item', ['uses' => 'Purchasing\DphController@create_list_item', 'as' => 'dph.create_list_item'] );
    Route::get('dph_show/{id}', ['uses' => 'Purchasing\DphController@show', 'as' => 'dph.show'] );
    Route::get('dph_show_edit/{id}', ['uses' => 'Purchasing\DphController@show_edit', 'as' => 'dph.show_edit'] );
    Route::post('dph_toApproval/{id}', ['uses' => 'Purchasing\DphController@toApproval', 'as' => 'dph.toApproval'] );
    Route::post('dph_updateNotes/{id}', ['uses' => 'Purchasing\DphController@updateNotes', 'as' => 'dph.updateNotes'] );
    Route::post('dph_cancel/{id}', ['uses' => 'Purchasing\DphController@cancel', 'as' => 'dph.cancel'] );

    Route::post('dph_add_store', ['uses' => 'Purchasing\DphController@add_store', 'as' => 'dph.add_store'] );
    Route::get('dph_add_supplier', ['uses' => 'Purchasing\DphController@add_supplier', 'as' => 'dph.add_supplier'] );
    Route::post('delete_supplier', ['uses' => 'Purchasing\DphController@delete_supplier', 'as' => 'dph.delete_supplier'] );

    Route::resource('po', 'Purchasing\PoController');
    Route::post('po_delete', ['uses' => 'Purchasing\PoController@delete', 'as' => 'po.delete']);
    Route::post('po_email', ['uses' => 'Purchasing\PoController@email', 'as' => 'po.email']);
    Route::get('po_datatables', ['uses' => 'Purchasing\PoController@datatables', 'as' => 'po.datatables'] );
    Route::get('po_search', ['uses' => 'Purchasing\PoController@search', 'as' => 'po.search'] );
    Route::get('po_print/{id}/{type}', ['uses' => 'Purchasing\PoController@print', 'as' => 'po.print'] );
    Route::post('po_print_merge', ['uses' => 'Purchasing\PoController@print_merge', 'as' => 'po.print.merge'] );
    Route::get('po_export', ['uses' => 'Purchasing\PoController@export', 'as' => 'po.export'] );
    Route::post('po_publish/{id}', ['uses' => 'Purchasing\PoController@publish', 'as' => 'po.publish'] );
    Route::post('po_revision', ['uses' => 'Purchasing\PoController@revision', 'as' => 'po.revision'] );
    Route::post('po_cancel', ['uses' => 'Purchasing\PoController@cancel', 'as' => 'po.cancel'] );
    Route::get('po_remove_item', ['uses' => 'Purchasing\PoController@remove', 'as' => 'po.remove_item'] );
    Route::get('get_po', ['uses' => 'Purchasing\PoController@loadData'] );
    Route::get('get_po_lpb/', ['uses' => 'Purchasing\PoController@getPOlpb'] );
    Route::get('getUpdateDate/{id}', ['uses' => 'Purchasing\PoController@getUpdateDate', 'as' => 'po.getUpdateDate'] );
    Route::put('po.update_date/{id}', ['uses' => 'Purchasing\PoController@storeUpdateDate', 'as' => 'po.update_date'] );
    Route::get('po_export_2_admin', ['uses' => 'Purchasing\PoController@export_2_admin', 'as' => 'po.export_2_admin'] );


    // EMAIL TAMBAHAN
    Route::get('get_info_po/{id}', ['uses' => 'Purchasing\PoController@getInfoPo', 'as' => 'po.get_info_po'] );
    Route::get('Attachment_po/{id}', ['uses' => 'Purchasing\PoController@checkAttachment', 'as' => 'po.Attachment_po'] );

    Route::get('pr', ['uses' => 'Purchasing\PrController@index', 'as' => 'pr'] );
    Route::get('pr_show/{id}', ['uses' => 'Purchasing\PrController@show', 'as' => 'pr.show'] );
    Route::get('pr_cancel/{id}', ['uses' => 'Purchasing\PrController@cancel', 'as' => 'pr.cancel'] );
    Route::post('pr_close/{id}', ['uses' => 'Purchasing\PrController@closePR', 'as' => 'pr.closepr'] );
    Route::get('pr_detail/{id}', ['uses' => 'Purchasing\PrController@detail', 'as' => 'pr.detail'] );
    Route::get('pr_history/{id}', ['uses' => 'Purchasing\PrController@history', 'as' => 'pr.history'] );
    Route::get('pr_datatables', ['uses' => 'Purchasing\PrController@datatables', 'as' => 'pr.datatables'] );
    Route::get('pr_category/{id}', ['uses' => 'Purchasing\PrController@category', 'as' => 'pr.category'] );
    Route::get('pr_datatables_category/{id}', ['uses' => 'Purchasing\PrController@datatablesCategory', 'as' => 'pr.datatables.category'] );
    Route::get('pr_search', ['uses' => 'Purchasing\PrController@search', 'as' => 'pr.search'] );
    Route::get('pr_print/{id}/{type}', ['uses' => 'Purchasing\PrController@print', 'as' => 'pr.print'] );
    Route::post('pr_print_merge', ['uses' => 'Purchasing\PrController@print_merge', 'as' => 'pr.print.merge'] );
    Route::get('pr_export', ['uses' => 'Purchasing\PrController@export', 'as' => 'pr.export'] );
    Route::post('pr_revision', ['uses' => 'Purchasing\PrController@revision', 'as' => 'pr.revision'] );
    Route::post('pr_close', ['uses' => 'Purchasing\PrController@close', 'as' => 'pr.close'] );
    Route::match(['get', 'post'],'pr_assign', ['uses' => 'Purchasing\PrController@assign', 'as' => 'pr.assign'] );
    Route::match(['get', 'post'],'pr_reassign', ['uses' => 'Purchasing\PrController@reassign', 'as' => 'pr.reassign'] );
    Route::post('pr_done', ['uses' => 'Purchasing\PrController@done', 'as' => 'pr.done'] );

    // CLOSE DOKUMEN PR
    Route::post('pr_close_document', ['uses' => 'Purchasing\PrController@close_document', 'as' => 'pr.close_document'] );

    Route::resource('suppliers', 'Purchasing\SupplierController');
    Route::post('suppliers_delete', ['uses' => 'Purchasing\SupplierController@delete', 'as' => 'suppliers.delete']);
    Route::post('suppliers_blacklist', ['uses' => 'Purchasing\SupplierController@blacklist', 'as' => 'suppliers.blacklist']);
    Route::get('suppliers_datatables', ['uses' => 'Purchasing\SupplierController@datatables', 'as' => 'suppliers.datatables'] );
    Route::get('suppliers_remove_pic', ['uses' => 'Purchasing\SupplierController@remove_pic', 'as' => 'suppliers.remove_pic'] );
    Route::get('suppliers_pic/{id}', ['uses' => 'Purchasing\SupplierController@pic', 'as' => 'suppliers.pic']);
    Route::get('suppliers_export', ['uses' => 'Purchasing\SupplierController@export', 'as' => 'suppliers.export']);
    Route::post('suppliers_pic', ['uses' => 'Purchasing\SupplierController@picStore', 'as' => 'suppliers.picStore']);
    Route::match(['get', 'post'], 'suppliers_import', ['uses' => 'Purchasing\SupplierController@import', 'as' => 'suppliers.import']);

    Route::put('suppliers_cancel/{id}', ['uses' => 'Purchasing\SupplierController@cancel', 'as' => 'suppliers.cancel']);

    Route::resource('payment_terms', 'Purchasing\PaymentTermController')->except(['show']);
    Route::post('payment_terms_delete', ['uses' => 'Purchasing\PaymentTermController@delete', 'as' => 'payment_terms.delete']);
    Route::get('payment_terms_datatables', ['uses' => 'Purchasing\PaymentTermController@datatables', 'as' => 'payment_terms.datatables'] );

    Route::get('approval', ['uses' => 'Purchasing\ApprovalController@index', 'as' => 'approval']);
    Route::get('approval/config/{id}', ['uses' => 'Purchasing\ApprovalController@config', 'as' => 'approval.config']);
    Route::get('approval_datatables', ['uses' => 'Purchasing\ApprovalController@datatables', 'as' => 'approval.datatables'] );
    Route::post('approval_store', ['uses' => 'Purchasing\\ApprovalController@store', 'as' => 'approval.store']);
    //APPROVAL DPH
    Route::get('approval/config_dph/{id}', ['uses' => 'Purchasing\ApprovalController@config_dph', 'as' => 'approval.config_dph']);
    Route::post('approval_store_dph', ['uses' => 'Purchasing\\ApprovalController@store_dph', 'as' => 'approval.store_dph']);
    //CHECKER PC
    Route::get('approval/config_pc/{id}', ['uses' => 'Purchasing\ApprovalController@config_pc', 'as' => 'approval.config_pc']);
    Route::post('approval_store_pc', ['uses' => 'Purchasing\\ApprovalController@store_pc', 'as' => 'approval.store_pc']);

    Route::get('approval/config_supplier',  ['uses' => 'Purchasing\ApprovalSupplierSettingController@index', 'as' => 'approval.supplier.config']);
    Route::post('approval_store_supplier',  ['uses' => 'Purchasing\ApprovalSupplierSettingController@store',  'as' => 'approval.supplier.store']);

    Route::resource('po_terms', 'Purchasing\PoTermController')->except(['show']);
    Route::post('po_terms_delete', ['uses' => 'Purchasing\PoTermController@delete', 'as' => 'po_terms.delete']);
    Route::get('po_terms_datatables', ['uses' => 'Purchasing\PoTermController@datatables', 'as' => 'po_terms.datatables'] );

    Route::resource('currency', 'Purchasing\CurrencyController')->except(['show']);
    Route::get('currency_datatables', ['uses' => 'Purchasing\CurrencyController@datatables', 'as' => 'currency.datatables'] );

    Route::resource('price_terms', 'Purchasing\PriceTermController')->except(['show']);
    Route::post('price_terms_delete', ['uses' => 'Purchasing\PriceTermController@delete', 'as' => 'price_terms.delete']);
    Route::get('price_terms_datatables', ['uses' => 'Purchasing\PriceTermController@datatables', 'as' => 'price_terms.datatables'] );

    Route::resource('po_notes', 'Purchasing\PoNotesController')->except(['show']);
    Route::post('po_notes_delete', ['uses' => 'Purchasing\PoNotesController@delete', 'as' => 'po_notes.delete']);
    Route::get('po_notes_datatables', ['uses' => 'Purchasing\PoNotesController@datatables', 'as' => 'po_notes.datatables'] );

    Route::resource('payment_methods', 'Purchasing\PaymentMethodController')->except(['show']);
    Route::post('payment_methods_delete', ['uses' => 'Purchasing\PaymentMethodController@delete', 'as' => 'payment_methods.delete']);
    Route::get('payment_methods_datatables', ['uses' => 'Purchasing\PaymentMethodController@datatables', 'as' => 'payment_methods.datatables'] );

    Route::get('get_notes/{id?}', ['uses' => 'Purchasing\PoNotesController@getNotesDetail', 'as' => 'get_notes'] );
    Route::get('get_supplier', ['uses' => 'Purchasing\SupplierController@loadData', 'as' => 'get_supplier'] );
    Route::get('get_payment_term', ['uses' => 'Purchasing\SupplierController@getPaymentTerm','as' => 'get_payment_term'] );
    Route::get('get_payment_method', ['uses' => 'Purchasing\SupplierController@getPaymentMethod','as' => 'get_payment_method'] );
    Route::get('get_supplier_pic/{pid?}', ['uses' => 'Purchasing\SupplierController@getSupplierContact','as' => 'get_supplier_pic'] );
    Route::get('get_supplier_pic_detail/{id?}', ['uses' => 'Purchasing\SupplierController@getSupplierContactDetail','as' => 'get_supplier_pic_detail'] );
    Route::get('suppliers_counts', ['uses' => 'Purchasing\SupplierController@counts', 'as' => 'suppliers.counts']);
    Route::get('get_pr/{location}', ['uses' => 'Logistic\PurchaseRequestController@getPR'] );

    Route::get('purchase_order_history/{id}', ['uses' =>'Purchasing\PoController@getItems', 'as' => 'po.items'] );

    Route::resource('itemslatestprice', 'Purchasing\ItemlatestpriceController');
    Route::get('itemslatestprice_datatables', ['uses' => 'Purchasing\ItemlatestpriceController@datatables', 'as' => 'itemslatestprice.datatables'] );

    Route::get('monitoring_pr', ['uses' => 'Purchasing\MonitoringItemController@index', 'as' => 'monitoring_pr'] );
    Route::get('monitoring_pr_datatables', ['uses' => 'Purchasing\MonitoringItemController@datatables', 'as' => 'monitoring_pr.datatables'] );
    Route::get('monitoring_pr_search', ['uses' => 'Purchasing\MonitoringItemController@search', 'as' => 'monitoring_pr.search'] );
    Route::get('monitoring_pr_export', ['uses' => 'Purchasing\MonitoringItemController@export', 'as' => 'monitoring_pr.export'] );
    Route::get('monitoring_pr_detail/{id}', ['uses' => 'Purchasing\MonitoringItemController@detail', 'as' => 'monitoring_pr.detail'] );

    //Monitoring Item PO
    Route::get('monitoring_po', ['uses' => 'Purchasing\MonitoringItemPoController@index', 'as' => 'monitoring_po'] );
    Route::get('monitoring_po_datatables', ['uses' => 'Purchasing\MonitoringItemPoController@datatables', 'as' => 'monitoring_po.datatables'] );
    Route::get('monitoring_po_search', ['uses' => 'Purchasing\MonitoringItemPoController@search', 'as' => 'monitoring_po.search'] );
    Route::get('monitoring_po_export', ['uses' => 'Purchasing\MonitoringItemPoController@export', 'as' => 'monitoring_po.export'] );
    Route::get('monitoring_po_show/{id}', ['uses' => 'Purchasing\MonitoringItemPoController@show', 'as' => 'monitoring_po.show'] );

    //Master Push Mail
    Route::get('po_post_mails', ['uses' => 'Purchasing\PoPostMailsController@index', 'as' => 'po_post_mails']);
    Route::get('po_post_mails_datatables', ['uses' => 'Purchasing\PoPostMailsController@datatables', 'as' => 'po_post_mails.datatables'] );
    Route::get('po_post_mails_create', ['uses' => 'Purchasing\PoPostMailsController@create', 'as' => 'po_post_mails.create']);
    Route::get('po_post_mails_edit/{id}', ['uses' => 'Purchasing\PoPostMailsController@edit', 'as' => 'po_post_mails.edit']);
    Route::post('po_post_mails_store', ['uses' => 'Purchasing\PoPostMailsController@store', 'as' => 'po_post_mails.store']);
    Route::put('po_post_mails_update/{id}', ['uses' => 'Purchasing\PoPostMailsController@update', 'as' => 'po_post_mails.update']);

    // CIRCULAR INVOICES
    Route::get('circular_invoice', ['uses' => 'Purchasing\CircularInvoiceController@index', 'as' => 'circular_invoice'] );
    Route::get('circular_invoice_datatables', ['uses' => 'Purchasing\CircularInvoiceController@datatables', 'as' => 'circular_invoice.datatables'] );
    Route::get('circular_invoice/create', ['uses' => 'Purchasing\CircularInvoiceController@create', 'as' => 'circular_invoice.create'] );
    Route::post('circular_invoice/store', ['uses' => 'Purchasing\CircularInvoiceController@store', 'as' => 'circular_invoice.store']);
    Route::post('circular_invoice/publish/{id}', ['uses' => 'Purchasing\CircularInvoiceController@publish', 'as' => 'circular_invoice.publish'] );
    Route::post('circular_invoice/cancel/{id}', ['uses' => 'Purchasing\CircularInvoiceController@cancel', 'as' => 'circular_invoice.cancel']);
    Route::get('circular-invoice/{id}/edit', ['uses' => 'Purchasing\CircularInvoiceController@edit', 'as' => 'circular_invoice.edit']);
    Route::put('circular-invoice/{id}/update', ['uses' => 'Purchasing\CircularInvoiceController@update', 'as' => 'circular_invoice.update']);
    Route::get('circular-invoice/{id}/show', ['uses' => 'Purchasing\CircularInvoiceController@show', 'as' => 'circular_invoice.show'] );
    Route::get('circular-invoice/{id}/print', ['uses' => 'Purchasing\CircularInvoiceController@print', 'as' => 'circular_invoice.print'] );
    Route::get('circular-invoice/print-multiple', ['uses' => 'Purchasing\CircularInvoiceController@printMultiple', 'as' => 'circular_invoice.print_multiple'] );
    Route::get('circular_invoice/ci_po', ['uses' => 'Purchasing\CircularInvoiceController@list', 'as' => 'circular_invoice.list'] );
    Route::get('circular_invoice_list_datatables', ['uses' => 'Purchasing\CircularInvoiceController@list_datatables', 'as' => 'circular_invoice.list_datatables'] );
    Route::get('circular_invoice_search', ['uses' => 'Purchasing\CircularInvoiceController@search', 'as' => 'circular_invoice.search'] );
    Route::get('circular_invoice_search_po', ['uses' => 'Purchasing\CircularInvoiceController@search_po', 'as' => 'circular_invoice.search_po'] );
    Route::post('circular_invoice/selesai', ['uses' => 'Purchasing\CircularInvoiceController@selesai', 'as' => 'circular_invoice.selesai'] );

    // Payment Completion
    Route::get('payment_completion', ['uses' => 'Purchasing\PaymentCompletionController@index', 'as' => 'payment_completion']);
    Route::get('payment_completion/list_po', ['uses' => 'Purchasing\PaymentCompletionController@list', 'as' => 'payment_completion.list']);
    Route::get('payment_completion_datatables', ['uses' => 'Purchasing\PaymentCompletionController@datatables', 'as' => 'payment_completion.datatables']);
    Route::get('payment_completion/{id}/show', ['uses' => 'Purchasing\PaymentCompletionController@show', 'as' => 'payment_completion.show']);
    Route::get('payment_completion/create', ['uses' => 'Purchasing\PaymentCompletionController@create', 'as' => 'payment_completion.create']);
    Route::get('payment_completion_po_datatables', ['uses' => 'Purchasing\PaymentCompletionController@list_po_datatables', 'as' => 'payment_completion.list_po_datatables']);
    Route::post('payment_completion/store', ['uses' => 'Purchasing\PaymentCompletionController@store', 'as' => 'payment_completion.store']);
    Route::get('payment_completion/{id}/edit', ['uses' => 'Purchasing\PaymentCompletionController@edit', 'as' => 'payment_completion.edit']);
    Route::put('payment_completion/{id}/update', ['uses' => 'Purchasing\PaymentCompletionController@update', 'as' => 'payment_completion.update']);
    Route::post('payment_completion/publish/{id}', ['uses' => 'Purchasing\PaymentCompletionController@publish', 'as' => 'payment_completion.publish']);
    Route::post('payment_completion/draft/{id}', ['uses' => 'Purchasing\PaymentCompletionController@draft', 'as' => 'payment_completion.draft']);
    Route::post('payment_completion/done/{id}', ['uses' => 'Purchasing\PaymentCompletionController@done', 'as' => 'payment_completion.done']);
    Route::get('payment_completion/sla', ['uses' => 'Purchasing\PaymentCompletionController@slaIndex', 'as' => 'payment_completion.sla']);
    Route::get('payment_completion/sla_data', ['uses' => 'Purchasing\PaymentCompletionController@slaDatatables', 'as' => 'payment_completion.sla_data']);
    Route::get('payment_completion/{id}/print', ['uses' => 'Purchasing\PaymentCompletionController@print', 'as' => 'payment_completion.print']);
    Route::post('payment_completion/reject', ['uses' => 'Purchasing\PaymentCompletionController@reject', 'as' => 'payment_completion.reject']);
    Route::post('payment_completion/tambahkelengkapan/{id}', ['uses' => 'Purchasing\PaymentCompletionController@tambahkelengkapan', 'as' => 'payment_completion.tambahkelengkapan']);
    Route::post('payment_completion/change_relation_po/{id}', ['uses' => 'Purchasing\PaymentCompletionController@changeRelationPo', 'as' => 'payment_completion.change_relation_po']);
    Route::get('payment_completion/get-po-list/{po}', ['uses' => 'Purchasing\PaymentCompletionController@getPoList', 'as' => 'payment_completion.get_po_list']);
    Route::get('payment_completion/search_po', ['uses' => 'Purchasing\PaymentCompletionController@search_po', 'as' => 'payment_completion.search_po']);
    Route::post('payment_completion/{id}/payment_detail/store', ['uses' => 'Purchasing\PaymentCompletionController@storePaymentDetail', 'as' => 'payment_completion.payment_detail.store']);
    Route::delete('payment_completion/payment_detail/{id}/destroy', ['uses' => 'Purchasing\PaymentCompletionController@destroyPaymentDetail', 'as' => 'payment_completion.payment_detail.destroy']);
    Route::get('payment_completion/search_invoice', ['uses' => 'Purchasing\PaymentCompletionController@searchInvoice', 'as' => 'payment_completion.search_invoice']);
    Route::get('payment_completion/export_excel', ['uses' => 'Purchasing\PaymentCompletionController@exportExcel', 'as' => 'payment_completion.export_excel']);

});

/*
|------------------------------------------------------------------------------------
| Approval
|------------------------------------------------------------------------------------
*/

Route::group(['prefix' => 'approval', 'as' => 'approval.', 'middleware'=>['auth']], function () {

    Route::get('purchase', ['uses' => 'Approval\PurchaseController@index', 'as' => 'purchase.index'] );
    Route::post('purchase_hold', ['uses' => 'Approval\PurchaseController@hold', 'as' => 'purchase.hold'] );
    Route::get('purchase_set/{id}', ['uses' => 'Approval\PurchaseController@set', 'as' => 'purchase.set'] );
    Route::post('purchase_update/{id}', ['uses' => 'Approval\PurchaseController@update', 'as' => 'purchase.update'] );
    Route::get('purchase_datatables', ['uses' => 'Approval\PurchaseController@datatables', 'as' => 'purchase.datatables'] );

    Route::get('po', ['uses' => 'Approval\PoController@index', 'as' => 'po.index'] );
    Route::get('po_set/{id}', ['uses' => 'Approval\PoController@set', 'as' => 'po.set'] );
    Route::post('po_update/{id}', ['uses' => 'Approval\PoController@update', 'as' => 'po.update'] );
    Route::post('po_update_multiple', ['uses' => 'Approval\PoController@updateMultiple', 'as' => 'po.update.multiple'] );
    Route::get('po_datatables', ['uses' => 'Approval\PoController@datatables', 'as' => 'po.datatables'] );

    Route::get('dph', ['uses' => 'Approval\DphController@index', 'as' => 'dph.index'] );
    Route::get('dph_set/{id}', ['uses' => 'Approval\DphController@set', 'as' => 'dph.set'] );
    Route::get('dph_datatables', ['uses' => 'Approval\DphController@datatables', 'as' => 'dph.datatables'] );
    Route::post('dph_update/{id}', ['uses' => 'Approval\DphController@update', 'as' => 'dph.update'] );
    // Route::post('dph_update_multiple', ['uses' => 'Approval\DphController@updateMultiple', 'as' => 'dph.update.multiple'] );

    Route::get('verify_pc', ['uses' => 'Approval\PaymentCompletionController@index', 'as' => 'verify_pc.index']);
    Route::get('verify_pc_datatables', ['uses' => 'Approval\PaymentCompletionController@datatables', 'as' => 'verify_pc.datatables']);
    Route::get('verify_pc/{id}/page', ['uses' => 'Approval\PaymentCompletionController@verifyPage', 'as' => 'verify_pc.page']);
    Route::post('verify_pc/{id}/lock', ['uses' => 'Approval\PaymentCompletionController@lock', 'as' => 'verify_pc.page_lock']);
    Route::post('verify_pc/{id}/done', ['uses' => 'Approval\PaymentCompletionController@done', 'as' => 'verify_pc.done']);

    Route::get('supplier',             ['uses' => 'Approval\SupplierController@index',      'as' => 'supplier.index']);
    Route::get('supplier_datatables',  ['uses' => 'Approval\SupplierController@datatables', 'as' => 'supplier.datatables']);
    Route::get('supplier_set/{id}',    ['uses' => 'Approval\SupplierController@set',        'as' => 'supplier.set']);
    Route::post('supplier_update/{id}',['uses' => 'Approval\SupplierController@update',     'as' => 'supplier.update']);
});


/*
|------------------------------------------------------------------------------------
| Monitoring
|------------------------------------------------------------------------------------
*/

Route::group(['prefix' => 'logistic', 'as' => 'logistic.', 'middleware'=>['auth']], function () {

    Route::get('logistic/verify-receipt-po',             ['uses' => 'Logistic\VerifyReceiptPoController@index',         'as' => 'verify-receipt-po.index']);
    Route::get('logistic/verify-receipt-po/lpb',         ['uses' => 'Logistic\VerifyReceiptPoController@datatablesLpb', 'as' => 'verify-receipt-po.lpb']);
    Route::get('logistic/verify-receipt-po/bpb',         ['uses' => 'Logistic\VerifyReceiptPoController@datatablesBpb', 'as' => 'verify-receipt-po.bpb']);
    Route::post('logistic/verify-receipt-po/verify',     ['uses' => 'Logistic\VerifyReceiptPoController@verify',        'as' => 'verify-receipt-po.verify']);
    Route::get('logistic/verify-receipt-po/show-lpb/{id}', ['uses' => 'Logistic\VerifyReceiptPoController@showLpb', 'as' => 'verify-receipt-po.show-lpb']);
    Route::get('logistic/verify-receipt-po/show-bpb/{id}', ['uses' => 'Logistic\VerifyReceiptPoController@showBpb', 'as' => 'verify-receipt-po.show-bpb']);
    Route::post('logistic/verify-receipt-po/request-perbaikan', ['uses' => 'Logistic\VerifyReceiptPoController@requestPerbaikan', 'as' => 'verify-receipt-po.request-perbaikan']);

    Route::resource('master_data_report','Logistic\MasterDataReportController');
    Route::get('master_data_report.pending_po', ['uses' => 'Logistic\MasterDataReportController@pending_po', 'as' => 'master_data_report.pending_po'] );
    Route::get('master_data_report.pending_pr', ['uses' => 'Logistic\MasterDataReportController@pending_pr', 'as' => 'master_data_report.pending_pr'] );
    Route::get('master_data_report.pending_approval', ['uses' => 'Logistic\MasterDataReportController@pending_approval', 'as' => 'master_data_report.pending_approval'] );
    Route::get('master_data_report.spb', ['uses' => 'Logistic\MasterDataReportController@spb', 'as' => 'master_data_report.spb'] );

    Route::get('monitoring', ['uses' => 'Logistic\MonitoringController@index', 'as' => 'monitoring.dpm'] );
    Route::get('monitoring_datatables', ['uses' => 'Logistic\MonitoringController@datatables', 'as' => 'monitoring.dpm.datatables'] );

    Route::get('monitoring_pending', ['uses' => 'Logistic\MonitoringController@index_pending', 'as' => 'monitoring.dpm_pending'] );
    Route::get('monitoring_datatables_pending', ['uses' => 'Logistic\MonitoringController@datatables_pending', 'as' => 'monitoring.dpm.datatables_pending'] );

    //DAHBOARD PENDING LOGISTIK
    Route::get('monitoring_count_dpm_pending', ['uses' => 'Logistic\MonitoringController@getDPMPending', 'as' => 'monitoring.monitoring_count_dpmp'] );
    Route::get('monitoring_count_pr_pending', ['uses' => 'Logistic\MonitoringController@getPRPending', 'as' => 'monitoring.monitoring_count_prp'] );
    Route::get('monitoring_count_pol_pending', ['uses' => 'Logistic\MonitoringController@getPOLPending', 'as' => 'monitoring.monitoring_count_polp'] );
    Route::get('monitoring_count_poj_pending', ['uses' => 'Logistic\MonitoringController@getPOJPending', 'as' => 'monitoring.monitoring_count_pojp'] );
    Route::get('monitoring_count_lpb_pending', ['uses' => 'Logistic\MonitoringController@getLPBPending', 'as' => 'monitoring.monitoring_count_lpbp'] );
    Route::get('monitoring_count_spb_pending', ['uses' => 'Logistic\MonitoringController@getSPBPending', 'as' => 'monitoring.monitoring_count_spbp'] );
    Route::get('export_lpb_pending', ['uses' => 'Logistic\MonitoringController@export_lpb_pending', 'as' => 'monitoring.export_lpb_pending'] );

    Route::get('monitoring_datatables_logistic', ['uses' => 'Logistic\MonitoringController@datatables_logistic', 'as' => 'monitoring.dpm.datatables_logistic'] );
    Route::get('monitoring_datatables_lpb_dashboard', ['uses' => 'Logistic\MonitoringController@datatables_lpb_dashboard', 'as' => 'monitoring.dpm.datatables_lpb_dashboard'] );
    Route::get('monitoring_detail/{id}', ['uses' => 'Logistic\MonitoringController@detail', 'as' => 'monitoring.dpm.detail'] );
    Route::get('monitoring_search', ['uses' => 'Logistic\MonitoringController@search', 'as' => 'monitoring.dpm.search'] );
    Route::post('monitoring_export/', ['uses' => 'Logistic\MonitoringController@export', 'as' => 'monitoring.dpm.export'] );

    Route::get('monitoring_inv', ['uses' => 'Logistic\MonitoringInvController@index', 'as' => 'monitoring.inv'] );
    Route::get('monitoring_inv_datatables', ['uses' => 'Logistic\MonitoringInvController@datatables', 'as' => 'monitoring.inv.datatables'] );
    Route::get('monitoring_inv_detail/{id}', ['uses' => 'Logistic\MonitoringInvController@detail', 'as' => 'monitoring.inv.detail'] );
    Route::get('monitoring_inv_search', ['uses' => 'Logistic\MonitoringInvController@search', 'as' => 'monitoring.inv.search'] );
    Route::post('monitoring_inv_export/', ['uses' => 'Logistic\MonitoringInvController@export', 'as' => 'monitoring.inv.export'] );

    Route::get('monitoring_item', ['uses' => 'Logistic\MonitoringItemController@index', 'as' => 'monitoring.item'] );
    Route::get('monitoring_item_datatables', ['uses' => 'Logistic\MonitoringItemController@datatables', 'as' => 'monitoring.item.datatables'] );
    Route::get('monitoring_item_detail/{id}', ['uses' => 'Logistic\MonitoringItemController@detail', 'as' => 'monitoring.item.detail'] );
    Route::get('monitoring_item_search', ['uses' => 'Logistic\MonitoringItemController@search', 'as' => 'monitoring.item.search'] );
    Route::get('monitoring_item_log/{id}', ['uses' => 'Logistic\MonitoringItemController@getLogItems', 'as' => 'monitoring.item.log'] );

    // Monitoring PR
    Route::get('monitoring_pr', ['uses' => 'Logistic\MonitoringItemPrController@index', 'as' => 'monitoring.item_pr'] );
    Route::get('monitoring_pr_datatables', ['uses' => 'Logistic\MonitoringItemPrController@datatables', 'as' => 'monitoring.item_pr.datatables'] );

    //Monitoring Item LPB
    Route::get('monitoring_lpb', ['uses' => 'Logistic\MonitoringItemLpbController@index', 'as' => 'monitoring.item_lpb'] );
    Route::get('monitoring_lpb_datatables', ['uses' => 'Logistic\MonitoringItemLpbController@datatables', 'as' => 'monitoring.item_lpb.datatables'] );
    Route::get('monitoring_lpb_export', ['uses' => 'Logistic\MonitoringItemLpbController@export', 'as' => 'monitoring_lpb.export'] );
    Route::get('data_item_lpb/{product_id}/{company_id}', ['uses' => 'Logistic\MonitoringItemLpbController@getDataMonitoringItemLpb', 'as' => 'monitoring_lpb.data_item_lpb']);

    //Monitoring Item SPB
    Route::get('monitoring_spb', ['uses' => 'Logistic\MonitoringItemSpbController@index', 'as' => 'monitoring.item_spb'] );
    Route::get('monitoring_spb_datatables', ['uses' => 'Logistic\MonitoringItemSpbController@datatables', 'as' => 'monitoring.item_spb.datatables'] );

    // Monitoring BPB Jakarta
    Route::get('monitoring_bpb_jakarta', ['uses' => 'Logistic\MonitoringItemBpbJakartaController@index', 'as' => 'monitoring.item_bpb_jakarta']);
    Route::get('monitoring_bpb_jakarta_datatables', ['uses' => 'Logistic\MonitoringItemBpbJakartaController@datatables', 'as' => 'monitoring.item_bpb_jakarta.datatables'] );

    // Monitoring BPB Lokal
    Route::get('monitoring_bpb_lokal', ['uses' => 'Logistic\MonitoringItemBpbLokalController@index', 'as' => 'monitoring.item_bpb_lokal']);
    Route::get('monitoring_bpb_lokal_datatables', ['uses' => 'Logistic\MonitoringItemBpbLokalController@datatables', 'as' => 'monitoring.item_bpb_lokal.datatables'] );

    // Route::get('monitoring_lpb_search', ['uses' => 'Logistic\MonitoringItemLpbController@search', 'as' => 'monitoring_lpb.search'] );
    // Route::get('monitoring_lpb_show/{id}', ['uses' => 'Logistic\MonitoringItemLpbController@show', 'as' => 'monitoring_lpb.show'] );


/*
|------------------------------------------------------------------------------------
| Monitoring Inventory
|------------------------------------------------------------------------------------
*/

    Route::resource('lpb', 'Logistic\LpbController', ['parameters' => ['create' => 'id']]);
    Route::get('lpb_po', ['uses' => 'Logistic\LpbController@list', 'as' => 'lpb.list'] );
    Route::get('lpb_po_datatabels', ['uses' => 'Logistic\LpbController@listDatatables', 'as' => 'lpb.list.datatables'] );
    Route::get('lpb_datatables', ['uses' => 'Logistic\LpbController@datatables', 'as' => 'lpb.datatables'] );
    Route::get('lpb_search', ['uses' => 'Logistic\LpbController@search', 'as' => 'lpb.search'] );
    Route::get('lpb_view_revisi/{id}', ['uses' => 'Logistic\LpbController@view_revisi', 'as' => 'lpb.view_revisi']);
    Route::post('lpb_revisi/{id}', ['uses' => 'Logistic\LpbController@revisi', 'as' => 'lpb.revisi']);
    Route::post('lpb_delete', ['uses' => 'Logistic\LpbController@delete', 'as' => 'lpb.delete']);
    Route::get('lpb_print/{id}/{type}', ['uses' => 'Logistic\LpbController@print', 'as' => 'lpb.print'] );
    Route::get('lpb_detail/{id}', ['uses' => 'Logistic\LpbController@detail', 'as' => 'lpb.detail'] );
    Route::get('lpb_export/', ['uses' => 'Logistic\LpbController@export', 'as' => 'lpb.export'] );

    Route::post('close_lpb/{id}', ['uses' => 'Logistic\LpbController@close', 'as' => 'lpb.close_lpb'] );

    Route::get('get_dokumen_lpb/{id}', ['uses' => 'Logistic\LpbController@getDokumenLpbById', 'as' => 'lpb.get_dokumen_lpb'] );
    Route::post('lpb_upload_dokumen', ['uses' => 'Logistic\LpbController@uploadDokumenLpb', 'as' => 'lpb.upload_dokumen'] );

    Route::resource('spb', 'Logistic\SpbController', ['parameters' => ['create' => 'id']]);
    Route::get('spb_datatables', ['uses' => 'Logistic\SpbController@datatables', 'as' => 'spb.datatables'] );
    Route::get('spb_lpb', ['uses' => 'Logistic\SpbController@list', 'as' => 'spb.list'] );
    Route::get('spb_lpb_datatabels', ['uses' => 'Logistic\SpbController@listDatatables', 'as' => 'spb.list.datatables'] );
    Route::get('spb_search', ['uses' => 'Logistic\SpbController@search', 'as' => 'spb.search'] );
    Route::post('spb_delete', ['uses' => 'Logistic\SpbController@delete', 'as' => 'spb.delete']);
    Route::get('spb_print/{id}/{type}', ['uses' => 'Logistic\SpbController@print', 'as' => 'spb.print'] );
    Route::post('spb_revision', ['uses' => 'Logistic\SpbController@revision', 'as' => 'spb.revision'] );
    Route::get('spb_export/', ['uses' => 'Logistic\SpbController@export', 'as' => 'spb.export'] );
    Route::post('spb_publish/{id}', ['uses' => 'Logistic\SpbController@publish', 'as' => 'spb.publish'] );
    Route::get('spb_popup/{id}', ['uses' => 'Logistic\SpbController@popup', 'as' => 'spb.popup'] );
    Route::get('spb_items', ['uses' => 'Logistic\SpbController@remove', 'as' => 'spb.item'] );
    Route::get('spb_remove_lpb', ['uses' => 'Logistic\SpbController@remove_lpb', 'as' => 'spb.remove_lpb'] );
    Route::get('get_dokumen_spb/{id}', ['uses' => 'Logistic\SpbController@getDokumenSpbById', 'as' => 'spb.get_dokumen_spb'] );
    Route::post('spb_upload_dokumen', ['uses' => 'Logistic\SpbController@uploadDokumenSpb', 'as' => 'spb.upload_dokumen'] );
    Route::post('spb_set_done/{id}', ['uses' => 'Logistic\SpbController@set_done', 'as' => 'spb.set_done'] );
    Route::get('get_item_spb_by_id/{id}', ['uses' => 'Logistic\SpbController@getProductItemById', 'as' => 'spb.getItem'] );


    Route::resource('bpb', 'Logistic\BpbController', ['parameters' => ['create' => 'id']]);
    Route::get('bpb_datatables', ['uses' => 'Logistic\BpbController@datatables', 'as' => 'bpb.datatables'] );
    Route::get('bpb_spb', ['uses' => 'Logistic\BpbController@list', 'as' => 'bpb.list'] );
    Route::get('bpb_spb_datatables', ['uses' => 'Logistic\BpbController@listDatatables', 'as' => 'bpb.list.datatables'] );
    Route::get('bpb_search', ['uses' => 'Logistic\BpbController@search', 'as' => 'bpb.search'] );
    Route::post('bpb_delete', ['uses' => 'Logistic\BpbController@delete', 'as' => 'bpb.delete']);
    Route::get('bpb_print/{id}/{type}', ['uses' => 'Logistic\BpbController@print', 'as' => 'bpb.print'] );
    Route::get('bpb_export/', ['uses' => 'Logistic\BpbController@export', 'as' => 'bpb.export'] );
    Route::post('bpb_publish/{id}', ['uses' => 'Logistic\BpbController@publish', 'as' => 'bpb.publish'] );

    Route::get('get_dokumen_bpb/{id}', ['uses' => 'Logistic\BpbController@getDokumenBpbById', 'as' => 'bpb.get_dokumen_bpb'] );
    Route::post('bpb_upload_dokumen', ['uses' => 'Logistic\BpbController@uploadDokumenBpb', 'as' => 'bpb.upload_dokumen'] );

    Route::resource('bpb_franco', 'Logistic\BpbFrancoController', ['parameters' => ['create' => 'id']]);
    Route::get('bpb_franco_datatables', ['uses' => 'Logistic\BpbFrancoController@datatables', 'as' => 'bpb_franco.datatables'] );
    Route::get('bpb_franco_spb', ['uses' => 'Logistic\BpbFrancoController@list', 'as' => 'bpb_franco.list'] );
    Route::get('bpb_franco_spb_datatables', ['uses' => 'Logistic\BpbFrancoController@listDatatables', 'as' => 'bpb_franco.list.datatables'] );
    Route::get('bpb_franco_search', ['uses' => 'Logistic\BpbFrancoController@search', 'as' => 'bpb_franco.search'] );
    Route::post('bpb_franco_delete', ['uses' => 'Logistic\BpbFrancoController@delete', 'as' => 'bpb_franco.delete']);
    Route::get('bpb_franco_print/{id}/{type}', ['uses' => 'Logistic\BpbFrancoController@print', 'as' => 'bpb_franco.print'] );
    Route::get('bpb_franco_export/', ['uses' => 'Logistic\BpbFrancoController@export', 'as' => 'bpb_franco.export'] );
    Route::post('bpb_franco_publish/{id}', ['uses' => 'Logistic\BpbFrancoController@publish', 'as' => 'bpb.publish'] );

    Route::get('get_dokumen_bpb_franco/{id}', ['uses' => 'Logistic\BpbFrancoController@getDokumenBpbFrancoById', 'as' => 'bpb_franco.get_dokumen_bpb_franco'] );
    Route::post('bpb_franco_upload_dokumen', ['uses' => 'Logistic\BpbFrancoController@uploadDokumenBpbFranco', 'as' => 'bpb_franco.upload_dokumen'] );

    Route::resource('inventory', 'Logistic\InventoryController');
    Route::get('inventory_before_index', ['uses' => 'Logistic\InventoryController@before_index', 'as' => 'inventory.before_index'] );
    Route::get('inventory_datatables', ['uses' => 'Logistic\InventoryController@datatables', 'as' => 'inventory.datatables'] );
    Route::get('inventory_search', ['uses' => 'Logistic\InventoryController@search', 'as' => 'inventory.search'] );
    Route::get('inventory_datatables_search', ['uses' => 'Logistic\InventoryController@search_datatables', 'as' => 'inventory.datatables.search'] );
    Route::post('inventory_delete', ['uses' => 'Logistic\InventoryController@delete', 'as' => 'inventory.delete']);
    Route::get('inventory_qr/{id}', ['uses' => 'Logistic\InventoryController@qr', 'as' => 'inventory.qr'] );
    Route::post('inventory_export/', ['uses' => 'Logistic\InventoryController@export', 'as' => 'inventory.export'] );
    Route::post('inventory_export_aging/', ['uses' => 'Logistic\InventoryController@export_aging', 'as' => 'inventory.export_aging'] );
    Route::post('inventory_stock_opname/', ['uses' => 'Logistic\InventoryController@stock_opname', 'as' => 'inventory.stock_opname'] );
    Route::post('inventory_proses/', ['uses' => 'Logistic\InventoryController@proses', 'as' => 'inventory.proses'] );
    Route::get('inventory_print_qr', ['uses' => 'Logistic\InventoryController@print_qr', 'as' => 'inventory.print_qr'] );
    Route::match(['get', 'post'], 'inventory_import', ['uses' => 'Logistic\InventoryController@import', 'as' => 'inventory.import']);
    Route::get('inventory_print_single/{id}', ['uses' => 'Logistic\InventoryController@print_single', 'as' => 'inventory.print_single'] );
    Route::get('inventory_print_merge', ['uses' => 'Logistic\InventoryController@print_merge', 'as' => 'inventory.print_merge'] );
    Route::get('inventory-get-locations', ['uses' => 'Logistic\InventoryController@getLocations', 'as' => 'inventory-get-locations'] );

    // SCAN
    Route::get('inventory_scan_qr', ['uses' => 'Logistic\InventoryController@scan_qr', 'as' => 'inventory.scan_qr'] );
    Route::post('inventory_store_scan_qr', ['uses' => 'Logistic\InventoryController@store_scan_qr', 'as' => 'inventory.store_scan_qr'] );
    Route::get('inventory_check-uuid/{uuid}', ['uses' => 'Logistic\InventoryController@checkUuid', 'as' => 'inventory.checkUuid'] );
    Route::get('inventory_fetch_produk_by_uuid/{uuid}', ['uses' => 'Logistic\InventoryController@fetch_produk_by_uuid', 'as' => 'inventory.fetch_produk_by_uuid'] );
    Route::post('store_status_label_applied', ['uses' => 'Logistic\InventoryController@store_status_label_applied', 'as' => 'inventory.store_status_label_applied'] );

    Route::resource('master_user_asset', 'Logistic\MasterUserAssetController');
    Route::get('master_user_asset_datatables', ['uses' => 'Logistic\MasterUserAssetController@datatables', 'as' => 'master_user_asset_datatables.datatables'] );


    // ASSET
    Route::resource('inventory_asset', 'Logistic\InventoryAssetController');
    Route::get('inventory_asset_datatables', ['uses' => 'Logistic\InventoryAssetController@datatables', 'as' => 'inventory_asset_datatables.datatables'] );
    Route::get('get_product_by_req', ['uses' => 'Logistic\InventoryAssetController@getProductByReq','as' => 'get_product_by_req' ] );
    Route::get('get_data_relation/{type?}/{product_id?}', ['uses' => 'Logistic\InventoryAssetController@getDataRelation','as' => 'get_data_relation']);
    Route::post('create_instant', ['uses' => 'Logistic\InventoryAssetController@create_instant','as' => 'inventory_asset.create_instant' ] );
    Route::get('get_data_department_by_company/{company_id?}', ['uses' => 'Logistic\InventoryAssetController@getDataDeptByCompany','as' => 'get_data_department_by_company']);
    Route::get('get_data_location_by_company/{company_id?}', ['uses' => 'Logistic\InventoryAssetController@getDataLocByCompany','as' => 'get_data_location_by_company']);
    Route::get('inventory_asset_print/{id}', ['uses' => 'Logistic\InventoryAssetController@print', 'as' => 'inventory_asset.print'] );
    Route::post('inventory_asset_print_merge', ['uses' => 'Logistic\InventoryAssetController@print_merge', 'as' => 'inventory_asset.print_merge'] );
    Route::resource('parent_inventory_asset', 'Logistic\ParentInventoryAssetController');
    Route::get('parent_inventory_asset_datatables', ['uses' => 'Logistic\ParentInventoryAssetController@datatables', 'as' => 'parent_inventory_asset_datatables.datatables'] );
    Route::resource('user_inventory_asset', 'Logistic\UserInventoryAssetController');
    Route::get('user_inventory_asset_datatables', ['uses' => 'Logistic\UserInventoryAssetController@datatables', 'as' => 'user_inventory_asset_datatables.datatables'] );

    Route::post('inventory_asset/{id}', ['uses' => 'Logistic\InventoryAssetController@destroy', 'as' => 'logistic.inventory_asset.destroy'] );
    Route::get('parent_inventory_asset_print/{id}', ['uses' => 'Logistic\ParentInventoryAssetController@print', 'as' => 'parent_inventory_asset.print']);

    Route::get('inventory_mutation', ['uses' => 'Logistic\InventoryMutationController@index', 'as' => 'inventory.mutation'] );
    Route::get('inventory_mutation_export', ['uses' => 'Logistic\InventoryMutationController@export', 'as' => 'inventory.mutation.export'] );

    Route::get('inventory_mutation_summary_month', ['uses' => 'Logistic\InventoryMutationController@summary_month', 'as' => 'inventory.mutation.summary_month'] );
    Route::get('inventory_mutation_summary_month_datatables/{month}/{year}', ['uses' => 'Logistic\InventoryMutationController@datatables_month', 'as' => 'inventory.mutation.summary_month.datatables'] );
    Route::post('inventory_mutation_summary_month_export', ['uses' => 'Logistic\InventoryMutationController@summary_month_export', 'as' => 'inventory.mutation.summary_month.export'] );

    Route::get('get_stock/{product_id?}/{product_type?}/{location_id?}', ['uses' => 'Logistic\InventoryController@getQty', 'as' => 'get_stock'] );
    Route::get('get_stock_product/{product_id?}/{location_id?}', ['uses' => 'Logistic\InventoryController@getStock', 'as' => 'get_stock_product'] );
    Route::get('get_inventory/{location_id?}', ['uses' => 'Logistic\InventoryController@loadData', 'as' => 'inventory.get_data'] );
    Route::get('get_data_inventory/{location_id?}', ['uses' => 'Logistic\InventoryController@getData', 'as' => 'inventory.getdata'] );

    Route::get('history/{id}', ['uses' => 'Logistic\InventoryController@history','as' => 'inventory.history']);
    Route::get('history_export/{id}', ['uses' => 'Logistic\InventoryController@history_export','as' => 'inventory.history.export']);
    Route::get('history_datatables/{id}', ['uses' => 'Logistic\InventoryController@historyDatatables', 'as' => 'inventory.history.datatables'] );

    Route::get('adjustment/{id}', ['uses' => 'Logistic\InventoryController@adjustment','as' => 'inventory.adjustment']);
    Route::get('adjustment_datatables/{id}', ['uses' => 'Logistic\InventoryController@adjustmentDatatables', 'as' => 'inventory.adjustment.datatables'] );
    Route::get('adjustment_add/{id}', ['uses' => 'Logistic\InventoryController@getAdjustment','as' => 'inventory.adjustment.add']);
    Route::post('adjustment_store', ['uses' => 'Logistic\InventoryController@storeAdjustment','as' => 'inventory.adjustment.store']);
    Route::get('adjustment_print/{id}', ['uses' => 'Logistic\InventoryController@getPrintAdjustment', 'as' => 'inventory.adjustment.print'] );

    Route::resource('adjustment_stock', 'Logistic\AdjustmentStockController');
    Route::get('adjustment_stock_datatables', ['uses' => 'Logistic\AdjustmentStockController@datatables', 'as' => 'adjustment_stock.datatables'] );
    Route::get('adjustment_stock_search', ['uses' => 'Logistic\AdjustmentStockController@search', 'as' => 'adjustment_stock.search'] );
    Route::get('adjustment_stock_export', ['uses' => 'Logistic\AdjustmentStockController@export', 'as' => 'adjustment_stock.export'] );

    Route::get('writeoff/{id}', ['uses' => 'Logistic\InventoryController@writeoff','as' => 'inventory.writeoff']);
    Route::post('writeoff_store', ['uses' => 'Logistic\InventoryController@storeWriteoff','as' => 'inventory.writeoff.store']);
    Route::get('writeoff_print/{id}', ['uses' => 'Logistic\InventoryController@getPrintWriteoff', 'as' => 'inventory.writeoff.print'] );

    //AdjustmentMerge
    Route::get('adjustment_merge', ['uses' => 'Logistic\InventoryController@adjustment_merge','as' => 'inventory.adjustment_merge']);
    Route::post('adjustment_store_merge', ['uses' => 'Logistic\InventoryController@storeAdjustment_merge','as' => 'inventory.adjustment_merge.store']);

    Route::resource('write_off', 'Logistic\WriteOffController')->except(['show']);
    Route::get('write_off_datatables', ['uses' => 'Logistic\WriteOffController@datatables', 'as' => 'write_off.datatables'] );
    Route::get('write_off_search', ['uses' => 'Logistic\WriteOffController@search', 'as' => 'write_off.search'] );
    Route::get('write_off_export', ['uses' => 'Logistic\WriteOffController@export', 'as' => 'write_off.export'] );

    Route::resource('ttb','Logistic\TtbController');
    Route::get('ttb_datatables', ['uses' => 'Logistic\TtbController@datatables', 'as' => 'ttb.datatables'] );
    Route::get('ttb_search', ['uses' => 'Logistic\TtbController@search', 'as' => 'ttb.search'] );
    Route::get('ttb_export', ['uses' => 'Logistic\TtbController@export', 'as' => 'ttb.export'] );
    Route::get('ttb_print/{id}/{type?}', ['uses' => 'Logistic\TtbController@print', 'as' => 'ttb.print'] );
    Route::post('ttb_delete', ['uses' => 'Logistic\TtbController@delete', 'as' => 'ttb.delete']);
    Route::post('ttb_publish/{id}', ['uses' => 'Logistic\TtbController@publish', 'as' => 'ttb.publish'] );
    Route::get('ttb_remove_item/{id}/{param?}', ['uses' => 'Logistic\TtbController@remove_item', 'as' => 'ttb.remove_item'] );
    Route::match(['get', 'post'], 'ttb_revision/{id}', ['uses' => 'Logistic\TtbController@revision', 'as' => 'ttb.revision']);

    Route::resource('conversion','Logistic\ConversionController');
    Route::get('conversion_datatables', ['uses' => 'Logistic\ConversionController@datatables', 'as' => 'conversion.datatables'] );
    Route::get('conversion_search', ['uses' => 'Logistic\ConversionController@search', 'as' => 'conversion.search'] );
    Route::get('conversion_export', ['uses' => 'Logistic\ConversionController@export', 'as' => 'conversion.export'] );
    Route::get('conversion_print/{id}', ['uses' => 'Logistic\ConversionController@print', 'as' => 'conversion.print'] );
    Route::post('conversion_delete', ['uses' => 'Logistic\ConversionController@delete', 'as' => 'conversion.delete']);
    Route::post('conversion_publish/{id}', ['uses' => 'Logistic\ConversionController@publish', 'as' => 'conversion.publish'] );

    Route::resource('transfer_in','Logistic\InventoryTransferInController');
    Route::get('transfer_in_datatables', ['uses' => 'Logistic\InventoryTransferInController@datatables', 'as' => 'transfer_in.datatables'] );
    Route::get('transfer_in_search', ['uses' => 'Logistic\InventoryTransferInController@search', 'as' => 'transfer_in.search'] );
    Route::get('transfer_in_export', ['uses' => 'Logistic\InventoryTransferInController@export', 'as' => 'transfer_in.export'] );
    Route::get('transfer_in_print/{id}', ['uses' => 'Logistic\InventoryTransferInController@print', 'as' => 'transfer_in.print'] );
    Route::post('transfer_in_delete', ['uses' => 'Logistic\InventoryTransferInController@delete', 'as' => 'transfer_in.delete']);
    Route::match(['get', 'post'], 'transfer_in_received', ['uses' => 'Logistic\InventoryTransferInController@received', 'as' => 'transfer_in.received']);
    Route::get('transfer_add', ['uses' => 'Logistic\InventoryTransferInController@add', 'as' => 'transfer_in.add'] );
    Route::get('transfer_add_datatables', ['uses' => 'Logistic\InventoryTransferInController@add_datatables', 'as' => 'transfer_in.add.datatables'] );
    Route::get('transfer_in_check/{id}', ['uses' => 'Logistic\InventoryTransferInController@check', 'as' => 'transfer_in.check'] );
    Route::put('transfer_in_check_store/{id}', ['uses' => 'Logistic\InventoryTransferInController@check_store', 'as' => 'transfer_in.check_store'] );

    Route::resource('transfer_out','Logistic\InventoryTransferOutController');
    Route::get('transfer_out_datatables', ['uses' => 'Logistic\InventoryTransferOutController@datatables', 'as' => 'transfer_out.datatables'] );
    Route::get('transfer_out_search', ['uses' => 'Logistic\InventoryTransferOutController@search', 'as' => 'transfer_out.search'] );
    Route::get('transfer_out_export', ['uses' => 'Logistic\InventoryTransferOutController@export', 'as' => 'transfer_out.export'] );
    Route::get('transfer_out_print/{id}', ['uses' => 'Logistic\InventoryTransferOutController@print', 'as' => 'transfer_out.print'] );
    Route::post('transfer_out_delete', ['uses' => 'Logistic\InventoryTransferOutController@delete', 'as' => 'transfer_out.delete']);
    Route::match(['get', 'post'], 'transfer_out_approval', ['uses' => 'Logistic\InventoryTransferOutController@approval', 'as' => 'transfer_out.approval']);


    Route::get('get_location_destination/{type?}/{company_id?}', [
        'uses' => 'Logistic\InventoryTransferOutController@getLocationsByTypeTransferOut',
        'as' => 'get_location_destination'
    ]);
    Route::get('get_department_by_location/{lokasi_destinasi?}', [
        'uses' => 'Logistic\InventoryTransferOutController@getDepartmentByLocation',
        'as' => 'get_department_by_location'
    ]);

    Route::resource('return_out','Logistic\ReturnOutController');
    Route::get('return_out_datatables', ['uses' => 'Logistic\ReturnOutController@datatables', 'as' => 'return_out.datatables'] );
    Route::get('return_out_search', ['uses' => 'Logistic\ReturnOutController@search', 'as' => 'return_out.search'] );
    Route::get('return_out_export', ['uses' => 'Logistic\ReturnOutController@export', 'as' => 'return_out.export'] );
    Route::get('return_out_print/{id}', ['uses' => 'Logistic\ReturnOutController@print', 'as' => 'return_out.print'] );
    Route::post('return_out_delete', ['uses' => 'Logistic\ReturnOutController@deleteReturn', 'as' => 'return_out.delete']);
    Route::get('return_out_popup/{id}', ['uses' => 'Logistic\ReturnOutController@popup', 'as' => 'return_out.popup'] );

    Route::resource('return_in','Logistic\ReturnInController');
    Route::get('return_in_datatables', ['uses' => 'Logistic\ReturnInController@datatables', 'as' => 'return_in.datatables'] );
    Route::get('return_in_search', ['uses' => 'Logistic\ReturnInController@search', 'as' => 'return_in.search'] );
    Route::get('return_in_export', ['uses' => 'Logistic\ReturnInController@export', 'as' => 'return_in.export'] );
    Route::get('return_in_print/{id}', ['uses' => 'Logistic\ReturnInController@print', 'as' => 'return_in.print'] );
    Route::post('return_in_delete', ['uses' => 'Logistic\ReturnInController@deleteReturn', 'as' => 'return_in.delete']);

    Route::get('return_in_approve/{id}', ['uses' => 'Logistic\ReturnInController@approve', 'as' => 'return_in.approve'] );
    Route::post('return_in_approve', ['uses' => 'Logistic\ReturnInController@approve_store', 'as' => 'return_in.approve.store'] );

    Route::get('return_in_list', ['uses' => 'Logistic\ReturnInController@list', 'as' => 'return_in.list'] );
    Route::get('return_in_list_datatabels', ['uses' => 'Logistic\ReturnInController@listDatatables', 'as' => 'return_in.list.datatables'] );

    Route::get('get_lpb', ['uses' => 'Logistic\LpbController@getLpb'] );
    Route::get('get_spb', ['uses' => 'Logistic\SpbController@getSpb'] );
    Route::get('get_spb_bpb', ['uses' => 'Logistic\SpbController@getSpbBpb'] );
    Route::get('get_bpb', ['uses' => 'Logistic\BpbController@getSpb'] );

    Route::resource('insurance_cargo', 'Logistic\InsuranceCargoController', ['parameters' => ['create' => 'id']]);
    Route::get('insurance_cargo_datatables', ['uses' => 'Logistic\InsuranceCargoController@datatables', 'as' => 'insurance_cargo.datatables'] );
    Route::get('insurance_cargo_spb', ['uses' => 'Logistic\InsuranceCargoController@list', 'as' => 'insurance_cargo.list'] );
    Route::post('insurance_cargo_delete', ['uses' => 'Logistic\InsuranceCargoController@delete', 'as' => 'insurance_cargo.delete']);
    Route::get('insurance_cargo_print/{id}/{type}', ['uses' => 'Logistic\InsuranceCargoController@print', 'as' => 'insurance_cargo.print'] );
    Route::post('insurance_cargo_export', ['uses' => 'Logistic\InsuranceCargoController@export', 'as' => 'insurance_cargo.export'] );
    Route::get('insurance_cargo_list_datatables', ['uses' => 'Logistic\InsuranceCargoController@listDatatables', 'as' => 'insurance_cargo.list.datatables'] );

    Route::resource('insurance_unit', 'Logistic\InsuranceUnitController', ['parameters' => ['create' => 'id']]);
    Route::get('insurance_unit_datatables', ['uses' => 'Logistic\InsuranceUnitController@datatables', 'as' => 'insurance_unit.datatables'] );
    Route::get('insurance_unit_spb', ['uses' => 'Logistic\InsuranceUnitController@list', 'as' => 'insurance_unit.list'] );
    Route::post('insurance_unit_delete', ['uses' => 'Logistic\InsuranceUnitController@delete', 'as' => 'insurance_unit.delete']);
    Route::get('insurance_unit_print/{id}/{type}', ['uses' => 'Logistic\InsuranceUnitController@print', 'as' => 'insurance_unit.print'] );
    Route::post('insurance_unit_export', ['uses' => 'Logistic\InsuranceUnitController@export', 'as' => 'insurance_unit.export'] );
    Route::get('insurance_unit_list_datatables', ['uses' => 'Logistic\InsuranceUnitController@listDatatables', 'as' => 'insurance_unit.list.datatables'] );

    Route::get('dpm_add', ['uses' => 'Logistic\PurchaseRequestController@form','as' => 'dpm.create']);

    Route::get('stock_opname', ['uses' => 'Logistic\StockOpnameController@index', 'as' => 'stock_opname'] );
    Route::get('stock_opname_datatables', ['uses' => 'Logistic\StockOpnameController@datatables', 'as' => 'stock_opname.datatables'] );

    Route::resource('insurance', 'Logistic\InsuranceController', ['parameters' => ['create' => 'id']]);
    Route::get('insurance_datatables', ['uses' => 'Logistic\InsuranceController@datatables', 'as' => 'insurance.datatables'] );
    Route::get('insurance_spb', ['uses' => 'Logistic\InsuranceController@list', 'as' => 'insurance.list'] );
    Route::get('insurance_print/{id}/{type}', ['uses' => 'Logistic\InsuranceController@print', 'as' => 'insurance.print'] );
    Route::post('insurance_export', ['uses' => 'Logistic\InsuranceController@export', 'as' => 'insurance.export'] );
    Route::get('insurance_list_datatables', ['uses' => 'Logistic\InsuranceController@listDatatables', 'as' => 'insurance.list.datatables'] );
    Route::get('insurance_approved/{id}', ['uses' => 'Logistic\InsuranceController@approved', 'as' => 'insurance.approved'] );
    Route::post('insurance_approved_update', ['uses' => 'Logistic\InsuranceController@update_approved', 'as' => 'insurance.update_approved']);
});



/*
|------------------------------------------------------------------------------------
| Accounting
|------------------------------------------------------------------------------------
*/

Route::group(['prefix' => 'accounting', 'as' => 'accounting.', 'middleware'=>['auth']], function () {

    Route::resource('asset', 'Accounting\AssetController');
    Route::get('asset_datatables', ['uses' => 'Accounting\AssetController@datatables', 'as' => 'asset.datatables'] );
    Route::get('asset_print/{id}/{type}', ['uses' => 'Accounting\AssetController@print', 'as' => 'asset.print'] );

    Route::resource('asset_category', 'Accounting\AssetCategoryController');
    Route::get('asset_category_datatables', ['uses' => 'Accounting\AssetCategoryController@datatables', 'as' => 'asset_category.datatables'] );
    Route::get('asset_category_get/{id?}', ['uses' => 'Accounting\AssetCategoryController@get', 'as' => 'asset_category.get'] );

});


Route::get('/', [ 'middleware' => 'auth','uses' => 'DashboardController@index']);
Route::get('export_new_dashboard', ['uses' => 'DashboardController@export_new', 'as' => 'export_new_dashboard'] );
Route::get('export_instan/{bulan}/{tahun}', ['uses' => 'DashboardController@export_instan', 'as' => 'export_instan'] );
Route::get('export_instan_pending_table', ['uses' => 'DashboardController@export_instan_pending_table', 'as' => 'export_instan_pending_table'] );
Route::get('pdf/{bulan}/{tahun}', ['uses' => 'DashboardController@pdf', 'as' => 'pdf'] );

Route::get('export_instan_item_lpb_30Days', ['uses' => 'DashboardController@export_instan_item_lpb_30Days', 'as' => 'export_instan_item_lpb_30Days'] );

Route::get('POHaritaShipping/{id}/{uuid}', ['uses' => 'PoController@show', 'as' => 'po_off_auth.show'] );

Route::get('PrintPO/{doc_no}/{id}/{type}/{uuid}', ['uses' => 'PoController@print', 'as' => 'print_po.no_auth'] );

// VERIFIKASI DOKUMEN VIA QR (PUBLIK, TANPA LOGIN)
Route::get('verify/lpb/{uuid}', ['uses' => 'DocumentVerificationController@lpb', 'as' => 'verify.lpb'] );
Route::get('verify/bpb/{uuid}', ['uses' => 'DocumentVerificationController@bpb', 'as' => 'verify.bpb'] );

// UNTUK MONITORING ITEM BPB
Route::get('shipping/getAllDataItemBpb', ['uses' =>'API\ApiController@getAllDataItemBpb','as' => 'api.shipping.get_all_data_item_bpb']);

// UNTUK MENAMPILKAN SEMUA DOKUMEN BPB
Route::get('shipping/getAllBpbData', ['uses' =>'API\ApiController@getAllBpbData','as' => 'api.shipping.get_all_bpb_data']);

// UNTUK MENAMPILKAN SHOW BPB BY ID
Route::get('shipping/getBpbDataByIdBpb/{id}', ['uses' =>'API\ApiController@getBpbDatabyIdBpb','as' => 'api.shipping.get_bpb_data_by_id_bpb']);

// Route::get('shipping/testGetData', ['uses' =>'API\ApiController@testGetData','as' => 'api.shipping.testGetData']);

// FIX API
Route::get('apiPoShipping', ['uses' =>'API\ApiController@apiPoShipping','as' => 'api.apiPoShipping']);

// SHOW DATA ASSET
Route::get('inventory_asset/{id}/{uuid}', ['uses' => 'InventoryAssetController@show', 'as' => 'inventory_asset_no_auth.show'] );
Route::get('download_lampiran_inventory_asset/download_lampiran/{idd}', ['uses' => 'InventoryAssetController@downloadLampiran', 'as' => 'download_lampiran_inventory_asset'] );

//API TO SHIP-HR
Route::get('transferAssetData', ['uses' =>'API\ShipHrTransferController@transferAssetData','as' => 'api.transferAssetData']);
Route::get('getAssetDataById/{id_hash}', ['uses' =>'API\ShipHrTransferController@getAssetDataById','as' => 'api.getAssetDataById']);

Auth::routes();

