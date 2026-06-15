<?php

use Illuminate\Http\Request;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->middleware('guest')->name('login');

$portalSections = [
    'dashboard' => 'dashboard',
    'dashabord' => 'dashboard',
    'portal' => 'dashboard',
    'fleet' => 'fleet',
    'fleets' => 'fleet',
    'customers' => 'customers',
    'hires' => 'hires',
    'hire-management' => 'hires',
    'invoice' => 'invoicing',
    'invoices' => 'invoicing',
    'invoicing' => 'invoicing',
    'reports' => 'reports',
    'maintenance' => 'maintenance',
    'navman' => 'navman',
    'documents' => 'documents',
    'settings' => 'settings',
];

foreach ($portalSections as $uri => $section) {
    Route::get('/'.$uri, function () use ($section) {
        return view('portal', ['section' => $section]);
    })->middleware('auth')->name($uri === 'portal' ? 'portal' : 'portal.'.$uri);
}

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/invoice/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoice.pdf');
    Route::get('/invoice/{invoice}/view-pdf', [InvoiceController::class, 'viewPdf'])->name('invoice.view-pdf');
    Route::post('/invoice/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoice.send');
});


