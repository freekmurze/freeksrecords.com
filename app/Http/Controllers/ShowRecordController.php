<?php

namespace App\Http\Controllers;

use App\Support\CollectionPage;
use App\Support\RecordCollection;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ShowRecordController extends Controller
{
    public function __invoke(
        int $instanceId,
        RecordCollection $collection,
        CollectionPage $page,
        ?string $slug = null,
    ): Response|RedirectResponse {
        $record = $collection->findSummary($instanceId);

        abort_if($record === null, 404);

        if ($slug !== $collection->slug($record)) {
            return redirect()->to($record['shareUrl'], 301);
        }

        return $page->render($record);
    }
}
