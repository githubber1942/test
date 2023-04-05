<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

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

Route::get('/home', function () {
    return view('home');
});

Route::post('/save-api-key', function (Illuminate\Http\Request $request) {
    $apiKey = $request->input('api_key');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->get('https://connect.mailerlite.com/api/subscribers', []);

    $msg = $response->json();
    $stat = $response->status();

    if ($response->status() === 401) {
        $errorMsg = isset($msg['message']) ? $msg['message'] : 'No response message';
        return redirect('/home')->with('error', 'Invalid API key! ' . $errorMsg . ' with status ' . $stat);
        // return redirect('/home')->with('error', 'Invalid API key! ' . json_encode($msg.message));
    }

    if ($response->failed() || $response->status() !== 200) {
        return redirect('/home')->with('error', 'Invalid API key! ' . json_encode($msg) . ' with status ' . $stat);
    }
    
    return redirect('/home')->with([
        'success' => 'API key checks out! ' . json_encode($msg) . ' with status ' . $stat,
        'response' => json_encode($msg),
        'key' => $apiKey
    ]);
})->name('save-api-key');