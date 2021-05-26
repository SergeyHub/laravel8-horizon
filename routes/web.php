<?php

use App\Models\User;
use App\Jobs\SomeJob;
use App\Mail\OrderShipped;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

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

Route::get('/jobs/{jobs}/{user}', function ($jobs, $user) {
    $user = User::find($user);

    for ($i=0; $i < $jobs; $i++) {
        SomeJob::dispatch($user);
    }
});

Route::post('/', function () {
    Mail::to('sergey@sergey.com')->queue(new OrderShipped);

    return redirect('/');
});

Route::get('/batch', function () {
    $batch = Bus::batch([
        new SomeJob(User::find(1)),
        new SomeJob(User::find(2)),
        new SomeJob(User::find(3)),
        new SomeJob(User::find(4)),
        new SomeJob(User::find(5)),
    ])->then(function (Batch $batch) {
        // All jobs completed successfully...
    })->catch(function (Batch $batch, Throwable $e) {
        // First batch job failure detected...
    })->finally(function (Batch $batch) {
        // The batch has finished executing...
        Log::info('Batch of SomeJobs are complete');
    })->name('Batch of SomeJobs')->dispatch();

    return $batch->id;
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
