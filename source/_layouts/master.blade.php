<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="x-ua-compatible" content="ie=edge">

        <title>{{ $page->siteName }}{{ $page->title ? ' | ' . $page->title : '' }}</title>
        <meta name="description" content="{{ $page->meta_description ?? $page->siteDescription }}">

        <link rel="home" href="{{ $page->baseUrl }}">
        <link rel="me" href="https://github.com/ralphschindler">
        <link rel="webmention" href="https://webmention.io/ralphschindler.dev/webmention" />
        <link rel="pingback" href="https://webmention.io/ralphschindler.dev/xmlrpc" />

        <link href="/articles/feed.atom" type="application/atom+xml" rel="alternate" title="{{ $page->siteName }} Atom Feed">

        @stack('meta')

        @if ($page->production)
            <script async src="https://www.googletagmanager.com/gtag/js?id=UA-143777600-1"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', 'UA-143777600-1');
            </script>
        @endif

        <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300,300i,400,400i,700,700i,800,800i" rel="stylesheet">
        <link rel="stylesheet" href="{{ mix('css/main.css', 'assets/build') }}">
    </head>

    <body class="flex flex-col justify-between min-h-screen text-gray-800 leading-normal font-sans">
        <header class="flex items-center shadow bg-white border-b h-24 py-4" role="banner">
            <div class="container flex items-center max-w-6xl mx-auto px-4 lg:px-8">
                <div class="flex items-center">
                    <a href="/" title="{{ $page->siteName }} home" class="inline-flex items-center">
                        <img class="block h-12 sm:h-16 rounded-full mx-auto mb-4 sm:mb-0 sm:mr-4 sm:ml-0" src="https://avatars2.githubusercontent.com/u/76674?v=4" alt="">
                        <div>
                            <h1 class="text-lg md:text-2xl text-red-800 font-semibold hover:text-red-600 my-0">{{ $page->siteName }}</h1>
                            <div class="text-purple-800 text-xs">
                                Principal Software Engineer at Ziff Davis / Ziff Media Group
                            </div>
                        </div>
                    </a>
                </div>

                <div id="vue-search" class="flex flex-1 justify-end items-center">
                    @include('_nav.menu')

                    @include('_nav.menu-toggle')
                </div>
            </div>
        </header>

        @include('_nav.menu-responsive')

        <main role="main" class="flex-auto w-full container max-w-5xl mx-auto py-16 px-6">
            @yield('body')
        </main>

        <footer class="bg-white text-center text-sm mt-12 py-4" role="contentinfo">
            <ul class="flex flex-col md:flex-row justify-center list-reset">
                <li class="md:mr-2">
                    &copy; <a href="https://ralphschindler.dev" title="Ralph Schindler">Ralph Schindler</a> {{ date('Y') }}.
                </li>

                <li>
                    Built with <a href="http://jigsaw.tighten.co" title="Jigsaw by Tighten">Jigsaw</a>,
                    <a href="https://tailwindcss.com" title="Tailwind CSS, a utility-first CSS framework">Tailwind CSS</a>,
                    and <a href="https://laravel.com" title="Laravel">Laravel</a>.
                </li>
            </ul>
        </footer>

        <script src="{{ mix('js/main.js', 'assets/build') }}"></script>

        @stack('scripts')
    </body>
</html>
