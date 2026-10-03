<?php

namespace App\Http\Controllers;

use App\Support\StoredShareImages;
use Illuminate\Http\RedirectResponse;

class CollectionShareImageController extends Controller
{
    public function __invoke(StoredShareImages $shareImages): RedirectResponse
    {
        return redirect()
            ->away($shareImages->collectionUrl())
            ->setCache(['public' => true, 'max_age' => 3600, 's_maxage' => 3600]);
    }
}
