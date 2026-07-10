@php
    $ssOptions = collect($options)->values()->all();
    $ssOnChange = $onChange ?? null;
@endphp
<div class="search-select" x-data="{
        open: false,
        query: '',
        options: {{ \Illuminate\Support\Js::from($ssOptions) }},
        current: {{ \Illuminate\Support\Js::from($current ?? '') }},
        get filtered() {
            if (!this.query.trim()) return this.options;
            const q = this.query.toLowerCase();
            return this.options.filter(o => o.label.toLowerCase().includes(q) || (o.sub && o.sub.toLowerCase().includes(q)));
        },
        get selected() {
            return this.options.find(o => o.value === this.current);
        },
        choose(opt) {
            this.current = opt.value;
            this.open = false;
            this.query = '';
            @if($ssOnChange)
                $wire.set({{ \Illuminate\Support\Js::from($model) }}, opt.value).then(() => $wire.call({{ \Illuminate\Support\Js::from($ssOnChange) }}));
            @else
                $wire.set({{ \Illuminate\Support\Js::from($model) }}, opt.value);
            @endif
        }
    }" @click.outside="open = false">
    <button type="button" class="search-select-trigger" @click="open = !open; $nextTick(() => open && $refs.ssSearchInput?.focus())">
        <span :class="{'search-select-placeholder': !selected}" x-text="selected ? selected.label : {{ \Illuminate\Support\Js::from($placeholder ?? 'Select...') }}"></span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" :style="open ? 'transform:rotate(180deg)' : ''"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="search-select-panel" x-show="open" x-cloak x-transition.opacity.duration.120ms @keydown.escape.window="open = false">
        <div class="search-select-search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <input type="text" x-model="query" x-ref="ssSearchInput" placeholder="Search...">
        </div>
        <div class="search-select-list">
            <template x-for="opt in filtered" :key="opt.value">
                <div class="search-select-option" :class="{active: opt.value === current}" @click="choose(opt)">
                    <span x-text="opt.label"></span>
                    <template x-if="opt.sub"><span class="search-select-sub" x-text="opt.sub"></span></template>
                </div>
            </template>
            <div class="search-select-empty" x-show="filtered.length === 0">No matches found</div>
        </div>
    </div>
</div>
