<?php

namespace App\Services\Center;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Collection;

class StaffService
{
    public function getStaffForCenter(int $centerId, bool $activeOnly = true): Collection
    {
        $query = Staff::where('center_id', $centerId)
            ->orderBy('display_order');

        if ($activeOnly) {
            $query->where('status', 'active');
        }

        return $query->get();
    }

    public function create(int $centerId, array $data): Staff
    {
        $data['center_id'] = $centerId;

        if (!isset($data['display_order'])) {
            $maxOrder = Staff::where('center_id', $centerId)->max('display_order') ?? 0;
            $data['display_order'] = $maxOrder + 1;
        }

        return Staff::create($data);
    }

    public function update(int $staffId, array $data): Staff
    {
        $staff = Staff::findOrFail($staffId);
        $staff->update($data);
        return $staff->fresh();
    }

    public function delete(int $staffId): bool
    {
        $staff = Staff::findOrFail($staffId);
        return $staff->delete();
    }

    public function reorder(int $centerId, array $orderArray): bool
    {
        foreach ($orderArray as $order => $staffId) {
            Staff::where('id', $staffId)
                ->where('center_id', $centerId)
                ->update(['display_order' => $order + 1]);
        }

        return true;
    }

    public function getActiveStaffCount(int $centerId): int
    {
        return Staff::where('center_id', $centerId)
            ->where('status', 'active')
            ->count();
    }
}
