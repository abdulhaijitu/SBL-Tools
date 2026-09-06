<?php

namespace App\Http\Controllers;

use App\Models\SblContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SblContactController extends Controller
{
    /**
     * Display a listing of SBL contacts, direct helpline, and WhatsApp channels.
     */
    public function index(Request $request): View
    {
        $query = SblContact::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('department', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('badge', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $departments = SblContact::distinct()->pluck('department');

        return view('contacts.index', compact('contacts', 'departments'));
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'available_hours' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['icon'] = $validated['icon'] ?: '📞';
        $validated['available_hours'] = $validated['available_hours'] ?: '10:00 AM - 08:00 PM';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_primary'] = $request->has('is_primary');

        $contact = SblContact::create($validated);

        return redirect()->back()->with('success', "Contact '{$contact->department}' added successfully.");
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, SblContact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'department' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'available_hours' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['icon'] = $validated['icon'] ?: '📞';
        $validated['available_hours'] = $validated['available_hours'] ?: '10:00 AM - 08:00 PM';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_primary'] = $request->has('is_primary');

        $contact->update($validated);

        return redirect()->back()->with('success', "Contact '{$contact->department}' updated successfully.");
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(SblContact $contact): RedirectResponse
    {
        $dept = $contact->department;
        $contact->delete();

        return redirect()->back()->with('success', "Contact '{$dept}' removed successfully.");
    }
}
