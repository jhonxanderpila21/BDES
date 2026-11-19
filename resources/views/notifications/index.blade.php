@extends('layouts.admin')

@section('title', 'Notifications & Updates - BDES')

@section('content')
    <x-page-header
        title="Notifications & Equipment Updates"
        icon="fas fa-bell"
        :breadcrumbs="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Notifications & Updates']
        ]"
    >
        <x-slot:actions>
            @if(($counts['unread'] ?? 0) > 0)
                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-xs sm:text-sm shadow-xs"
                    >
                        <i class="fas fa-check-double text-blue-600"></i>
                        <span>Mark All as Read ({{ $counts['unread'] }})</span>
                    </button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-kpi-card
            title="Unread Alerts"
            :value="$counts['unread'] ?? 0"
            icon="fas fa-bell"
            color="rose"
            subtitle="Awaiting your review"
        />

        <x-kpi-card
            title="New Equipment"
            :value="$counts['equipment'] ?? 0"
            icon="fas fa-box-open"
            color="emerald"
            subtitle="Recently added to inventory"
        />

        <x-kpi-card
            title="Maintenance & Inspections"
            :value="($counts['maintenance'] ?? 0) + ($counts['inspection'] ?? 0)"
            icon="fas fa-screwdriver-wrench"
            color="amber"
            subtitle="Service & inspection updates"
        />

        <x-kpi-card
            title="Deployments & Reports"
            :value="($counts['deployment'] ?? 0) + ($counts['reported_asset'] ?? 0)"
            icon="fas fa-truck-fast"
            color="blue"
            subtitle="Field operations & incidents"
        />
    </div>

    {{-- Category Filter Tabs --}}
    @php
        $tabs = [
            'all'            => ['label' => 'All Notifications', 'icon' => 'fas fa-layer-group', 'count' => $counts['all'] ?? 0],
            'unread'         => ['label' => 'Unread',            'icon' => 'fas fa-envelope',    'count' => $counts['unread'] ?? 0],
            'equipment'      => ['label' => 'New Equipment',     'icon' => 'fas fa-box-open',    'count' => $counts['equipment'] ?? 0],
            'deployment'     => ['label' => 'Deployments',       'icon' => 'fas fa-truck-fast',  'count' => $counts['deployment'] ?? 0],
            'maintenance'    => ['label' => 'Maintenance',       'icon' => 'fas fa-wrench',      'count' => $counts['maintenance'] ?? 0],
            'reported_asset' => ['label' => 'Reported Assets',   'icon' => 'fas fa-flag',        'count' => $counts['reported_asset'] ?? 0],
        ];
    @endphp
    <div class="flex flex-wrap items-center gap-2 mb-5">
        @foreach($tabs as $key => $tab)
            @php
                $active = $activeCategory === $key;
            @endphp
            <a
                href="{{ route('notifications.index', ['category' => $key]) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all border {{ $active ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}"
            >
                <i class="{{ $tab['icon'] }} {{ $active ? 'text-white' : 'text-blue-600' }}"></i>
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $active ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Notifications Feed Card --}}
    <x-card>
        <div class="divide-y divide-gray-200">
            @forelse($notifications as $item)
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-colors {{ $item['is_read'] ? 'bg-white hover:bg-gray-50/70' : 'bg-blue-100/75 hover:bg-blue-100 border-l-4 border-l-blue-600' }}">
                    <div class="flex items-start gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-sm {{ $item['color'] ?? 'bg-blue-50 text-blue-600' }}">
                            <i class="{{ $item['icon'] ?? 'fas fa-bell' }}"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('notifications.view-record', $item['id']) }}" class="text-sm {{ $item['is_read'] ? 'font-semibold text-gray-800' : 'font-extrabold text-gray-900' }} hover:text-blue-600 transition-colors">
                                    {{ $item['title'] }}
                                </a>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ ($item['priority'] ?? '') === 'high' ? 'bg-rose-100 text-rose-700' : (($item['category'] ?? '') === 'equipment' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ $item['badge'] ?? $item['category_label'] ?? 'Update' }}
                                </span>
                                @if(!$item['is_read'])
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span> UNREAD
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs sm:text-sm {{ $item['is_read'] ? 'text-gray-500' : 'text-gray-800 font-medium' }} mt-1 leading-relaxed">
                                {{ $item['message'] ?? $item['description'] }}
                            </p>

                            <div class="flex items-center gap-3 mt-2 text-[11px] {{ $item['is_read'] ? 'text-gray-400' : 'text-gray-600 font-medium' }}">
                                <span><i class="far fa-clock mr-1"></i>{{ $item['timestamp'] }}</span>
                                <span>&bull;</span>
                                <span>{{ $item['category_label'] ?? ucfirst($item['category'] ?? 'General') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-center flex-shrink-0">
                        @if(!$item['is_read'])
                            <form action="{{ route('notifications.mark-read') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item['id'] }}">
                                <button
                                    type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-medium text-gray-700 transition-colors"
                                    title="Mark as read"
                                >
                                    <i class="fas fa-check text-blue-600 text-[11px]"></i>
                                    <span>Mark Read</span>
                                </button>
                            </form>
                        @endif

                        <a
                            href="{{ route('notifications.view-record', $item['id']) }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold transition-colors shadow-xs"
                        >
                            <span>View Details</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-12 px-4 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3 text-lg">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 class="text-sm font-bold text-gray-800">No notifications found</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        There are currently no notifications or equipment alerts in this category.
                    </p>
                </div>
            @endforelse
        </div>
    </x-card>
@endsection

