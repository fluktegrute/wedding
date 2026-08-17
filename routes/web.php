<?php

use App\Livewire\Admin\Photos as AdminPhotos;
use App\Livewire\Admin\QrCode as AdminQrCode;
use App\Livewire\Admin\Tags as AdminTags;
use App\Livewire\Home;
use App\Livewire\Photos\Index;
use App\Livewire\Photos\Show;
use App\Livewire\Photos\Upload;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', Home::class)->name('dashboard');

    Route::livewire('photos', Index::class)->name('photos.index');
    Route::livewire('photos/create', Upload::class)->name('photos.create');
    Route::livewire('photos/{photo}', Show::class)->name('photos.show');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('photos', AdminPhotos::class)->name('photos');
        Route::livewire('tags', AdminTags::class)->name('tags');
        Route::livewire('qr-code', AdminQrCode::class)->name('qr-code');
    });
});

require __DIR__.'/settings.php';
