<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetAccessory;
use App\Models\Attachment;
use App\Models\Maintenance;
use App\Models\OperationalRequirement;
use App\Models\OperationalRequirementRestock;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ExpenseLedgerService
{
    /**
     * Compile all expense sources into a unified, filterable collection.
     */
    public static function getLedger(array $filters = []): Collection
    {
        $items = collect();

        // 1. Capital Assets (acquisition_cost > 0)
        try {
            $assets = Asset::with(['category', 'receipts'])
                ->whereNotNull('acquisition_cost')
                ->where('acquisition_cost', '>', 0)
                ->get();

            foreach ($assets as $asset) {
                $date = $asset->purchase_date ? Carbon::parse($asset->purchase_date) : $asset->created_at;
                $items->push((object)[
                    'id'             => 'asset-' . $asset->id,
                    'type'           => 'asset',
                    'category_label' => 'Capital Assets',
                    'item_name'      => $asset->name,
                    'reference'      => $asset->code,
                    'date'           => $date,
                    'formatted_date' => $date ? $date->format('M d, Y') : 'N/A',
                    'quantity'       => 1,
                    'unit_cost'      => (float) $asset->acquisition_cost,
                    'total_cost'     => (float) $asset->acquisition_cost,
                    'recorded_by'    => 'Inventory System',
                    'notes'          => $asset->notes ?: ($asset->category?->name ?? 'Disaster Asset'),
                    'receipts'       => $asset->receipts ?? collect(),
                    'receipts_count' => ($asset->receipts ?? collect())->count(),
                    'route'          => route('assets.show', $asset->id),
                ]);
            }
        } catch (\Throwable $e) {}

        // 2. Accessories (AssetAccessory with unit_cost > 0)
        try {
            if (Schema::hasTable('asset_accessories')) {
                $accessories = AssetAccessory::with(['asset', 'receipts'])
                    ->whereNotNull('unit_cost')
                    ->where('unit_cost', '>', 0)
                    ->get();

                foreach ($accessories as $acc) {
                    $qty = max(1, (int) ($acc->quantity ?? 1));
                    $unitCost = (float) $acc->unit_cost;
                    $total = $unitCost * $qty;
                    $date = $acc->created_at;

                    $items->push((object)[
                        'id'             => 'accessory-' . $acc->id,
                        'type'           => 'accessory',
                        'category_label' => 'Accessories',
                        'item_name'      => $acc->name,
                        'reference'      => 'ACC-' . str_pad($acc->id, 3, '0', STR_PAD_LEFT) . ($acc->asset ? ' (' . $acc->asset->code . ')' : ''),
                        'date'           => $date,
                        'formatted_date' => $date ? $date->format('M d, Y') : 'N/A',
                        'quantity'       => $qty,
                        'unit_cost'      => $unitCost,
                        'total_cost'     => $total,
                        'recorded_by'    => 'Inventory Officer',
                        'notes'          => 'Dedicated accessory for ' . ($acc->asset->name ?? 'general equipment'),
                        'receipts'       => $acc->receipts ?? collect(),
                        'receipts_count' => ($acc->receipts ?? collect())->count(),
                        'route'          => route('asset-accessories.index', ['search' => $acc->name]),
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        // 3. Attachments (Attachment with unit_cost > 0)
        try {
            if (Schema::hasTable('attachments') && Schema::hasColumn('attachments', 'unit_cost')) {
                $attachments = Attachment::with(['asset', 'receipts'])
                    ->whereNotNull('unit_cost')
                    ->where('unit_cost', '>', 0)
                    ->get();

                foreach ($attachments as $att) {
                    $qty = 1;
                    $unitCost = (float) $att->unit_cost;
                    $total = $unitCost * $qty;
                    $date = $att->created_at;

                    $items->push((object)[
                        'id'             => 'attachment-' . $att->id,
                        'type'           => 'attachment',
                        'category_label' => 'Attachments',
                        'item_name'      => $att->name,
                        'reference'      => 'ATT-' . str_pad($att->id, 3, '0', STR_PAD_LEFT) . ($att->asset ? ' (' . $att->asset->code . ')' : ''),
                        'date'           => $date,
                        'formatted_date' => $date ? $date->format('M d, Y') : 'N/A',
                        'quantity'       => $qty,
                        'unit_cost'      => $unitCost,
                        'total_cost'     => $total,
                        'recorded_by'    => 'Inventory Officer',
                        'notes'          => $att->description ?: 'Equipment attachment',
                        'receipts'       => $att->receipts ?? collect(),
                        'receipts_count' => ($att->receipts ?? collect())->count(),
                        'route'          => route('attachments.index', ['search' => $att->name]),
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        // 4. Operational Requirements Restocks (operational_requirement_restocks)
        try {
            if (Schema::hasTable('operational_requirement_restocks')) {
                $restocks = OperationalRequirementRestock::with(['requirement', 'recordedBy', 'receipts'])->get();

                foreach ($restocks as $restock) {
                    $qty = max(1, (int) $restock->quantity);
                    $unitCost = (float) $restock->unit_cost;
                    $total = (float) ($restock->total_cost > 0 ? $restock->total_cost : ($unitCost * $qty));
                    if ($total <= 0 && $unitCost <= 0) continue;

                    $date = $restock->restocked_at ? Carbon::parse($restock->restocked_at) : $restock->created_at;

                    $items->push((object)[
                        'id'             => 'restock-' . $restock->id,
                        'type'           => 'operational_requirement',
                        'category_label' => 'Operational Supplies',
                        'item_name'      => $restock->requirement?->requirement_type ?? ($restock->requirement?->name ?? 'Supply Refill'),
                        'reference'      => 'REF-' . str_pad($restock->id, 4, '0', STR_PAD_LEFT),
                        'date'           => $date,
                        'formatted_date' => $date ? $date->format('M d, Y') : 'N/A',
                        'quantity'       => $qty,
                        'unit_cost'      => $unitCost > 0 ? $unitCost : ($total / $qty),
                        'total_cost'     => $total,
                        'recorded_by'    => $restock->recordedBy?->name ?? 'Supplies Custodian',
                        'notes'          => $restock->notes ?: ($restock->supplier ? 'Supplier: ' . $restock->supplier : 'Restock shipment'),
                        'receipts'       => $restock->receipts ?? collect(),
                        'receipts_count' => ($restock->receipts ?? collect())->count(),
                        'route'          => route('operational-requirements.index'),
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        // 5. Maintenance & Repairs (maintenances where cost > 0)
        try {
            $maintenances = Maintenance::with(['asset', 'assignedUser', 'receipts'])
                ->whereNotNull('cost')
                ->where('cost', '>', 0)
                ->get();

            foreach ($maintenances as $m) {
                $cost = (float) $m->cost;
                $date = $m->completed_at ? Carbon::parse($m->completed_at) : ($m->scheduled_date ? Carbon::parse($m->scheduled_date) : $m->created_at);

                $items->push((object)[
                    'id'             => 'maintenance-' . $m->id,
                    'type'           => 'maintenance',
                    'category_label' => 'Maintenance & Repairs',
                    'item_name'      => ($m->asset ? $m->asset->name . ' - ' : '') . ($m->work_order_number ?: (ucfirst($m->type) . ' Maintenance')),
                    'reference'      => $m->work_order_number ?: ('WO-' . str_pad($m->id, 4, '0', STR_PAD_LEFT) . ($m->asset ? ' (' . $m->asset->code . ')' : '')),
                    'date'           => $date,
                    'formatted_date' => $date ? $date->format('M d, Y') : 'N/A',
                    'quantity'       => 1,
                    'unit_cost'      => $cost,
                    'total_cost'     => $cost,
                    'recorded_by'    => $m->technician ?: ($m->assignedUser?->name ?? 'Maintenance Officer'),
                    'notes'          => $m->notes ?: ($m->parts_used ? 'Parts: ' . $m->parts_used : 'Servicing work order'),
                    'receipts'       => $m->receipts ?? collect(),
                    'receipts_count' => ($m->receipts ?? collect())->count(),
                    'route'          => route('maintenance.index', ['search' => $m->work_order_number ?: ($m->asset?->name ?? '')]),
                ]);
            }
        } catch (\Throwable $e) {}

        // Apply filters
        return self::applyFilters($items, $filters);
    }

    /**
     * Filter and sort ledger collection safely.
     */
    protected static function applyFilters(Collection $items, array $filters): Collection
    {
        // 1. Type / Category Filter
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $target = strtolower($filters['category']);
            $items = $items->filter(function ($item) use ($target) {
                return strtolower($item->type) === $target || strtolower($item->category_label) === $target;
            });
        }

        // 2. Search Filter
        if (!empty($filters['search'])) {
            $search = strtolower(trim($filters['search']));
            $items = $items->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->item_name), $search)
                    || str_contains(strtolower($item->reference), $search)
                    || str_contains(strtolower($item->category_label), $search)
                    || str_contains(strtolower((string)$item->notes), $search)
                    || str_contains(strtolower((string)$item->recorded_by), $search);
            });
        }

        // 3. Date Range Filter
        $dateFrom = !empty($filters['date_from']) ? Carbon::tryParse($filters['date_from']) : null;
        $dateTo = !empty($filters['date_to']) ? Carbon::tryParse($filters['date_to']) : null;

        // Auto-fix inverted dates
        if ($dateFrom && $dateTo && $dateFrom->gt($dateTo)) {
            $temp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $temp;
        }

        if ($dateFrom) {
            $dateFromStart = $dateFrom->copy()->startOfDay();
            $items = $items->filter(function ($item) use ($dateFromStart) {
                return $item->date && $item->date->gte($dateFromStart);
            });
        }

        if ($dateTo) {
            $dateToEnd = $dateTo->copy()->endOfDay();
            $items = $items->filter(function ($item) use ($dateToEnd) {
                return $item->date && $item->date->lte($dateToEnd);
            });
        }

        // 4. Min / Max Amount Filter
        if (isset($filters['min_amount']) && is_numeric($filters['min_amount']) && $filters['min_amount'] >= 0) {
            $min = (float) $filters['min_amount'];
            $items = $items->filter(fn ($item) => $item->total_cost >= $min);
        }

        if (isset($filters['max_amount']) && is_numeric($filters['max_amount']) && $filters['max_amount'] > 0) {
            $max = (float) $filters['max_amount'];
            $items = $items->filter(fn ($item) => $item->total_cost <= $max);
        }

        // 5. Receipts Only Filter
        if (!empty($filters['has_receipts'])) {
            $items = $items->filter(fn ($item) => $item->receipts_count > 0);
        }

        // 6. Sorting
        $sortDir = strtolower($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $sortBy = $filters['sort_by'] ?? 'date';

        if ($sortBy === 'total_cost') {
            $items = $sortDir === 'asc'
                ? $items->sortBy('total_cost')
                : $items->sortByDesc('total_cost');
        } elseif ($sortBy === 'item_name') {
            $items = $sortDir === 'asc'
                ? $items->sortBy('item_name', SORT_NATURAL | SORT_FLAG_CASE)
                : $items->sortByDesc('item_name', SORT_NATURAL | SORT_FLAG_CASE);
        } else {
            $items = $sortDir === 'asc'
                ? $items->sortBy(fn ($item) => $item->date ? $item->date->timestamp : 0)
                : $items->sortByDesc(fn ($item) => $item->date ? $item->date->timestamp : 0);
        }

        return $items->values();
    }

    /**
     * Compute high-level summary KPIs for the expenses ledger.
     */
    public static function getSummaryKpis(Collection $ledger): array
    {
        $totalExpenses = $ledger->sum('total_cost');

        $byCategory = [
            'assets'       => $ledger->where('type', 'asset')->sum('total_cost'),
            'maintenance'  => $ledger->where('type', 'maintenance')->sum('total_cost'),
            'supplies'     => $ledger->where('type', 'operational_requirement')->sum('total_cost'),
            'accessories'  => $ledger->where('type', 'accessory')->sum('total_cost') + $ledger->where('type', 'attachment')->sum('total_cost'),
        ];

        $withReceipts = $ledger->where('receipts_count', '>', 0)->count();
        $totalCount = $ledger->count();

        return [
            'total_expenses'         => $totalExpenses,
            'assets_total'           => $byCategory['assets'],
            'maintenance_total'      => $byCategory['maintenance'],
            'supplies_total'         => $byCategory['supplies'],
            'accessories_total'      => $byCategory['accessories'],
            'total_count'            => $totalCount,
            'receipts_attached_pct'  => $totalCount > 0 ? round(($withReceipts / $totalCount) * 100) : 0,
            'with_receipts_count'    => $withReceipts,
            'without_receipts_count' => $totalCount - $withReceipts,
        ];
    }

    /**
     * Paginate the ledger collection for presentation.
     */
    public static function paginate(Collection $items, int $perPage = 15, int $page = 1, array $options = []): LengthAwarePaginator
    {
        $offset = ($page - 1) * $perPage;
        $sliced = $items->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $sliced,
            $items->count(),
            $perPage,
            $page,
            $options
        );
    }
}

