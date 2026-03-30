@php
    $siteName = config('civic_atlas.name', config('app.name'));
    $siteUrl = rtrim((string) config('civic_atlas.public_url', config('app.url')), '/');
    $defaultTitle = config('civic_atlas.seo.default_title', $siteName);
    $defaultDescription = config('civic_atlas.seo.default_description', $siteName);
    $defaultImage = config('civic_atlas.seo.default_image_path', '/og/peshawar-civic-gis-atlas.svg');
    $defaultLocale = config('civic_atlas.seo.locale', 'en_PK');
    $currentPath = request()->path();
    $canonicalFallback = $currentPath === '/' ? $siteUrl : $siteUrl.'/'.ltrim($currentPath, '/');
    $rawTitle = trim((string) ($seo['title'] ?? ($title ?? $defaultTitle)));
    $fullTitle = $rawTitle === $siteName ? $rawTitle : trim($rawTitle.' | '.$siteName, ' |');
    $description = trim((string) ($seo['description'] ?? $defaultDescription));
    $canonical = (string) ($seo['canonical'] ?? $canonicalFallback);
    $image = (string) ($seo['image'] ?? $defaultImage);
    $type = (string) ($seo['type'] ?? 'website');
    $jsonLd = $seo['json_ld'] ?? null;
    $noindexRoutes = ['admin.*', 'dashboard', 'login', 'register', 'password.*', 'verification.*', 'profile.*'];
    $robots = (string) ($seo['robots'] ?? (request()->routeIs($noindexRoutes) ? 'noindex, nofollow, noarchive' : 'index, follow, max-image-preview:large'));
    $twitterCard = (string) config('civic_atlas.seo.twitter_card', 'summary_large_image');

    if (! str_starts_with($canonical, 'http')) {
        $canonical = $siteUrl.'/'.ltrim($canonical, '/');
    }

    if (! str_starts_with($image, 'http')) {
        $image = $siteUrl.'/'.ltrim($image, '/');
    }
@endphp
<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:locale" content="{{ $defaultLocale }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:alt" content="{{ $rawTitle ?: $siteName }}">
<meta name="twitter:card" content="{{ $twitterCard }}">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
@if ($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
