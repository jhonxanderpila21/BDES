<?php

namespace App\Services;

use App\Models\Deployment;
use App\Models\Inspection;
use App\Models\Maintenance;
use App\Models\MaintenanceSchedule;
use App\Models\ReportedAsset;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

class NotificationService
{
    /**
     * Session key used to persist read notification IDs.
     */
    public const SESSION_READ_KEY = 'bdes_read_notifications';

    /**
     * Cache key prefix used to persist read notification IDs across logins/reloads.
     */
    public const CACHE_READ_PREFIX = 'bdes_read_notifs_user_';

    /**
     * Retrieve all real operational notifications compiled from application models.
     *
     * @param string|null $categoryFilter Optional category filter (e.g. 'maintenance', 'deployment', 'inspection', 'reported_asset')
     * @return Collection<int, array>
     */
    public function getAllNotifications(?string $categoryFilter = null): Collection
    {
        $notifications = collect();
        $today = Carbon::today();
        $readIds = $this->getReadNotificationIds();

        try {
            // 1. Maintenance Work Orders (Overdue & Due within 7 days)
            $maintenances = Maintenance::with('asset')
                ->whereIn('status', ['scheduled', 'pending', 'in_progress'])
                ->whereNotNull('scheduled_date')
                ->orderBy('scheduled_date', 'asc')
                ->get();

            foreach ($maintenances as $m) {
                $scheduled = Carbon::parse($m->scheduled_date);
                $isOverdue = $scheduled->lt($today);
                $isDueSoon = !$isOverdue && $scheduled->lte($today->copy()->addDays(7));
                $isExplicitHigh = in_array(strtolower($m->priority ?? ''), ['high', 'emergency', 'urgent'], true);

                if ($isOverdue || $isDueSoon || $isExplicitHigh) {
                    $id = 'maint-' . $m->id;
                    $priority = ($isOverdue || $isExplicitHigh) ? 'high' : 'medium';
                    $badgeText = $isOverdue ? 'Overdue Work Order' : ($isExplicitHigh ? 'High Priority' : 'Due Soon');
                    $assetName = $m->asset?->name ?? 'Equipment';
                    $assetCode = $m->asset?->code ? " ({$m->asset->code})" : '';

                    $notifications->push([
                        'id' => $id,
                        'source_id' => $m->id,
                        'category' => 'maintenance',
                        'category_label' => 'Maintenance',
                        'title' => ($isOverdue ? 'Overdue: ' : 'Maintenance Due: ') . $assetName,
                        'description' => ($m->work_order_number ? "{$m->work_order_number}: " : '') . 
                            ($m->type ? ucfirst(str_replace('_', ' ', $m->type)) : 'Scheduled service') . 
                            " for {$assetName}{$assetCode} scheduled for {$scheduled->format('M d, Y')}.",
                        'message' => ($m->work_order_number ? "{$m->work_order_number}: " : '') . 
                            ($m->type ? ucfirst(str_replace('_', ' ', $m->type)) : 'Scheduled service') . 
                            " for {$assetName}{$assetCode} scheduled for {$scheduled->format('M d, Y')}.",
                        'priority' => $priority,
                        'badge' => $badgeText,
                        'icon' => 'fas fa-wrench',
                        'icon_bg' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600',
                        'color' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600',
                        'url' => route('maintenance.index', ['tab' => 'logs']),
                        'timestamp' => $scheduled->diffForHumans(),
                        'date' => $scheduled,
                        'is_today' => $scheduled->isToday(),
                        'is_read' => in_array($id, $readIds, true),
                    ]);
                }
            }

            // 2. Active Deployments (Overdue & Due for return within 3 days)
            $deployments = Deployment::with('asset')
                ->where('status', 'active')
                ->whereNotNull('expected_return_date')
                ->orderBy('expected_return_date', 'asc')
                ->get();

            foreach ($deployments as $d) {
                $returnDate = Carbon::parse($d->expected_return_date);
                $isOverdue = $returnDate->lt($today);
                $isDueSoon = !$isOverdue && $returnDate->lte($today->copy()->addDays(3));

                if ($isOverdue || $isDueSoon) {
                    $id = 'deploy-' . $d->id;
                    $priority = $isOverdue ? 'high' : 'medium';
                    $badgeText = $isOverdue ? 'Overdue Return' : 'Return Reminder';
                    $assetName = $d->asset?->name ?? 'Borrowed Asset';
                    $borrower = $d->responsible_person ?: ($d->assigned_team ?: 'Field Team');

                    $notifications->push([
                        'id' => $id,
                        'source_id' => $d->id,
                        'category' => 'deployment',
                        'category_label' => 'Deployment',
                        'title' => ($isOverdue ? 'Overdue Return: ' : 'Return Reminder: ') . $assetName,
                        'description' => "Deployed to {$borrower} at {$d->destination}. Expected return was {$returnDate->format('M d, Y')}.",
                        'message' => "Deployed to {$borrower} at {$d->destination}. Expected return was {$returnDate->format('M d, Y')}.",
                        'priority' => $priority,
                        'badge' => $badgeText,
                        'icon' => 'fas fa-truck-fast',
                        'icon_bg' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600',
                        'color' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-blue-50 text-blue-600',
                        'url' => route('deployment.index', ['status' => 'active']),
                        'timestamp' => $returnDate->diffForHumans(),
                        'date' => $returnDate,
                        'is_today' => $returnDate->isToday(),
                        'is_read' => in_array($id, $readIds, true),
                    ]);
                }
            }

            // 3. Preventive Maintenance Schedules (Due or Overdue)
            $schedules = MaintenanceSchedule::with('asset')
                ->whereIn('status', [MaintenanceScheduleService::STATUS_DUE, MaintenanceScheduleService::STATUS_OVERDUE])
                ->orderBy('next_due_date', 'asc')
                ->get();

            foreach ($schedules as $s) {
                $dueDate = $s->next_due_date ? Carbon::parse($s->next_due_date) : null;
                $isOverdue = $s->status === MaintenanceScheduleService::STATUS_OVERDUE;
                $id = 'sched-' . $s->id;
                $priority = $isOverdue ? 'high' : 'medium';
                $assetName = $s->asset?->name ?? 'Asset';

                $notifications->push([
                    'id' => $id,
                    'source_id' => $s->id,
                    'category' => 'maintenance',
                    'category_label' => 'Preventive',
                    'title' => 'Preventive Schedule: ' . $assetName,
                    'description' => "Task '{$s->maintenance_task}' is marked " . strtolower($s->status) . 
                        ($dueDate ? " (Due: {$dueDate->format('M d, Y')})." : '.'),
                    'message' => "Task '{$s->maintenance_task}' is marked " . strtolower($s->status) . 
                        ($dueDate ? " (Due: {$dueDate->format('M d, Y')})." : '.'),
                    'priority' => $priority,
                    'badge' => $isOverdue ? 'Schedule Overdue' : 'Schedule Due',
                    'icon' => 'fas fa-calendar-check',
                    'icon_bg' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600',
                    'color' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600',
                    'url' => route('maintenance.index', ['tab' => 'schedules']),
                    'timestamp' => $dueDate ? $dueDate->diffForHumans() : 'Due now',
                    'date' => $dueDate ?? $today,
                    'is_today' => $dueDate ? $dueDate->isToday() : false,
                    'is_read' => in_array($id, $readIds, true),
                ]);
            }

            // 4. Scheduled Inspections (Overdue or Due within 7 days)
            $inspections = Inspection::with('asset')
                ->where('status', 'scheduled')
                ->whereNotNull('inspection_date')
                ->where('inspection_date', '<=', $today->copy()->addDays(7)->toDateString())
                ->orderBy('inspection_date', 'asc')
                ->get();

            foreach ($inspections as $ins) {
                $inspDate = Carbon::parse($ins->inspection_date);
                $isOverdue = $inspDate->lt($today);
                $id = 'insp-' . $ins->id;
                $priority = $isOverdue ? 'high' : 'medium';
                $assetName = $ins->asset?->name ?? 'Equipment';

                $notifications->push([
                    'id' => $id,
                    'source_id' => $ins->id,
                    'category' => 'inspection',
                    'category_label' => 'Inspection',
                    'title' => ($isOverdue ? 'Overdue Inspection: ' : 'Inspection Due: ') . $assetName,
                    'description' => "Physical inspection scheduled for {$inspDate->format('M d, Y')}" . 
                        ($ins->inspector ? " by {$ins->inspector}." : '.'),
                    'message' => "Physical inspection scheduled for {$inspDate->format('M d, Y')}" . 
                        ($ins->inspector ? " by {$ins->inspector}." : '.'),
                    'priority' => $priority,
                    'badge' => $isOverdue ? 'Overdue Inspection' : 'Inspection Due',
                    'icon' => 'fas fa-clipboard-check',
                    'icon_bg' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-purple-50 text-purple-600',
                    'color' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-purple-50 text-purple-600',
                    'url' => route('maintenance.index', ['tab' => 'inspections']),
                    'timestamp' => $inspDate->diffForHumans(),
                    'date' => $inspDate,
                    'is_today' => $inspDate->isToday(),
                    'is_read' => in_array($id, $readIds, true),
                ]);
            }

            // 5. Open Reported Assets (Pending or For Inspection)
            $reports = ReportedAsset::with('asset')
                ->whereIn('status', ['pending', 'for_inspection'])
                ->latest('reported_date')
                ->take(6)
                ->get();

            foreach ($reports as $r) {
                $reportDate = $r->reported_date ? Carbon::parse($r->reported_date) : Carbon::parse($r->created_at);
                $isDamage = in_array(strtolower($r->reported_type ?? ''), ['damage', 'lost', 'damaged'], true);
                $id = 'report-' . $r->id;
                $priority = $isDamage ? 'high' : 'medium';

                $notifications->push([
                    'id' => $id,
                    'source_id' => $r->id,
                    'category' => 'reported_asset',
                    'category_label' => 'Damage Report',
                    'title' => 'Reported Issue: ' . ($r->asset_name ?: 'Asset Incident'),
                    'description' => ucfirst($r->reported_type ?? 'issue') . ' status is ' . 
                        ucwords(str_replace('_', ' ', $r->status)) . ': ' . 
                        Str::limit($r->description ?? 'Requires verification.', 70),
                    'message' => ucfirst($r->reported_type ?? 'issue') . ' status is ' . 
                        ucwords(str_replace('_', ' ', $r->status)) . ': ' . 
                        Str::limit($r->description ?? 'Requires verification.', 70),
                    'priority' => $priority,
                    'badge' => $isDamage ? 'High Urgency' : 'Report Review',
                    'icon' => 'fas fa-triangle-exclamation',
                    'icon_bg' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600',
                    'color' => $priority === 'high' ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600',
                    'url' => route('reported-assets.show', $r->id),
                    'timestamp' => $reportDate->diffForHumans(),
                    'date' => $reportDate,
                    'is_today' => $reportDate->isToday(),
                    'is_read' => in_array($id, $readIds, true),
                ]);
            }
        } catch (Throwable $e) {
            // Defensive handling: log failure quietly to prevent navbar from breaking if database has transient issues
            report($e);
        }

        // Sort by priority (High first), then unread first, then by date descending
        $sorted = $notifications->sort(function ($a, $b) {
            // Unread before read
            if ($a['is_read'] !== $b['is_read']) {
                return $a['is_read'] ? 1 : -1;
            }
            // High priority before medium/low
            $priorityOrder = ['high' => 1, 'medium' => 2, 'low' => 3];
            $pA = $priorityOrder[$a['priority']] ?? 9;
            $pB = $priorityOrder[$b['priority']] ?? 9;
            if ($pA !== $pB) {
                return $pA <=> $pB;
            }
            // Date descending
            return $b['date'] <=> $a['date'];
        })->values();

        if ($categoryFilter && $categoryFilter !== 'all') {
            if ($categoryFilter === 'unread') {
                $sorted = $sorted->where('is_read', false)->values();
            } else {
                $sorted = $sorted->where('category', $categoryFilter)->values();
            }
        }

        return $sorted;
    }

    /**
     * Retrieve a batch of notifications formatted for the navbar dropdown.
     */
    public function getRecentNotifications(int $limit = 15): array
    {
        $all = $this->getAllNotifications();
        $unreadCount = $all->where('is_read', false)->count();

        return [
            'unread_count' => $unreadCount,
            'total_count' => $all->count(),
            'items' => $all->take($limit)->values()->all(),
        ];
    }

    /**
     * Retrieve the list of IDs marked as read from user session or cache.
     */
    public function getReadNotificationIds(): array
    {
        $userId = auth()->id();
        $cached = [];
        if ($userId) {
            $cached = Cache::get(self::CACHE_READ_PREFIX . $userId, []);
            if (!is_array($cached)) {
                $cached = [];
            }
        }

        $sessionIds = session()->get(self::SESSION_READ_KEY, []);
        if (!is_array($sessionIds)) {
            $sessionIds = [];
        }

        $merged = array_values(array_unique(array_merge($cached, $sessionIds)));
        return $merged;
    }

    /**
     * Save the read notification IDs to both session and cache.
     */
    public function saveReadNotificationIds(array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));
        session()->put(self::SESSION_READ_KEY, $ids);

        $userId = auth()->id();
        if ($userId) {
            Cache::put(self::CACHE_READ_PREFIX . $userId, $ids, now()->addDays(60));
        }
    }

    /**
     * Mark a specific notification ID as read.
     */
    public function markAsRead(string $id): void
    {
        $read = $this->getReadNotificationIds();
        if (!in_array($id, $read, true)) {
            $read[] = $id;
            $this->saveReadNotificationIds($read);
        }
    }

    /**
     * Mark all provided notification IDs (or all active) as read.
     *
     * @param array<string>|null $ids
     */
    public function markAllAsRead(?array $ids = null): void
    {
        if ($ids === null) {
            $ids = $this->getAllNotifications()->pluck('id')->all();
        }

        $read = $this->getReadNotificationIds();
        $merged = array_unique(array_merge($read, $ids));
        $this->saveReadNotificationIds($merged);
    }
}
