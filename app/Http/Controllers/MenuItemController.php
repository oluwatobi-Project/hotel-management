<?php

namespace App\Http\Controllers;

use App\Models\RestaurantMenuItem;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $menuItems = RestaurantMenuItem::orderBy('category')->orderBy('name')->get();
        $categories = RestaurantMenuItem::distinct()->orderBy('category')->pluck('category');

        return view('restaurant.menu_items', compact('menuItems', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $item = RestaurantMenuItem::create($data);

        return $this->respond($request, $item, 'Menu item created.');
    }

    public function update(Request $request, RestaurantMenuItem $menuItem)
    {
        $data = $this->validateData($request);

        $menuItem->update($data);

        return $this->respond($request, $menuItem, 'Menu item updated.');
    }

    public function destroy(Request $request, RestaurantMenuItem $menuItem)
    {
        $menuItem->delete();

        return $this->respond($request, null, 'Menu item deleted.');
    }

    public function toggleAvailability(Request $request, RestaurantMenuItem $menuItem)
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return $this->respond($request, $menuItem, $menuItem->is_available ? 'Menu item is now available.' : 'Menu item is now unavailable.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'is_available' => ['nullable', 'boolean'],
        ]);
    }

    protected function respond(Request $request, ?RestaurantMenuItem $item, string $message, string $level = 'success')
    {
        if ($request->wantsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'success' => $level === 'success',
                'message' => $message,
                'menu_item' => $item,
            ], $level === 'success' ? 200 : 422);
        }

        return back()->with($level, $message);
    }
}
