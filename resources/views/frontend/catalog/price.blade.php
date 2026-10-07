@if(! empty($item['display_price']))
    <span class="font-extrabold text-slate-950 dark:text-white">{{ $item['display_price'] }}</span>
@elseif(! empty($item['price_label']))
    <span class="font-extrabold text-slate-950 dark:text-white">{{ $item['price_label'] }}</span>
@endif
@if(! empty($item['compare_at_price']))
    <span class="text-xs font-bold text-slate-400 line-through">{{ $item['compare_at_price'] }}</span>
@endif
@unless($item['is_available'])
    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-extrabold text-amber-900 dark:bg-amber-400/15 dark:text-amber-200">Currently unavailable</span>
@endunless
