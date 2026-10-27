<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display the full Notifications Center page with category filtering.
     */
    public function index(Request $request): View
    {
        $currentFilter = $request->get('filter', 'all');
        $allNotifications = $this->notificationService->getAllNotifications();

        // Categorized statistics
        $stats = [
            'total' => $allNotifications->count(),
            'unread' => $allNotifications->where('is_read', false)->count(),
            'maintenance' => $allNotifications->where('category', 'maintenance')->count(),
            'deployment' => $allNotifications->where('category', 'deployment')->count(),
            'inspection' => $allNotifications->where('category', 'inspection')->count(),
            'reported_asset' => $allNotifications->where('category', 'reported_asset')->count(),
        ];

        // Filtered list
        $notifications = $this->notificationService->getAllNotifications($currentFilter);

        return view('admin.notifications.index', compact('notifications', 'currentFilter', 'stats'));
    }

    /**
     * Mark a single notification as read (supports both AJAX and form submit).
     */
    public function markAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'id' => 'required|string',
        ]);

        $this->notificationService->markAsRead($validated['id']);
        $recent = $this->notificationService->getRecentNotifications();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'id' => $validated['id'],
                'unread_count' => $recent['unread_count'],
            ]);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read (supports both AJAX and form submit).
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $ids = $request->input('ids');
        if (is_array($ids)) {
            $this->notificationService->markAllAsRead($ids);
        } else {
            $this->notificationService->markAllAsRead();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'All notifications have been marked as read.');
    }

    /**
     * Mark a specific notification as read and redirect directly to its target record.
     */
    public function viewRecord(string $id): RedirectResponse
    {
        $this->notificationService->markAsRead($id);

        $notification = $this->notificationService->getAllNotifications()->firstWhere('id', $id);

        if ($notification && !empty($notification['url'])) {
            return redirect()->to($notification['url']);
        }

        return redirect()->route('notifications.index');
    }
}
