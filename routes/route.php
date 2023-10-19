<?php
use Illuminate\Support\Facades\Route;
use Shafiulnaeem\MultiAuthRolePermission\Http\Controllers\RolePermissionController;
use Symfony\Component\HttpFoundation\Response;

// auth guards
Route::get('/guards', function () {
    return sendResponse(
        'Data fetch successfully.',
        \Shafiulnaeem\MultiAuthRolePermission\Models\AuthGuard::all(),
        Response::HTTP_OK
    );
})->name('guards');
