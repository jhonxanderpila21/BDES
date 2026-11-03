<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Attachment;
use App\Models\AuditLog;
use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Location;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\OperationalRequirement;
use App\Models\ReportedAsset;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ArchiveService
{
    /**
     * Supported modules registry.
     */
    public static function registry(): array
    {
        return [
            'assets' => [
                'key'         => 'assets',
                'label'       => 'Assets Registry',
                'model'       => Asset::class,
                'name_field'  => 'name',
                'code_field'  => 'code',
                'icon'        => 'fas fa-boxes-stacked',
                'color'       => 'blue',
                'with'        => ['category', 'type', 'location', 'deletedByUser'],
            ],
            'attachments' => [
                'key'         => 'attachments',
                'label'       => 'Components & Parts',
                'model'       => Attachment::class,
                'name_field'  => 'name',
                'code_field'  => 'id',
                'icon'        => 'fas fa-puzzle-piece',
                'color'       => 'indigo',
                'with'        => ['asset', 'deletedByUser'],
            ],
            'operational_requirements' => [
                'key'         => 'operational_requirements',
                'label'       => 'Operational Requirements',
                'model'       => OperationalRequirement::class,
                'name_field'  => 'requirement_type',
                'code_field'  => 'id',
                'icon'        => 'fas fa-list-check',
                'color'       => 'amber',
                'with'        => ['assetType', 'deletedByUser'],
            ],
            'maintenances' => [
                'key'         => 'maintenances',
                'label'       => 'Maintenance & Work Orders',
                'model'       => Maintenance::class,
                'name_field'  => 'type',
                'code_field'  => 'work_order_number',
                'icon'        => 'fas fa-screwdriver-wrench',
                'color'       => 'purple',
                'with'        => ['asset', 'deletedByUser'],
            ],
            'inspections' => [
                'key'         => 'inspections',
                'label'       => 'Inspections',
                'model'       => Inspection::class,
                'name_field'  => 'findings',
                'code_field'  => 'id',
                'icon'        => 'fas fa-clipboard-check',
                'color'       => 'teal',
                'with'        => ['asset', 'deletedByUser'],
            ],
            'maintenance_schedules' => [
                'key'         => 'maintenance_schedules',
                'label'       => 'Maintenance Schedules',
                'model'       => MaintenanceSchedule::class,
                'name_field'  => 'maintenance_task',
                'code_field'  => 'id',
                'icon'        => 'fas fa-calendar-check',
                'color'       => 'violet',
                'with'        => ['asset', 'deletedByUser'],
            ],
            'deployments' => [
                'key'         => 'deployments',
                'label'       => 'Deployments',
                'model'       => Deployment::class,
                'name_field'  => 'destination',
                'code_field'  => 'id',
                'icon'        => 'fas fa-truck-ramp-box',
                'color'       => 'emerald',
                'with'        => ['asset', 'deletedByUser'],
            ],
            'reported_assets' => [
                'key'         => 'reported_assets',
                'label'       => 'Reported Assets',
                'model'       => ReportedAsset::class,
                'name_field'  => 'description',
                'code_field'  => 'id',
                'icon'        => 'fas fa-flag',
                'color'       => 'rose',
                'with'        => ['asset', 'deletedByUser'],
            ],
            'locations' => [
                'key'         => 'locations',
                'label'       => 'Locations',
                'model'       => Location::class,
                'name_field'  => 'name',
                'code_field'  => 'id',
                'icon'        => 'fas fa-location-dot',
                'color'       => 'cyan',
                'with'        => ['deletedByUser'],
            ],
            'asset_categories' => [
                'key'         => 'asset_categories',
                'label'       => 'Asset Categories',
                'model'       => AssetCategory::class,
                'name_field'  => 'name',
                'code_field'  => 'id',
                'icon'        => 'fas fa-tags',
                'color'       => 'slate',
                'with'        => ['deletedByUser'],
            ],
            'asset_types' => [
                'key'         => 'asset_types',
                'label'       => 'Asset Types',
                'model'       => AssetType::class,
                'name_field'  => 'name',
                'code_field'  => 'id',
                'icon'        => 'fas fa-layer-group',
                'color'       => 'fuchsia',
                'with'        => ['category', 'deletedByUser'],
            ],
            'users' => [
                'key'         => 'users',
                'label'       => 'Users',
                'model'       => User::class,
                'name_field'  => 'name',
                'code_field'  => 'username',
                'icon'        => 'fas fa-users-gear',
                'color'       => 'orange',
                'with'        => ['deletedByUser'],
            ],
        ];
    }

    /**
     * Compute summary metrics for the archive center.
     */
    public static function getMetrics(): array
    {
        $registry = static::registry();
        $total = 0;
        $thirtyDaysAgo = now()->subDays(30);
        $recent = 0;

        foreach ($registry as $conf) {
            $modelClass = $conf['model'];
            $count = $modelClass::onlyTrashed()->count();
            $total += $count;
            $recent += $modelClass::onlyTrashed()->where('deleted_at', '>=', $thirtyDaysAgo)->count();
        }

        return [
            'total_archived'      => $total,
            'archived_assets'     => Asset::onlyTrashed()->count(),
            'archived_work_orders'=> Maintenance::onlyTrashed()->count(),
            'archived_recent'     => $recent,
        ];
    }

    /**
     * Query archived records with unified formatting, filtering, and pagination.
     */
    public static function getArchivedRecords(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $registry = static::registry();
        $selectedModule = $filters['module'] ?? 'all';
        $search = trim($filters['search'] ?? '');
        $deletedBy = $filters['deleted_by'] ?? null;
        $dateFilter = $filters['date_filter'] ?? 'all';

        $startDate = null;
        if ($dateFilter === 'today') {
            $startDate = now()->startOfDay();
        } elseif ($dateFilter === '7days') {
            $startDate = now()->subDays(7)->startOfDay();
        } elseif ($dateFilter === '30days') {
            $startDate = now()->subDays(30)->startOfDay();
        }

        $allResults = collect();

        $modulesToQuery = ($selectedModule !== 'all' && isset($registry[$selectedModule]))
            ? [$selectedModule => $registry[$selectedModule]]
            : $registry;

        foreach ($modulesToQuery as $key => $conf) {
            $modelClass = $conf['model'];
            $query = $modelClass::onlyTrashed()->with($conf['with'] ?? []);

            if ($deletedBy) {
                $query->where('deleted_by', $deletedBy);
            }

            if ($startDate) {
                $query->where('deleted_at', '>=', $startDate);
            }

            if (!empty($filters['date_from'])) {
                $query->whereDate('deleted_at', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('deleted_at', '<=', $filters['date_to']);
            }

            if ($search !== '') {
                $nameField = $conf['name_field'];
                $codeField = $conf['code_field'];

                $query->where(function ($q) use ($search, $nameField, $codeField) {
                    $q->where($nameField, 'like', "%{$search}%")
                      ->orWhere('deletion_reason', 'like', "%{$search}%");

                    if ($codeField && $codeField !== 'id') {
                        $q->orWhere($codeField, 'like', "%{$search}%");
                    }
                    if (is_numeric($search)) {
                        $q->orWhere('id', (int) $search);
                    }
                });
            }

            $items = $query->latest('deleted_at')->get();

            foreach ($items as $item) {
                $rawId = $item->id;
                $formattedId = static::formatRecordId($conf, $item);
                $displayName = static::formatRecordName($conf, $item);

                $allResults->push((object) [
                    'id'               => "{$key}-{$rawId}",
                    'raw_id'           => $rawId,
                    'module_key'       => $key,
                    'module_label'     => $conf['label'],
                    'module_icon'      => $conf['icon'],
                    'module_color'     => $conf['color'],
                    'record_id'        => $formattedId,
                    'record_name'      => $displayName,
                    'deletion_reason'  => $item->deletion_reason ?? 'Archived via system management interface.',
                    'deleted_by_id'    => $item->deleted_by,
                    'deleted_by_name'  => $item->deletedByUser?->name ?? 'System / Administrator',
                    'deleted_at'       => $item->deleted_at ? Carbon::parse($item->deleted_at) : null,
                    'model'            => $item,
                ]);
            }
        }

        // Sort descending by deleted_at
        $sorted = $allResults->sortByDesc(function ($item) {
            return $item->deleted_at ? $item->deleted_at->timestamp : 0;
        })->values();

        $page = (int) request()->get('page', 1);
        $paginatedItems = $sorted->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $paginatedItems,
            $sorted->count(),
            $perPage,
            $page,
            [
                'path'     => route('archived-records.index'),
                'pageName' => 'page',
                'query'    => request()->query(),
            ]
        );
    }

    /**
     * Format display identifier for a record.
     */
    protected static function formatRecordId(array $conf, Model $item): string
    {
        $codeField = $conf['code_field'];
        if ($codeField && $codeField !== 'id' && !empty($item->{$codeField})) {
            return (string) $item->{$codeField};
        }

        return '#' . str_pad((string) $item->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Format human-readable title/name for a record.
     */
    protected static function formatRecordName(array $conf, Model $item): string
    {
        $nameField = $conf['name_field'];
        $val = $item->{$nameField} ?? null;

        if ($conf['key'] === 'maintenances') {
            $assetName = $item->asset?->name ?? 'Equipment';
            return ucfirst($item->type ?? 'Maintenance') . " - {$assetName}";
        }

        if ($conf['key'] === 'deployments') {
            $assetName = $item->asset?->name ?? 'Asset';
            return "Deployment to " . ($item->destination ?? 'Field') . " ({$assetName})";
        }

        if ($conf['key'] === 'inspections') {
            $assetName = $item->asset?->name ?? 'Asset';
            return "Inspection by " . ($item->inspector ?? 'Staff') . " ({$assetName})";
        }

        return $val ? (string) $val : "Record #{$item->id}";
    }

    /**
     * Safety check before permanent deletion.
     */
    public static function canPermanentlyDelete(string $moduleKey, Model $record): array
    {
        switch ($moduleKey) {
            case 'assets':
                if (method_exists($record, 'expenses') && $record->expenses()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete asset [{$record->code}]: Linked financial expense records exist and must be preserved for accountability.",
                    ];
                }
                if ($record->deployments()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete asset [{$record->code}]: Linked historical deployment records exist and must be preserved for accountability.",
                    ];
                }
                if ($record->maintenance()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete asset [{$record->code}]: Linked historical maintenance work orders exist.",
                    ];
                }
                if ($record->inspections()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete asset [{$record->code}]: Linked safety inspection reports exist.",
                    ];
                }
                break;

            case 'maintenances':
                if (method_exists($record, 'parts') && $record->parts()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete work order [{$record->work_order_number}]: Parts & materials consumption is recorded.",
                    ];
                }
                if (method_exists($record, 'receipts') && $record->receipts()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete work order [{$record->work_order_number}]: Expense receipts and invoices are attached.",
                    ];
                }
                break;

            case 'deployments':
                if (method_exists($record, 'returns') && $record->returns()->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete deployment #{$record->id}: Return verification records are linked to this deployment.",
                    ];
                }
                break;

            case 'users':
                if (AuditLog::where('user_id', $record->id)->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete user [{$record->name}]: Historical audit logs and official actions are signed by this account.",
                    ];
                }
                break;

            case 'locations':
                if (Asset::withTrashed()->where('location_id', $record->id)->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete location [{$record->name}]: Active or archived assets are assigned to this location.",
                    ];
                }
                break;

            case 'asset_categories':
                if (Asset::withTrashed()->where('category_id', $record->id)->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete category [{$record->name}]: Active or archived assets are categorized under this entry.",
                    ];
                }
                break;

            case 'asset_types':
                if (Asset::withTrashed()->where('asset_type_id', $record->id)->exists()) {
                    return [
                        'allowed' => false,
                        'reason'  => "Cannot permanently delete type [{$record->name}]: Active or archived assets are registered under this type.",
                    ];
                }
                break;
        }

        return [
            'allowed' => true,
            'reason'  => null,
        ];
    }

    /**
     * Restore an archived record.
     */
    public static function restore(string $moduleKey, int|string $id): Model
    {
        $registry = static::registry();
        if (!isset($registry[$moduleKey])) {
            throw new \InvalidArgumentException("Invalid archive module: {$moduleKey}");
        }

        $modelClass = $registry[$moduleKey]['model'];
        $record = $modelClass::onlyTrashed()->findOrFail($id);

        $record->restore();

        return $record;
    }

    /**
     * Permanently delete an archived record with strict safety enforcement.
     */
    public static function forceDelete(string $moduleKey, int|string $id): Model
    {
        $registry = static::registry();
        if (!isset($registry[$moduleKey])) {
            throw new \InvalidArgumentException("Invalid archive module: {$moduleKey}");
        }

        $modelClass = $registry[$moduleKey]['model'];
        $record = $modelClass::onlyTrashed()->findOrFail($id);

        $check = static::canPermanentlyDelete($moduleKey, $record);
        if (!$check['allowed']) {
            throw new \DomainException($check['reason']);
        }

        $identifier = $record->code ?? $record->work_order_number ?? $record->name ?? "#{$record->id}";
        $entityName = class_basename($record);

        AuditTrailService::log(
            'permanently_deleted',
            $record,
            "{$entityName} [{$identifier}] permanently deleted from system archives.",
            $record->toArray(),
            null,
            Auth::id()
        );

        $record->forceDelete();

        return $record;
    }

    /**
     * Return structured details of an archived record for the View modal.
     */
    public static function getRecordDetails(string $moduleKey, int|string $id): array
    {
        $registry = static::registry();
        if (!isset($registry[$moduleKey])) {
            throw new \InvalidArgumentException("Invalid archive module: {$moduleKey}");
        }

        $conf = $registry[$moduleKey];
        $modelClass = $conf['model'];
        $record = $modelClass::onlyTrashed()->with($conf['with'] ?? [])->findOrFail($id);

        $attributes = [];
        $rawAttributes = $record->toArray();

        // Omit internal / sensitive / redundant keys
        $omit = ['password', 'remember_token', 'deleted_at', 'deleted_by', 'deletion_reason'];
        foreach ($rawAttributes as $k => $v) {
            if (in_array($k, $omit, true)) continue;
            if (is_array($v) || is_object($v)) continue;
            $attributes[ucwords(str_replace('_', ' ', $k))] = $v !== null && $v !== '' ? (string) $v : 'N/A';
        }

        $safetyCheck = static::canPermanentlyDelete($moduleKey, $record);

        return [
            'id'                  => $record->id,
            'module_key'          => $moduleKey,
            'module_label'        => $conf['label'],
            'module_icon'         => $conf['icon'],
            'module_color'        => $conf['color'],
            'record_id'           => static::formatRecordId($conf, $record),
            'record_name'         => static::formatRecordName($conf, $record),
            'deletion_reason'     => $record->deletion_reason ?? 'Archived via system management interface.',
            'deleted_by_name'     => $record->deletedByUser?->name ?? 'System / Administrator',
            'deleted_by_email'    => $record->deletedByUser?->email ?? null,
            'deleted_at'          => $record->deleted_at ? Carbon::parse($record->deleted_at)->format('M d, Y h:i A') : 'N/A',
            'deleted_at_relative' => $record->deleted_at ? Carbon::parse($record->deleted_at)->diffForHumans() : 'N/A',
            'created_at'          => $record->created_at ? Carbon::parse($record->created_at)->format('M d, Y h:i A') : 'N/A',
            'can_permanently_delete' => $safetyCheck['allowed'],
            'permanent_delete_block_reason' => $safetyCheck['reason'],
            'attributes'          => $attributes,
        ];
    }
}

