<div class="flex border-b border-gray-200 mb-6 space-x-1 overflow-x-auto">
    <a 
        href="{{ route('financials.expenses') }}" 
        class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('financials.expenses*') ? 'border-emerald-600 text-emerald-700 bg-emerald-50/50 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
    >
        <i class="fas fa-file-invoice-dollar {{ request()->routeIs('financials.expenses*') ? 'text-emerald-600' : 'text-gray-400' }}"></i>
        <span>Expenses Ledger & Outlay</span>
    </a>
    <a 
        href="{{ route('financials.depreciation') }}" 
        class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('financials.depreciation*') ? 'border-blue-600 text-blue-700 bg-blue-50/50 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
    >
        <i class="fas fa-arrow-trend-down {{ request()->routeIs('financials.depreciation*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
        <span>Depreciation Schedule</span>
    </a>
    <a 
        href="{{ route('financials.salvage') }}" 
        class="flex items-center gap-2 py-3 px-5 text-sm font-semibold border-b-2 transition-colors whitespace-nowrap {{ request()->routeIs('financials.salvage*') ? 'border-indigo-600 text-indigo-700 bg-indigo-50/50 rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
    >
        <i class="fas fa-recycle {{ request()->routeIs('financials.salvage*') ? 'text-indigo-600' : 'text-gray-400' }}"></i>
        <span>Salvage Value Records</span>
    </a>
</div>

