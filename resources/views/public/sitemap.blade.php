{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>always</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('news.index') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('breaking-news') }}</loc>
        <changefreq>always</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('local-news') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>0.8</priority>
    </url>
    @foreach ($categories as $category)
    <url>
        <loc>{{ route('category.show', $category->slug) }}</loc>
        <changefreq>hourly</changefreq>
        <priority>0.8</priority>
    </url>
    @endforeach
    @foreach ($news as $item)
    <url>
        <loc>{{ route('news.show', $item->slug) }}</loc>
        <lastmod>{{ ($item->published_at ?: $item->updated_at)?->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.7</priority>
        <news:news>
            <news:publication>
                <news:name>Jankatha.com</news:name>
                <news:language>hi</news:language>
            </news:publication>
            <news:publication_date>{{ ($item->published_at ?: $item->created_at)?->toAtomString() }}</news:publication_date>
            <news:title><![CDATA[{{ $item->headline }}]]></news:title>
        </news:news>
    </url>
    @endforeach
</urlset>
