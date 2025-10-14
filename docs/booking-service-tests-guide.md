BookingService tests - implementation guide

This guide documents the steps, pitfalls and concrete actions taken while implementing unit tests for BookingService. Use it as a reference when writing similar service tests.

1) Goal

- Write focused unit tests for BookingService to cover: create (happy path + edge cases), update/reschedule, cancel, confirm/complete transitions, reminders retrieval and marking.

2) Key components to mock or fake

- CalendlyService (external integration) — use Mockery to bind a mock and configure expectations (isConfigured, createEvent, rescheduleEvent, cancelEvent).
- Jobs/Queue — use Queue::fake() and assert job dispatch (SendBookingConfirmationJob::class).
- Factories — use model factories for User, Center, Service, Booking. Ensure factories don't write columns that don't exist.

3) Setup patterns and gotchas

- Resolve services after binding mocks: If a service type is resolved before you bind a mock for an injected dependency, the mock will not be used. Always bind/define mocks first, then resolve the service from the container ($this->app->make(BookingService::class)).

- DB column formats vs model casts:
  - booking_date can be stored as DATE and SQLite may return a "YYYY-MM-DD 00:00:00" value. When comparing dates, use whereDate('booking_date', $date) in queries or normalize formats in assertions.
  - booking_time stored as SQL TIME should be treated as string on the model. Casting it to DateTime may lead to format mismatches in tests.

- Factory attributes must match migrations: If a factory writes a column that doesn't exist in the migration (for instance 'display_order' on services), tests will fail with "no column named" errors. Keep factories aligned with migrations.

4) Duplicate booking detection

- Be precise in the duplicate check. Use:
  Booking::where('user_id', $userId)
    ->where('center_id', $centerId)
    ->whereDate('booking_date', $date)
    ->where('booking_time', $time)
    ->whereIn('status', ['pending', 'confirmed'])
    ->first();

  This ensures date/time comparisons work with SQLite and other DBs.

5) Test case checklist (examples)

- test_create_booking_happy_path_dispatches_job
  - Mock CalendlyService::isConfigured & createEvent
  - Queue::fake()
  - Create user/center/service and call service->create()
  - Assert DB record created and job pushed

- test_create_rejects_past_date
  - Call create with booking_date in the past; expect InvalidArgumentException

- test_create_rejects_duplicate_booking
  - Create an initial booking (via service with Calendly mocked off)
  - Call create again with identical date/time; expect RuntimeException

- test_update_reschedules_calendly_event_if_present
  - Create a booking with calendly_event_uri
  - Mock CalendlyService::rescheduleEvent() expectation
  - Call service->update() and assert reschedule was called

- test_cancel_calls_calendly_and_sets_cancelled
  - Create confirmed booking with calendly_event_uri
  - Mock CalendlyService::cancelEvent() expectation
  - Call service->cancel(), assert status === 'cancelled' and reason set

- test_confirm_and_complete_transitions
  - Create pending booking, call confirm() and complete() in order and assert status changes

- test_get_bookings_needing_reminders_and_mark
  - Create booking for target date (now()+1 day), reminder_sent_at null
  - Call getBookingsNeedingReminders(), assert booking present
  - Call markReminderSent(), assert reminder_sent_at set and sms_sent true

6) Validation and iteration

- Lint new test files with php -l before running PHPUnit.
- Run phpunit for the specific test class to get fast feedback.
- If tests fail, inspect: factory vs migration mismatches, model casts, mock binding timing, datetime formatting.

7) Notes

- Keep tests deterministic: set predictable dates using Carbon::setTestNow if needed.
- For job dispatch assertions, prefer Queue::assertPushed(YourJob::class, fn($job) => $job->payload === expected).


Conclusion

Use this guide as a checklist and reference while creating service tests for other modules (MediaService, TranslationService, AccountDeletionService, DataExportService). The same principles apply: mock external integrations, align factories with migrations, and ensure model casts and query comparisons are DB-robust.
