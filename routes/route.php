<?php
use Illuminate\Support\Facades\Route;
use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
use Symfony\Component\HttpFoundation\Response;

Route::get('/test1', function () {
    return 'ok';
});

/*
 * auth guards
 */
Route::get('/guards', function () {
    return sendResponse(
        'Data fetch successfully.',
        \Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard::all(),
        Response::HTTP_OK
    );
});

/*
 * guard wise route list
*/
Route::get('role/permission/module/{guard}', [RolePermissionController::class, 'module_permission'])->name('module.route.list');

/*
* role route
*/
Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
    Route::get('/list', [RolePermissionController::class, 'index'])->name('list');
    Route::post('/create', [RolePermissionController::class, 'store'])->name('create');
    Route::get('/show/{id}', [RolePermissionController::class, 'show'])->name('show');
    Route::put('/update/{id}', [RolePermissionController::class, 'update'])->name('update');
    Route::delete('/delete/{id}', [RolePermissionController::class, 'destroy'])->name('delete');
});

Route::middleware('check.auth:web')->group(function () {

    Route::middleware('permission:web')->group(function () {
        // test route
        Route::group(['prefix' => 'test', 'as' => 'testing.'],function (){
            Route::delete('/test/{id}/ff', function () {
                return 'ok';
            })->name('index_route.index');
        });
    });
});