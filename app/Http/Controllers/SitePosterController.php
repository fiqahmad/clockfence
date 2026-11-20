<?php

namespace App\Http\Controllers;

use App\Clock\SitePoster;
use App\Models\Site;
use Illuminate\View\View;

/**
 * A printable A4 Poster for a Site's entrance.
 */
class SitePosterController extends Controller
{
    public function __invoke(Site $site): View
    {
        abort_if($site->isArchived(), 410);

        return view('posters.show', [
            'site' => $site,
            'qrSvg' => SitePoster::svg($site),
        ]);
    }
}
