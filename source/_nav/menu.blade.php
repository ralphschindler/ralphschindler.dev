<nav class="hidden lg:flex items-center justify-end text-lg">
    <a title="{{ $page->siteName }} Articles" href="/articles"
        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/articles') ? 'active text-blue-600' : '' }}">
        Articles
    </a>

{{--    <a title="{{ $page->siteName }} About" href="/about"--}}
{{--        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/about') ? 'active text-blue-600' : '' }}">--}}
{{--        About--}}
{{--    </a>--}}

{{--    <a title="{{ $page->siteName }} About" href="/laravel-tips"--}}
{{--        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/laravel-tips') ? 'active text-blue-600' : '' }}">--}}
{{--        Laravel Tips--}}
{{--    </a>--}}

{{--    <a title="{{ $page->siteName }} Contact" href="/contact"--}}
{{--        class="ml-6 text-gray-700 hover:text-blue-600 {{ $page->isActive('/contact') ? 'active text-blue-600' : '' }}">--}}
{{--        Contact--}}
{{--    </a>--}}
</nav>
