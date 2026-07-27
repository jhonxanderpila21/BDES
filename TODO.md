# TODO - Dashboard Cards Clickable

## Task
Make all dashboard cards clickable so they automatically render to their corresponding page.

## Implementation Steps

### Step 1: Edit `resources/views/components/statistic-card.blade.php`
- [x] Add optional `href` prop
- [x] When `href` is provided, wrap the card div in an `<a>` tag with hover effects

### Step 2: Edit `resources/views/admin/dashboard/index.blade.php`
- [x] Add `:href="route('assets.index')"` to Total Assets card
- [x] Add `:href="route('assets.index', ['status' => 'available'])"` to Available Assets card
- [x] Add `:href="route('deployment.index')"` to Deployed Assets card
- [x] Add `:href="route('maintenance.index')"` to Under Maintenance card
- [x] Add `:href="route('inspection.index')"` to Due for Inspection card
- [x] Add `:href="route('incident-reports.index')"` to Reported Incidents card
- [x] Make Upcoming Inspections items clickable → link to `route('inspection.show', $inspection->id)`
- [x] Make Maintenance Reminders items clickable → link to `route('maintenance.show', $reminder->id)`
- [x] Make "View All" link in Recent Activities point to `route('asset-history.index')`

### Step 3: Verify
- [ ] Test that all links route correctly

