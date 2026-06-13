<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Booking;

class SearchController extends Controller
{
    /**
     * Global search endpoint — called by ⌘K Command Palette
     * GET /search/global?q=...
     */
    public function global(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['results' => [], 'query' => $q]);
        }

        $results = collect();
        $limit   = 4; // max per category

        // ── Clients ──────────────────────────────────────────────────────────
        Client::where('company_name', 'like', "%{$q}%")
            ->orWhere('pic_name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->each(fn($c) => $results->push([
                'icon'  => '🏢',
                'label' => $c->company_name,
                'sub'   => 'Client · ' . ($c->pic_name ?? '—'),
                'type'  => 'client',
                'url'   => route('clients.show', $c->id),
            ]));

        // ── Opportunities / Deals ────────────────────────────────────────────
        Opportunity::where('title', 'like', "%{$q}%")
            ->orWhere('opp_number', 'like', "%{$q}%")
            ->with('client:id,company_name')
            ->limit($limit)
            ->get()
            ->each(fn($o) => $results->push([
                'icon'  => '🎯',
                'label' => $o->title,
                'sub'   => 'Deal · ' . ($o->client->company_name ?? '—') . ' · ' . ucfirst($o->stage),
                'type'  => 'deal',
                'url'   => route('pipeline.index') . '#deal-' . $o->id,
            ]));

        // ── Vehicles / Fleet ─────────────────────────────────────────────────
        Vehicle::where('plate_number', 'like', "%{$q}%")
            ->orWhere('model', 'like', "%{$q}%")
            ->orWhere('brand', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->each(fn($v) => $results->push([
                'icon'  => '🚌',
                'label' => ($v->brand ? ucfirst($v->brand) : '') . ' ' . $v->model,
                'sub'   => 'Fleet · ' . ($v->plate_number ?? '—') . ' · ' . ($v->status ?? '—'),
                'type'  => 'fleet',
                'url'   => route('fleet.show', $v->id),
            ]));

        // ── Drivers ──────────────────────────────────────────────────────────
        Driver::where('name', 'like', "%{$q}%")
            ->orWhere('license_number', 'like', "%{$q}%")
            ->limit($limit)
            ->get()
            ->each(fn($d) => $results->push([
                'icon'  => '👤',
                'label' => $d->name,
                'sub'   => 'Driver · ' . ($d->license_number ?? '—'),
                'type'  => 'driver',
                'url'   => route('fleet.index'),
            ]));

        // ── Bookings ─────────────────────────────────────────────────────────
        Booking::where('booking_number', 'like', "%{$q}%")
            ->with('client:id,company_name')
            ->limit($limit)
            ->get()
            ->each(fn($b) => $results->push([
                'icon'  => '📋',
                'label' => $b->booking_number,
                'sub'   => 'Booking · ' . ($b->client->company_name ?? '—'),
                'type'  => 'booking',
                'url'   => route('bookings.show', $b->id),
            ]));

        return response()->json([
            'results' => $results->take(16)->values(),
            'query'   => $q,
            'total'   => $results->count(),
        ]);
    }
}
