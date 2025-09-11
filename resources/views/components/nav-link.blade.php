@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-2 border-b-2 border-indigo-500 text-sm font-medium leading-5 text-white focus:outline-none focus:border-indigo-400 transition'
            : 'inline-flex items-center px-3 py-2 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-400 hover:text-white hover:border-gray-500 focus:outline-none focus:text-white focus:border-gray-500 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
