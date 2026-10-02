<?php

namespace App\Http\Controllers;

use App\Support\RecordCollection;
use App\Support\RecordShareImage;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class RecordShareImageController extends Controller
{
    public function __invoke(int $instanceId, RecordCollection $collection, RecordShareImage $image): Response
    {
        $record = $collection->findSummary($instanceId);

        abort_if($record === null, 404);

        $renderedFields = Arr::only($record, ['cover', 'artist', 'displayTitle', 'originalYear']);
        $cacheKey = 'record-share-image-v3:'.hash('sha256', json_encode($renderedFields, JSON_THROW_ON_ERROR));

        $png = Cache::store('file')->rememberForever($cacheKey, fn (): string => $image->render($record));

        return response($png, headers: [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
