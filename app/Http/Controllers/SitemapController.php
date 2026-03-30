<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $siteUrl = rtrim((string) config('civic_atlas.public_url'), '/');

        $urls = [
            [
                'loc' => $siteUrl.'/',
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
        ];

        foreach (Facility::query()->published()->latest('updated_at')->get(['slug', 'updated_at']) as $facility) {
            $urls[] = [
                'loc' => $siteUrl.route('facilities.show', ['facility' => $facility->slug], false),
                'lastmod' => optional($facility->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
