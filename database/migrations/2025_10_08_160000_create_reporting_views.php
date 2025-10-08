<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW active_centers_summary AS
SELECT
    c.id,
    c.name,
    c.slug,
    c.city,
    c.status,
    c.capacity,
    c.current_occupancy,
    ROUND((c.current_occupancy / NULLIF(c.capacity, 0) * 100), 2) AS occupancy_rate,
    c.moh_license_number,
    c.accreditation_status,
    COUNT(DISTINCT s.id) AS services_count,
    COUNT(DISTINCT st.id) AS staff_count,
    COUNT(DISTINCT CASE WHEN b.status = 'confirmed' THEN b.id END) AS confirmed_bookings_count,
    COUNT(DISTINCT CASE WHEN t.status = 'approved' THEN t.id END) AS approved_testimonials_count,
    ROUND(AVG(CASE WHEN t.status = 'approved' THEN t.rating END), 2) AS average_rating,
    c.created_at,
    c.updated_at
FROM centers c
LEFT JOIN services s ON c.id = s.center_id AND s.deleted_at IS NULL AND s.status = 'published'
LEFT JOIN staff st ON c.id = st.center_id AND st.status = 'active'
LEFT JOIN bookings b ON c.id = b.center_id AND b.deleted_at IS NULL
LEFT JOIN testimonials t ON c.id = t.center_id AND t.deleted_at IS NULL
WHERE c.deleted_at IS NULL AND c.status = 'published'
GROUP BY
    c.id,
    c.name,
    c.slug,
    c.city,
    c.status,
    c.capacity,
    c.current_occupancy,
    c.moh_license_number,
    c.accreditation_status,
    c.created_at,
    c.updated_at;
SQL);

        DB::statement(<<<'SQL'
CREATE OR REPLACE VIEW user_booking_history AS
SELECT
    b.id AS booking_id,
    b.booking_number,
    b.user_id,
    u.name AS user_name,
    u.email AS user_email,
    b.center_id,
    c.name AS center_name,
    c.address AS center_address,
    c.phone AS center_phone,
    b.service_id,
    s.name AS service_name,
    s.price AS service_price,
    s.price_unit AS service_price_unit,
    b.booking_date,
    b.booking_time,
    b.booking_type,
    b.status,
    b.calendly_event_uri,
    b.calendly_cancel_url,
    b.calendly_reschedule_url,
    b.created_at,
    b.updated_at
FROM bookings b
INNER JOIN users u ON b.user_id = u.id
INNER JOIN centers c ON b.center_id = c.id
LEFT JOIN services s ON b.service_id = s.id
WHERE b.deleted_at IS NULL AND u.deleted_at IS NULL;
SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS user_booking_history');
        DB::statement('DROP VIEW IF EXISTS active_centers_summary');
    }
};
