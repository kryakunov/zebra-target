<?php

namespace App\Http\Controllers;

use App\Services\SeoService;

class SitemapController extends Controller
{
    public function index(SeoService $seoService)
    {
        $urls = $seoService->sitemapUrls();

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
