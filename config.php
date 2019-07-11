<?php

return [
    'baseUrl' => '',
    'production' => false,
    'siteName' => 'Ralph Schindler',
    'siteDescription' => '',
    'siteAuthor' => 'Ralph Schindler',

    // collections
    'collections' => [
        'articles' => [
            'author' => 'Ralph Schindler', // Default author, if not provided in a post
            'sort' => '-date',
            'path' => 'article/{filename}',
        ],
        // 'laravel-tips' => [
        //     'path' => 'laravel-tips/{filename}'
        // ]
    ],

    // helpers
    'getDate' => function ($page) {
        return Datetime::createFromFormat('U', $page->date);
    },
    'getExcerpt' => function ($page, $length = 255) {
        $content = $page->excerpt ?? $page->getContent();
        $cleaned = strip_tags(
            preg_replace(['/<pre>[\w\W]*?<\/pre>/', '/<h\d>[\w\W]*?<\/h\d>/'], '', $content),
            '<code>'
        );

        $truncated = substr($cleaned, 0, $length);

        if (substr_count($truncated, '<code>') > substr_count($truncated, '</code>')) {
            $truncated .= '</code>';
        }

        return strlen($cleaned) > $length
            ? preg_replace('/\s+?(\S+)?$/', '', $truncated) . '...'
            : $cleaned;
    },
    'isActive' => function ($page, $path) {
        return Illuminate\Support\Str::endsWith(trimPath($page->getPath()), trimPath($path));
    },
];
