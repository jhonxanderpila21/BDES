@props([
    'headers' => [],
    'rows' => [],
    'class' => ''
])

<div class="overflow-x-auto {{ $class }}">
    <table class="w-full">
        <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
                @foreach($headers as $header)
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                        {{ $header }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($rows as $row)
                <tr class="hover:bg-gray-50 transition-colors">
                    {{ $row }}
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-3xl text-gray-300 mb-2 block"></i>
                        No data available
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
