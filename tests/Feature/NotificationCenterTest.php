<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        session()->flush();
        $this->user = User::factory()->create([
            'role' => 'administrator',
            'is_active' => true,
        ]);
    }

    /**
     * Test the dedicated notification center page and its route have been removed.
     */
    public function test_notification_center_page_and_route_are_removed(): void
    {
        $response = $this->actingAs($this->user)->get('/notifications');

        $response->assertStatus(404);
    }

    /**
     * Test the sidebar no longer contains the Notifications Center menu item.
     */
    public function test_sidebar_does_not_contain_notification_center(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Notifications Center');
        $response->assertDontSee('href="http://localhost/notifications"', false);
    }

    /**
     * Test the top navigation bell is retained with dropdown and unread badge.
     */
    public function test_top_navbar_bell_is_retained(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('fa-bell', false);
        $response->assertSee('bdesNotificationComponent', false);
        $response->assertSee(route('notifications.mark-read'), false);
        $response->assertSee(route('notifications.mark-all-read'), false);
    }

    /**
     * Test markAsRead API endpoint persists read status in database/cache.
     */
    public function test_mark_as_read_api_persists_status(): void
    {
        $notificationService = app(NotificationService::class);
        $all = $notificationService->getAllNotifications();

        $testId = $all->isNotEmpty() ? $all->first()['id'] : 'maint-999';

        $response = $this->actingAs($this->user)
            ->postJson(route('notifications.mark-read'), [
                'id' => $testId,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'id' => $testId,
        ]);

        $readIds = $notificationService->getReadNotificationIds();
        $this->assertContains($testId, $readIds);
    }

    /**
     * Test clicking View Record route marks notification as read and redirects.
     */
    public function test_view_record_route_marks_notification_read_and_redirects(): void
    {
        $notificationService = app(NotificationService::class);
        $all = $notificationService->getAllNotifications();

        $testId = $all->isNotEmpty() ? $all->first()['id'] : 'test-view-record';

        $response = $this->actingAs($this->user)->get(route('notifications.view-record', ['id' => $testId]));

        $response->assertStatus(302);

        $readIds = $notificationService->getReadNotificationIds();
        $this->assertContains($testId, $readIds);
    }

    /**
     * Test read status persists across subsequent requests.
     */
    public function test_read_status_persists_across_subsequent_requests(): void
    {
        $this->actingAs($this->user);
        $notificationService = app(NotificationService::class);
        $testId = 'persisted-notif-1';

        $notificationService->markAsRead($testId);

        // Fetch read notifications in another call
        $readIds = $notificationService->getReadNotificationIds();
        $this->assertContains($testId, $readIds);

        // Verify it is also in cache prefix for user
        $cached = Cache::get(NotificationService::CACHE_READ_PREFIX . $this->user->id);
        $this->assertIsArray($cached);
        $this->assertContains($testId, $cached);
    }
}
