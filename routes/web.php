<?php

use Illuminate\Http\Request;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\XeroController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->middleware('guest')->name('login');

Route::get('/admin', function () {
    return view('login');
})->middleware('guest')->name('admin.login');

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

    return redirect()->route('admin.login');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/invoice/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoice.pdf');
    Route::get('/invoice/{invoice}/view-pdf', [InvoiceController::class, 'viewPdf'])->name('invoice.view-pdf');
    Route::post('/invoice/{invoice}/send', [InvoiceController::class, 'sendEmail'])->name('invoice.send');
     Route::get('/xero/connect', [XeroController::class, 'connect'])->name('xero.connect');
    Route::get('/xero/callback', [XeroController::class, 'callback'])->name('xero.callback');
    Route::post('/xero/sync', [XeroController::class, 'sync'])->name('xero.sync');
    Route::post('/xero/disconnect', [XeroController::class, 'disconnect'])->name('xero.disconnect');
    Route::post('/xero/invoices/{invoice}/push', [XeroController::class, 'pushInvoice'])->name('xero.invoices.push');
});

