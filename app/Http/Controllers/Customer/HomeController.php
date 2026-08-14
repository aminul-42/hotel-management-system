<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\RoomType;

class HomeController extends Controller
{
    public function index()
    {
        $roomTypes = RoomType::where('is_active', true)
            ->orderBy('name')
            ->get();

        $facilities = Facility::where('is_active', true)
            ->orderBy('sort_order')
            ->take(6)
            ->get();

        // Pull a handful of real room photos for the closing marquee slider
        $roomGalleryImages = $roomTypes->flatMap(function ($rt) {
                return collect($rt->image_urls ?? [])->take(2)->map(fn ($url) => [
                    'url' => $url,
                    'label' => $rt->name,
                ]);
            })
            ->take(10)
            ->values();

        return view('customer.home', compact('roomTypes', 'facilities', 'roomGalleryImages'));
    }
}