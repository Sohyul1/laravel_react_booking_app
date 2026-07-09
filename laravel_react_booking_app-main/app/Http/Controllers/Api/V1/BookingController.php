<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\V1\BookingCollection;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;

class BookingController extends Controller
{
    public function index()
    {
        return new BookingCollection(Booking::all());
    }

    public function store(StoreBookingRequest $request)
    {
        Booking::create($request->validated());
        return response()->json("Booking Created");
    }

    public function update(StoreBookingRequest $request, Booking $booking)
    {
        $booking->update($request->validated());
        return response()->json("Booking updated");
    }

    public function show(Booking $booking)
    {
        return new BookingResource($booking);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();
        return response()->json("Booking Deleted");
    }
}
