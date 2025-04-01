<?php

use App\Http\Controllers\AppointmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/appointments', [AppointmentController::class, 'store']);
Route::get('/appointments', [AppointmentController::class, 'filterAppointments']);
Route::get('/availability', [AppointmentController::class, 'getAvailableTimes']);
Route::get('/calendar/event/{id}', [AppointmentController::class, 'getAppointment']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
