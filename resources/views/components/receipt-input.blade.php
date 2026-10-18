{{--
    Reusable proof-of-cost upload field. Drop inside any <form> that has a cost
    input. The form MUST declare enctype="multipart/form-data".

    <x-receipt-input />                         default label
    <x-receipt-input label="Proof of Payment" />
--}}
@props([
    'label' => 'Receipt / Proof of Cost',
    'help'  => 'Official Receipt, Sales Invoice, Delivery Receipt or Quotation. PDF/JPG/PNG/WEBP, up to 5 files, 5 MB each.',
    'showReference' => true,
])

@php $uid = 'rcpt_' . \Illuminate\Support\Str::random(6); @endphp

<div class="rounded-lg border border-dashed border-gray-300 bg-gray-50/60 p-3 space-y-2" x-data="{ files: [], tooBig: [], tooMany: false }">
    <label for="{{ $uid }}" class="block text-sm font-medium text-gray-700">
        <i class="fas fa-receipt text-gray-400 mr-1"></i>{{ $label }}
        <span class="text-xs font-normal text-gray-400">(optional)</span>
    </label>

    @if($showReference)
        <input type="text" name="receipt_reference_number" maxlength="100"
               value="{{ old('receipt_reference_number') }}"
               placeholder="OR / Invoice No. (e.g. OR-2026-00123)"
               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    @endif

    <input id="{{ $uid }}" type="file" name="receipts[]" multiple
           accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
           @change="
               files = Array.from($event.target.files);
               tooMany = files.length > 5;
               tooBig = files.filter(f => f.size > 5 * 1024 * 1024).map(f => f.name);
           "
           class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">

    <p class="text-xs text-gray-500">{{ $help }}</p>

    <template x-if="tooMany">
        <p class="text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>Please select at most 5 files.</p>
    </template>
    <template x-if="tooBig.length">
        <p class="text-xs text-red-600"><i class="fas fa-circle-exclamation mr-1"></i>Too large (max 5 MB): <span x-text="tooBig.join(', ')"></span></p>
    </template>
    <template x-if="files.length && !tooMany && !tooBig.length">
        <p class="text-xs text-green-700"><i class="fas fa-check mr-1"></i><span x-text="files.length"></span> file(s) ready to upload.</p>
    </template>

    @error('receipts') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    @error('receipts.*') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
    @error('receipt_reference_number') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
</div>
