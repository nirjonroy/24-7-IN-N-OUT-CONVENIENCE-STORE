@if($icon === 'phone')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="6.5" y="2.5" width="11" height="19" rx="2.5"/><path stroke-linecap="round" d="M10 5h4M11 18.5h2"/></svg>
@elseif($icon === 'smoothie')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 5h10l-1.2 14H8.2L7 5Z"/><path stroke-linecap="round" d="M10 2l4 3M9 10c2-2 4 2 6 0"/></svg>
@elseif($icon === 'shield')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5.5 5.5v5.2c0 4.4 2.8 8.3 6.5 10.3 3.7-2 6.5-5.9 6.5-10.3V5.5L12 3Z"/><path stroke-linecap="round" d="m9.5 12 1.7 1.7 3.5-4"/></svg>
@elseif($icon === 'pin')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-11a7 7 0 1 0-14 0c0 6.65 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
@elseif($icon === 'check')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/></svg>
@elseif($icon === 'external')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 4h6v6M20 4l-9 9"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg>
@elseif($icon === 'star')
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 2.7 5.47 6.04.88-4.37 4.26 1.03 6.02L12 16.8l-5.4 2.83 1.03-6.02-4.37-4.26 6.04-.88L12 3Z"/></svg>
@else
    <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8h12l-1 12H7L6 8Z"/><path stroke-linecap="round" d="M9 8a3 3 0 0 1 6 0"/></svg>
@endif
