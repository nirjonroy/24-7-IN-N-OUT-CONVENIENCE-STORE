<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BusinessHourController extends Controller
{
    public const DAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    public function edit(Location $location): View
    {
        return view('admin.business-hours.edit', [
            'location' => $location->load('business'),
            'days' => self::DAYS,
            'hours' => $location->businessHours()->get()->keyBy('day_of_week'),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
            'hours.*.is_closed' => ['nullable', 'boolean'],
            'hours.*.is_24_hours' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($location, $data) {
            foreach (self::DAYS as $day => $label) {
                $row = $data['hours'][$day] ?? [];
                $isClosed = ! empty($row['is_closed']);
                $is24Hours = ! empty($row['is_24_hours']);
                $opensAt = $row['opens_at'] ?? null;
                $closesAt = $row['closes_at'] ?? null;

                if ($isClosed) {
                    $opensAt = null;
                    $closesAt = null;
                    $is24Hours = false;
                } elseif ($is24Hours) {
                    $opensAt = null;
                    $closesAt = null;
                    $isClosed = false;
                } elseif (! $opensAt || ! $closesAt) {
                    throw ValidationException::withMessages([
                        "hours.{$day}.opens_at" => "{$label} requires open and close times unless closed or 24 hours.",
                    ]);
                }

                BusinessHour::updateOrCreate(
                    ['location_id' => $location->id, 'day_of_week' => $day],
                    [
                        'opens_at' => $opensAt,
                        'closes_at' => $closesAt,
                        'is_closed' => $isClosed,
                        'is_24_hours' => $is24Hours,
                    ]
                );
            }
        });

        return back()->with('success', 'Business hours updated successfully.');
    }
}
