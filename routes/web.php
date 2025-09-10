<?php

use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\Auth\Register;

//Route::get('/', function () {
//    return view('welcome');
//})->name('home');
Route::post('/upload-image', [App\Http\Controllers\ImageUploadController::class, 'upload'])->name('upload.image');

// ...existing code...
Route::prefix('/v0')->group(function() {
    Route::get('/', 'App\Http\Controllers\HomeController@landingPage')->name('lp');
    Route::get('/inicio', 'App\Http\Controllers\HomeController@index')->name('index');
    Route::resource('resources', ResourceController::class);
    Route::get('/about', 'App\Http\Controllers\HomeController@about')->name('about');
    Route::get('/team', 'App\Http\Controllers\HomeController@team')->name('team');


    Route::get('/search/tags/{tagId}', 'App\Http\Controllers\ResourceController@searchByTag')->name('searchByTag');
    Route::get('/search/subject/{subjectId}', 'App\Http\Controllers\ResourceController@searchBySubject')->name('searchBySubject');
    Route::get('/search/type/{typeId}', 'App\Http\Controllers\ResourceController@searchByFormat')->name('searchByFormat');
    Route::get('/search/', 'App\Http\Controllers\ResourceController@showAll')->name('showAll');
    Route::get('/search/user/{userId}', 'App\Http\Controllers\ResourceController@showByUser')->name('showByUser');

    Route::get('/special/{subjectSlug}', 'App\Http\Controllers\SubjectController@showSpecialPage')->name('showSpecialPage');
});

Route::group(['middleware' => ['auth', 'active_user']], function() {
    Route::get('/home', 'App\Http\Controllers\HomeController@home')->name('home');

    Route::get('users', 'App\Http\Controllers\Auth\RegisterController@showUsers')->name('showUsers');
    Route::get('users/{userId}', 'App\Http\Controllers\Auth\RegisterController@editUser')->name('editUser');
    Route::post('new-users', 'App\Http\Controllers\Auth\RegisterController@updateUser')->name('updateUser');
    Route::post('new-user', 'App\Http\Controllers\Auth\RegisterController@create')->name('create');
    Route::patch('users/{userId}/toggle-status', 'App\Http\Controllers\Auth\RegisterController@toggleStatus')->name('users.toggleStatus');

    Route::resource('tags', TagController::class);
    Route::patch('tags/{tagId}/toggle-status', 'App\Http\Controllers\TagController@toggleStatus')->name('tags.toggleStatus');

    Route::resource('subjects', SubjectController::class);
    Route::patch('subjects/{resourceId}/toggle-status', 'App\Http\Controllers\SubjectController@toggleStatus')->name('subjects.toggleStatus');

    Route::post('tickets/bug-report', 'App\Http\Controllers\TicketController@bugReport')->name('bugReport');
    Route::post('tickets/help-request', 'App\Http\Controllers\TicketController@helpRequest')->name('helpRequest');
    Route::post('tickets/resource-report', 'App\Http\Controllers\TicketController@resourceReport')->name('resourceReport');

    Route::resource('tickets', TicketController::class);
    Route::get('tickets/management/{tab}/{status?}', 'App\Http\Controllers\TicketController@index')->name('tickets.tabs.index');
    Route::post('tickets/{ticket}/add-comment', 'App\Http\Controllers\TicketController@addComment')->name('tickets.addComment');
    Route::get('tickets/{ticket}/owner', 'App\Http\Controllers\TicketController@editOwner')->name('tickets.editOwner');
    Route::get('tickets/{ticket}/responsible', 'App\Http\Controllers\TicketController@editResponsible')->name('tickets.editResponsible');
});

Route::get('/', 'App\Http\Controllers\HomeController@indexV2')
    ->name('v2.index');
Route::get('/about', 'App\Http\Controllers\HomeController@aboutV2')
    ->name('v2.about');
Route::get('/team', 'App\Http\Controllers\HomeController@teamV2')
    ->name('v2.team');

Route::get('/special/{subjectSlug}', 'App\Http\Controllers\SubjectController@showSpecialPageV2')
    ->name('v2.showSpecialPage');

Route::get('/researcher/{slug}', 'App\Http\Controllers\ResourceController@showByUserV2')
    ->name('v2.authorPage');

Route::prefix('/search')->group(function(){
    Route::get('/', 'App\Http\Controllers\ResourceController@showAllV2')
        ->name('v2.showAll');

    Route::get('/tags/{tagId}', 'App\Http\Controllers\ResourceController@searchByTagV2')
        ->name('v2.searchByTag');

    Route::get('/type/{typeId}', 'App\Http\Controllers\ResourceController@searchByFormat')
        ->name('v2.searchByFormat');

    Route::get('/subject/{subjectId}', 'App\Http\Controllers\ResourceController@searchBySubjectV2')
        ->name('v2.searchBySubject');
});



Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
