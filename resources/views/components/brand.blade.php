@props(['href' => '/'])
<a {{ $attributes->class(['ct-brand'])->merge(['href' => $href, 'aria-label' => 'ChemTrack home']) }}>
    <x-brand-mark /><span class="ct-brand-word">Chem<span>Track</span></span>
</a>
