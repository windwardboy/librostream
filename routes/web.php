<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudiobookController; // Import the AudiobookController

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

// Homepage - list all audiobooks
Route::get('/', [AudiobookController::class, 'index'])->name('audiobooks.index');

// Show a single audiobook
// Using {audiobook:slug} for route model binding by slug
Route::get('/audiobooks/{audiobook:slug}', [AudiobookController::class, 'show'])->name('audiobooks.show');

// Route for listing audiobooks by tag (category, author, narrator, etc.)
Route::get('/audiobooks/tag/{tag}', [AudiobookController::class, 'byTag'])->name('audiobooks.byTag');

// We can keep the welcome route for now, or remove it if the homepage will be the audiobook list.
// Route::get('/welcome-laravel', function () {
//     return view('welcome');
// });

// Static Pages
Route::get('/about', function () {
    return view('pages.about');
})->name('pages.about');

Route::get('/terms-of-service', function () {
    return view('pages.terms');
})->name('pages.terms');

Route::get('/privacy-policy', function () {
    return view('pages.privacy');
})->name('pages.privacy');

// Contact Us Page
Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('pages.contact');

// FAQ Page
Route::get('/faq', [App\Http\Controllers\FaqController::class, 'index'])->name('pages.faq');

// Cookie Policy Page
Route::get('/cookie-policy', [App\Http\Controllers\CookiePolicyController::class, 'index'])->name('pages.cookie-policy');
