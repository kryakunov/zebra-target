@php
    $seo = isset($seo) ? $seo : [];
    $seoTitle = $seo['title'] ?? 'Зебра Таргет';
    $seoDescription = $seo['description'] ?? '';
    $seoRobots = $seo['robots'] ?? 'index, follow';
    $seoCanonical = $seo['canonical'] ?? url()->current();
    $seoOgTitle = $seo['og_title'] ?? $seoTitle;
    $seoOgDescription = $seo['og_description'] ?? $seoDescription;
    $seoOgImage = $seo['og_image'] ?? 'https://zebra-target.ru/logo-small.png';
    $seoOgUrl = $seo['og_url'] ?? $seoCanonical;
    $seoOgType = $seo['og_type'] ?? 'website';
    $seoSiteName = $seo['site_name'] ?? 'Зебра Таргет';
@endphp
<title>@hasSection('title')@yield('title')@else{{ $seoTitle }}@endif</title>
<meta name="description" content="@hasSection('description')@yield('description')@else{{ $seoDescription }}@endif">
<meta name="robots" content="@hasSection('robots')@yield('robots')@else{{ $seoRobots }}@endif">
<link rel="canonical" href="@hasSection('canonical')@yield('canonical')@else{{ $seoCanonical }}@endif">
<meta property="og:locale" content="ru_RU">
<meta property="og:type" content="{{ $seoOgType }}">
<meta property="og:site_name" content="{{ $seoSiteName }}">
<meta property="og:title" content="@hasSection('og_title')@yield('og_title')@else{{ $seoOgTitle }}@endif">
<meta property="og:description" content="@hasSection('og_description')@yield('og_description')@else{{ $seoOgDescription }}@endif">
<meta property="og:image" content="{{ $seoOgImage }}">
<meta property="og:url" content="{{ $seoOgUrl }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@hasSection('og_title')@yield('og_title')@else{{ $seoOgTitle }}@endif">
<meta name="twitter:description" content="@hasSection('og_description')@yield('og_description')@else{{ $seoOgDescription }}@endif">
<meta name="twitter:image" content="{{ $seoOgImage }}">
