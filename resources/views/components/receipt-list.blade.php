{{--
    Lists receipts attached to a model.  <x-receipt-list :receipts="$model->receipts" />
--}}
@props(['receipts' => collect(), 'compact' => false])

@if($receipts && $receipts->count())
    <ul class="{{ $compact ? 'flex flex-wrap gap-1.5' : 'space-y-1.5' }}">
        @foreach($receipts as $receipt)
            <li class="{{ $compact ? '' : 'flex items-center justify-between gap-2 text-sm' }}">
                <a href="{{ route('receipts.show', $receipt) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-medium"
                   title="{{ $receipt->file_name }} ({{ $receipt->formatted_size }})">
                    <i class="fas {{ $receipt->is_image ? 'fa-file-image' : 'fa-file-pdf' }}"></i>
                    {{ $receipt->reference_number ?: \Illuminate\Support\Str::limit($receipt->file_name, 24) }}
                </a>
                @if(! $compact && (auth()->user()->isAdmin() || (int) $receipt->uploaded_by === (int) auth()->id()))
                    <form method="POST" action="{{ route('receipts.destroy', $receipt) }}" class="inline" onsubmit="return confirmSecureDelete(event, this, 'Receipt: {{ addslashes($receipt->file_name) }}', 'This attached receipt/document will be permanently deleted.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs" title="Remove receipt">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
@else
    <span class="text-xs text-gray-400 italic">No receipt</span>
@endif

