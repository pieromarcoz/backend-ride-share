<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Events\TripEnded;
use App\Events\TripLocationUpdated;
use App\Events\TripStarted;
use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'origin' => 'required',
            'destination' => 'required',
            'destination_name' => 'required',
        ]);

        return $request->user()->trips()->create([
            'driver_id' => $request->user()->driver->id,
            'origin' => $request->origin,
            'destination' => $request->destination,
            'destination_name' => $request->destination_name,
        ]);
    }

    public function show(Request $request, Trip $trip)
    {
        // ¿el viaje está asociado al usuario autenticado?
        if ($trip->user_id === $request->user()->id) {
            return $trip;
        }

        if ($trip->driver && $request->user()->driver)
        {
            if ($trip->driver->id === $request->user()->driver->id) {
                return $trip;
            }
        }
        return response()->json([
            'message' => 'Cannot access this trip',
        ], 404);
    }

    public function accept(Request $request, Trip $trip)
    {
        // el conductor acepta un viaje
        $request->validate([
            'driver_location' => 'required',
        ]);
        $trip->update([
            'driver_id' => $request->user()->id,
            'driver_location' => $request->driver_location,
        ]);

        $trip->load('driver.user');

        TripAccepted::dispatch($trip, $request->user());

        return $trip;
    }

    public function start(Request $request, Trip $trip)
    {
        // el conductor ha puesto en marcha a un pasajero hacia su destino
        $trip->update([
            'is_started' => true,
        ]);
        $trip->load('driver.user');

        TripStarted::dispatch($trip, $request->user());

        return $trip;
    }
    public function end(Request $request, Trip $trip)
    {
        // el conductor ha finalizado el viaje
        $trip->update([
            'is_completed' => true,
        ]);
        $trip->load('driver.user');

        TripEnded::dispatch($trip, $request->user());

        return $trip;
    }
    public function location(Request $request, Trip $trip)
    {
        // actualizar la ubicación actual del conductor
        $request->validate([
            'driver_location' => 'required',
        ]);
        $trip->update([
            'driver_location' => $request->driver_location,
        ]);
        $trip->load('driver.user');

        TripLocationUpdated::dispatch($trip, $request->user());

        return $trip;
    }
}
