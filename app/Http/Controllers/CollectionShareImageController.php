<?php

namespace App\Http\Controllers;

use App\Support\RecordCollection;
use App\Support\RecordShareImage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class CollectionShareImageController extends Controller
{
    public function __invoke(RecordCollection $collection, RecordShareImage $image): Response
    {
        $records = $collection->latest(5);

        $covers = array_column($records, 'cover');
        $cacheKey = 'collection-share-image-v2:'.hash('sha256', json_encode($covers, JSON_THROW_ON_ERROR));

        $png = Cache::store('file')->rememberForever($cacheKey, fn (): string => $image->renderCollection($records));

        return response($png, headers: [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
