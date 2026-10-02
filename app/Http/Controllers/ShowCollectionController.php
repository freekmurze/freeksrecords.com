<?php

namespace App\Http\Controllers;

use App\Support\CollectionPage;
use Inertia\Response;

class ShowCollectionController extends Controller
{
    public function __invoke(CollectionPage $page): Response
    {
        return $page->render();
    }
}
