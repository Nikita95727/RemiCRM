<?php

use App\Modules\Contact\Livewire\ContactsList;
use Illuminate\Support\Facades\Route;

Route::get('/', ContactsList::class)->name('contacts.index');
Route::get('/contacts', ContactsList::class)->name('contacts.list');
