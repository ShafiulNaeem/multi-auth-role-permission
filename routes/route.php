<?php
use Illuminate\Support\Facades\Route;
use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
use Symfony\Component\HttpFoundation\Response;

//Route::get('/test1', function () {
//    return 'ok';
//});

/*
 * auth guards
 */
Route::get('/guards', function () {
    return sendResponse(
        'Data fetch successfully.',
        \Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard::all(),
        Response::HTTP_OK
    );
})->name('guards');
//
//Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
//    //guard wise route list
//    Route::get('permission/module/{guard}', [RolePermissionController::class, 'module_permission'])->name('module.route.list');
//
//    Route::get('/list', [RolePermissionController::class, 'index'])->name('list');
//    Route::post('/create', [RolePermissionController::class, 'store'])->name('create');
//    Route::get('/show/{id}', [RolePermissionController::class, 'show'])->name('show');
//    Route::put('/update/{id}', [RolePermissionController::class, 'update'])->name('update');
//    Route::delete('/delete/{id}', [RolePermissionController::class, 'destroy'])->name('delete');
//    Route::post('/user/permission/add', [RolePermissionController::class, 'user_permission'])->name('user.permission.add');
//    Route::post('/user/permission/list', [RolePermissionController::class, 'get_user_permission_list'])->name('user.permission.list');
//});

//Route::middleware('check.auth:web')->group(function () {
//    Route::middleware('permission:web')->group(function () {
//        Route::group(['prefix' => 'test', 'as' => 'test.'], function () {
//            //guard wise route list
//            Route::get('/a', function () {
//                return 'ok';
//            })->name('a');
//            Route::get('/b', function () {
//                return 'ok';
//            })->name('b');
//        });
//    });
//});