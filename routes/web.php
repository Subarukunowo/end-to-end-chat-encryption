<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/chat');
    });

    Route::get('/chat', function () {
        return view('chat.index');
    })->name('chat');
    
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');

    Route::get('/api/contacts/search', function (\Illuminate\Http\Request $request) {
        $query = $request->input('q');
        $user = auth()->user();
        
        $contacts = $user->contacts()
            ->when($query, function ($q, $query) {
                return $q->where('name', 'ilike', "%{$query}%");
            })
            ->get();
            
        return response()->json($contacts);
    });

    Route::post('/api/conversations/with/{contact_id}', [ChatController::class, 'getConversationWith']);
    Route::get('/api/conversations/{conversation}/messages', [ChatController::class, 'getMessages']);
    Route::post('/api/chat/{conversation}', [ChatController::class, 'storeMessage']);
});