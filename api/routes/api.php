<?php

declare(strict_types=1);

\Illuminate\Support\Facades\Route::apiResource('contacts', \App\Containers\Contacts\Controllers\ContactsApiController::class)
    ->only(['index', 'show', 'store', 'update', 'destroy'])
    ->parameters([
        'contacts' => 'contactId',
    ]);

\Illuminate\Support\Facades\Route::post('contacts/import', [\App\Containers\Contacts\Controllers\ContactsImportApiController::class, 'store']);
\Illuminate\Support\Facades\Route::get('contacts/import/{importId}/failures', [\App\Containers\Contacts\Controllers\ContactsImportApiController::class, 'failures']);

\Illuminate\Support\Facades\Route::get('imports/{importId}', [\App\Containers\Imports\Controllers\JobImportsStatusApiController::class, 'show'])->name('imports.show');
