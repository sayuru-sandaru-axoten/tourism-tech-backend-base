<?php

use Illuminate\Support\Facades\Route;

// Per-domain route files, required here under the shared v1 prefix, so this index
// stays short as more domains land (ARCHITECTURE.md §6) — add one require per domain.
Route::prefix('v1')->group(function (): void {
    require app_path('Domains/Identity/routes.php');
<<<<<<< Updated upstream
=======
    require app_path('Domains/Partners/routes.php');
    require app_path('Domains/Destinations/routes.php');
    require app_path('Domains/Experiences/routes.php');
>>>>>>> Stashed changes
});
