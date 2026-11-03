@props([
    'placeholder' => 'Search...',
    'class' => '',
    'autocomplete' => true,
])

<div class="relative {{ $class }}" x-data="{
    open: false,
    suggestions: [],
    loading: false,
    fetchSuggestions(val) {
        if (!val || val.trim().length < 1) {
            this.suggestions = [];
            this.open = false;
            return;
        }
        this.loading = true;
        fetch('{{ route('search.autocomplete') }}?q=' + encodeURIComponent(val.trim()), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            this.suggestions = (data.suggestions || []).slice(0, 5);
            this.open = this.suggestions.length > 0;
            this.loading = false;
        })
        .catch(() => { this.loading = false; });
    }
}" @click.outside="open = false">
    <input 
        type="text" 
        placeholder="{{ $placeholder }}"
        class="w-full px-4 py-2 pl-10 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors text-sm"
        @input.debounce.250ms="fetchSuggestions($event.target.value)"
        @focus="if (suggestions.length > 0) open = true"
        @keydown.escape="open = false"
        {{ $attributes }}
    >
    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>

    {{-- Autocomplete Dropdown with at least 5 suggestions --}}
    <div 
        x-show="open && suggestions.length > 0" 
        x-cloak 
        class="absolute left-0 right-0 mt-1.5 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden divide-y divide-gray-100 text-left text-sm"
    >
        <template x-for="item in suggestions" :key="item.id">
            <div 
                @click="window.location.href = item.url" 
                class="p-2.5 hover:bg-blue-50/70 cursor-pointer flex items-center justify-between transition-colors group"
            >
                <div class="min-w-0 flex-1 pr-2">
                    <p class="font-semibold text-xs text-gray-900 truncate group-hover:text-blue-700" x-text="item.title"></p>
                    <p class="text-[11px] text-gray-500 truncate" x-text="item.subtitle"></p>
                </div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded border shrink-0" :class="item.badge_color" x-text="item.category"></span>
            </div>
        </template>
    </div>
</div>
