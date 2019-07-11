@extends('_layouts.master')

@section('body')
    @foreach ($articles->where('featured', true) as $featuredArticle)
        <div class="w-full mb-6">
            @if ($featuredArticle->cover_image)
                <img src="{{ $featuredArticle->cover_image }}" alt="{{ $featuredArticle->title }} cover image" class="mb-6">
            @endif

            <p class="text-gray-700 font-medium my-2">
                {{ $featuredArticle->getDate()->format('F j, Y') }}
            </p>

            <h2 class="text-3xl mt-0">
                <a href="{{ $featuredArticle->getUrl() }}" title="Read {{ $featuredArticle->title }}" class="text-black font-extrabold">
                    {{ $featuredArticle->title }}
                </a>
            </h2>

            <p class="mt-0 mb-4">{!! $featuredArticle->getExcerpt() !!}</p>

            <a href="{{ $featuredArticle->getUrl() }}" title="Read - {{ $featuredArticle->title }}" class="uppercase tracking-wide mb-4">
                Read
            </a>
        </div>

        @if (! $loop->last)
            <hr class="border-b my-6">
        @endif
    @endforeach

    @foreach ($articles->where('featured', false)->take(6)->chunk(2) as $row)
        <div class="flex flex-col md:flex-row md:-mx-6">
            @foreach ($row as $article)
                <div class="w-full md:w-1/2 md:mx-6">
                    @include('_components.article-preview-inline', compact('article'))
                </div>

                @if (! $loop->last)
                    <hr class="block md:hidden w-full border-b mt-2 mb-6">
                @endif
            @endforeach
        </div>

        @if (! $loop->last)
            <hr class="w-full border-b mt-2 mb-6">
        @endif
    @endforeach
@stop
