Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});