<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class SeoService
{
    /**
     * @return array<string, mixed>
     */
    public function forCurrentRoute(): array
    {
        $route = Route::current();
        $name = $route ? $route->getName() : null;
        $uri = $route ? $route->uri() : '/';

        $pages = config('seo.pages', []);
        $page = [];

        if ($uri && isset($pages[$uri])) {
            $page = $pages[$uri];
        } elseif ($name && isset($pages[$name])) {
            $page = $pages[$name];
        }

        $defaults = config('seo.default', []);
        $seo = array_merge($defaults, $page);

        $seo['canonical'] = isset($page['canonical'])
            ? $page['canonical']
            : $this->canonicalForUri($uri);
        $seo['og_title'] = isset($seo['og_title']) ? $seo['og_title'] : $seo['title'];
        $seo['og_description'] = isset($seo['og_description']) ? $seo['og_description'] : $seo['description'];
        $seo['og_image'] = isset($seo['og_image']) ? $seo['og_image'] : config('seo.og_image');
        $seo['og_url'] = $seo['canonical'];
        $seo['og_type'] = isset($seo['og_type']) ? $seo['og_type'] : 'website';
        $seo['site_name'] = config('seo.site_name', 'Зебра Таргет');
        $seo['h1'] = isset($seo['h1']) ? $seo['h1'] : ($seo['title'] ?? '');
        $seo['benefits'] = isset($seo['benefits']) ? $seo['benefits'] : [];
        $seo['faq'] = isset($seo['faq']) ? $seo['faq'] : [];

        return $seo;
    }

    /**
     * @param string $uri
     * @return string
     */
    private function canonicalForUri($uri)
    {
        if ($uri === '/' || $uri === '') {
            return url('/');
        }

        return url('/' . ltrim($uri, '/'));
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function sitemapUrls(): array
    {
        $urls = [];

        foreach (config('seo.pages', []) as $key => $page) {
            if (empty($page['sitemap'])) {
                continue;
            }

            $path = $key === '/' ? '/' : '/' . ltrim($key, '/');

            $urls[] = [
                'loc' => url($path),
                'changefreq' => 'weekly',
                'priority' => $key === '/' ? '1.0' : '0.8',
                'lastmod' => date('Y-m-d'),
            ];
        }

        return $urls;
    }
}
