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

function getAPI_Key() {
    return DB::table('api_keys')->value('key');
};

Route::get('/', function () {
    return redirect('/home');
    // return view('welcome');
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/home', function () {
    $apiKey = DB::table('api_keys')->value('key');
    session(['key' => $apiKey]);
    return view('home');
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

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

    DB::table('api_keys')->updateOrInsert(['key' => $apiKey], ['key' => $apiKey]);
    
    return redirect('/home')->with([
        'success' => 'API key checks out!',
        'response' => json_encode($msg),
        'key' => $apiKey
    ]);
})->name('save-api-key')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/key', function () {
    $apiKey = DB::table('api_keys')->value('key');
    return view('key')->with([
        'key' => $apiKey,
    ]);
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/display', function () {
    $apiKey = DB::table('api_keys')->value('key');
    return view('display')->with([
        'key' => $apiKey,
    ]);
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/newsubscribers', function () {
    return view('newsubscribers');
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/newsubscribers/data', function () {
    $apiKey = DB::table('api_keys')->value('key');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->get('https://connect.mailerlite.com/api/subscribers', []);

    $subscribers = $response->json()['data'];
    $meta = json_encode($response->json()['meta']);
    $links = json_encode($response->json()['links']);
    
    $formattedData = [];
    foreach ($subscribers as $subscriber) {
        $formattedData[] = [
            'id' => $subscriber['id'],
            'email' => $subscriber['email'],
            'name' => $subscriber['fields']['name'],
            'country' => $subscriber['fields']['country'],
            'subscribed_at' => $subscriber['subscribed_at'],
            'subscribed_at' => $subscriber['subscribed_at'],
        ];
    }

    session([
        'meta' => $meta,
        'links' => $links,
    ]);

    return response()->json([
        'data' => $formattedData,
        'meta' => $meta,
        'links' => $links,
    ]);
})->name('newsubscribers.data')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/subscribers1', function () {
    session([
        'thisy' => "https://connect.mailerlite.com/api/subscribers"
    ]);
    return view('subscribers1');
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

// Attempted to use an optional parameter to make requests to a different enpoint everytime (using response's links.next). Didn't manage to pass an optional parameter in any way
Route::get('/subscribers1/data/{endpointy?}', function ($endpointy = "https://connect.mailerlite.com/api/subscribers") {
    $apiKey = DB::table('api_keys')->value('key');

    $received = $endpointy;
    $booly = false;
    if ($endpointy !== "https://connect.mailerlite.com/api/subscribers") {
        $booly = true;
    }

    $endpoint = session('thisy');
    $was = true;
    if ($endpoint !== "https://connect.mailerlite.com/api/subscribers") {
        $endpoint = "https://connect.mailerlite.com/api/subscribers";
        $was = false;
    } 

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->get($endpoint, []);

    $subscribers = $response->json()['data'];
    $meta = json_encode($response->json()['meta']);
    $links = json_encode($response->json()['links']);
    $next = $response->json()['links']['next'];
    
    $formattedData = [];
    foreach ($subscribers as $subscriber) {
        $formattedData[] = [
            'id' => $subscriber['id'],
            'email' => $subscriber['email'],
            'bool' => $received,
            // 'fields.name' => $subscriber['fields.name'],
            // 'fields.country' => $subscriber['fields.country'],
            // 'subscribed_at' => $subscriber['subscribed_at'],
            // 'subscribed_at' => $subscriber['subscribed_at'],
        ];
    }

    session([
        'meta' => $meta,
        'links' => $links,
        'thisy' => $next,
        // 'nextw' => $nextw,
    ]);

    return response()->json([
        'data' => $formattedData,
        'meta' => $meta,
        'links' => $links,
        'next' => $next,
        // 'reqreq' => $request['headers']->json(),
    ]);

    // return compact('subscribers');
})->name('subscribers1.data')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/subscribers', function () {
    $apiKey = DB::table('api_keys')->value('key');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->get('https://connect.mailerlite.com/api/subscribers', []);

    $subscribers = $response->json()['data'];
    // $links = $response->json()['links'];
    session([
        'endpoint' => "https://connect.mailerlite.com/api/subscribers",
    ]);

    return view('subscribers', compact('subscribers'));
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('/subscribers-next/{endpoint}', function ($endpoint, Illuminate\Http\Request $request) {
    $apiKey = DB::table('api_keys')->value('key');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->get($endpoint, []);

    // $subscribers = $response->json()['data'];
    // $links = $response->json()['links'];
    // session([
    //     'reqformat' => 'Request: ' . json_encode($request->all()),
    // ]);
    $subscribers = $response->json()['data'];
    $endpoint = $response->json()['links']['next'];
    
    $formattedData = [];
    foreach ($subscribers as $subscriber) {
        $formattedData[] = [
            'email' => $subscriber['email'],
            'fields.name' => $subscriber['fields.name'],
            'fields.country' => $subscriber['fields.country'],
            'subscribed_at' => $subscriber['subscribed_at'],
            'subscribed_at' => $subscriber['subscribed_at'],
        ];
    }

    return response()->json([
        'data' => $formattedData,
        'endpoint' => $endpoint,
    ]);
})->name('subscribers-next')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::get('create-subscriber', function () {
    return view('create-subscriber');
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::post('create-subscriber', function (Illuminate\Http\Request $request) {
    $apiKey = DB::table('api_keys')->value('key');

    $email = $request->input('email');
    $name = $request->input('name');
    $country = $request->input('country');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->post('https://connect.mailerlite.com/api/subscribers', [
        'email' => $email
    ]);

    
    $resp = $response->json();
    $body = json_encode($resp);
    $status = $response->status();
    if ($status == 200) {
        return back()->with([
            'error' => 'Subscriber already exists.',
            'response' => $body,
            'status' => $status,
        ]);
    }
    if ($status !== 201) {
        return back()->with([
            'error' => 'An error occurred while adding the subscriber.',
            'response' => $body,
            'status' => $status,
        ]);
    }

    $id = $resp['data']['id'];
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->put('https://connect.mailerlite.com/api/subscribers/' . $id, [
        'fields' => [
            'name' => $name,
            'country' => $country,
        ],
    ]);

    if ($response->status() == 200) {
        return back()->with([
            'success' => 'Subscriber added successfully!',
            'response' => $body,
            'status' => $response->status(),
        ]);
    }
    return back()->with([
        'error' => 'Subscriber created but error occurred registering parameters.',
        'response' => $body,
        'status' => $response->status(),
    ]);
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::post('edit-subscriber', function (Illuminate\Http\Request $request) {
    $apiKey = DB::table('api_keys')->value('key');

    $email = $request->input('email');
    $name = $request->input('name');
    $country = $request->input('country');

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ])->get('https://connect.mailerlite.com/api/subscribers/' . $email, []);

    $resp = $response->json();
    $body = json_encode($resp);
    $status = $response->status();

    if ($status !== 200) {
        return back()->with([
            'error' => 'An error occurred while fetching the subscriber.',
            'response' => $body,
            'status' => $status,
        ]);
    }
    
    $id = $resp['data']['id'];
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->put('https://connect.mailerlite.com/api/subscribers/' . $id, [
        'fields' => [
            'name' => $name,
            'country' => $country,
        ],
    ]);

    if ($response->status() == 200) {
        return back()->with([
            'success' => 'Subscriber edited successfully!',
            'response' => $body,
            'status' => $response->status(),
        ]);
    }
    return back()->with([
        'error' => 'Error occurred while editing parameters.',
        'response' => $body,
        'status' => $response->status(),
    ]);
})->name('edit-subscriber')->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

Route::post('delete-subscriber', function () {
    $apiKey = DB::table('api_keys')->value('key');

    $id = request('id');
    
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ])->delete('https://connect.mailerlite.com/api/subscribers/' . $id, []);


    $body = $response->json();

    if ($response->status() == 204) {
        return back()->with([
            'success' => 'Subscriber deleted successfully!',
            'response' => $body,
            'status' => $response->status(),
        ]);
    }
    if ($response->status() == 404) {
        return back()->with([
            'error' => 'Subscriber not found',
            'response' => $body,
            'status' => $response->status(),
        ]);
    }
    return back()->with([
        'error' => 'Error occurred while deleting subscriber.',
        'response' => $body,
        'status' => $response->status(),
    ]);
})->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);