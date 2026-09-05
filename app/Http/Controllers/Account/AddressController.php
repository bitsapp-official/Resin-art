<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Rules\IndianPhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->latest()->get();

        // Guarantee at least one address is marked default if addresses exist
        if ($addresses->isNotEmpty() && !$addresses->contains('is_default', true)) {
            $addresses->first()->update(['is_default' => true]);
            $addresses = $user->addresses()->orderBy('is_default', 'desc')->latest()->get();
        }

        return view('account.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:shipping,billing'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', new IndianPhoneNumber()],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $validated['type'] = $validated['type'] ?? 'shipping';
        $validated['full_name'] = !empty($validated['full_name']) ? $validated['full_name'] : $user->name;
        $isDefault = $request->boolean('is_default');

        // If user has no existing addresses, force first address to be default
        if ($isDefault || $user->addresses()->count() === 0) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        // Sync user profile phone if user profile phone is empty
        if (empty($user->phone)) {
            $user->update(['phone' => $validated['phone']]);
        }

        $user->addresses()->create($validated);

        return back()->with('success', 'Address added successfully.');
    }

    public function update(Request $request, Address $address)
    {
        $this->authorize('update', $address);

        $validated = $request->validate([
            'type' => ['nullable', 'in:shipping,billing'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', new IndianPhoneNumber()],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $validated['type'] = $validated['type'] ?? 'shipping';
        $validated['full_name'] = !empty($validated['full_name']) ? $validated['full_name'] : $user->name;

        if ($request->boolean('is_default')) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        // Sync user profile phone if user profile phone is empty
        if (empty($user->phone)) {
            $user->update(['phone' => $validated['phone']]);
        }

        $address->update($validated);

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Address $address)
    {
        $this->authorize('delete', $address);
        $wasDefault = $address->is_default;
        $address->delete();

        $user = Auth::user();
        // If deleted address was default, set latest remaining address as default
        if ($wasDefault && $user->addresses()->count() > 0) {
            $user->addresses()->latest()->first()->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed.');
    }

    public function setDefault(Address $address)
    {
        $this->authorize('update', $address);

        $user = Auth::user();
        $user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        // Sync profile phone if empty
        if (empty($user->phone) && !empty($address->phone)) {
            $user->update(['phone' => $address->phone]);
        }

        return back()->with('success', 'Default address updated.');
    }
}


