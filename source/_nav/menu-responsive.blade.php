<nav id="js-nav-menu" class="nav-menu hidden lg:hidden">
    <ul class="list-reset my-0">
        <li class="pl-4">
            <a
                title="{{ $page->siteName }} Articles"
                href="/articles"
                class="nav-menu__item hover:text-blue {{ $page->isActive('/articles') ? 'active text-blue' : '' }}"
            >Articles</a>
        </li>
{{--        <li class="pl-4">--}}
{{--            <a--}}
{{--                title="{{ $page->siteName }} About"--}}
{{--                href="/about"--}}
{{--                class="nav-menu__item hover:text-blue {{ $page->isActive('/about') ? 'active text-blue' : '' }}"--}}
{{--            >About</a>--}}
{{--        </li>--}}
{{--        <li class="pl-4">--}}
{{--            <a--}}
{{--                title="{{ $page->siteName }} Contact"--}}
{{--                href="/contact"--}}
{{--                class="nav-menu__item hover:text-blue {{ $page->isActive('/contact') ? 'active text-blue' : '' }}"--}}
{{--            >Contact</a>--}}
{{--        </li>--}}
    </ul>
</nav>
