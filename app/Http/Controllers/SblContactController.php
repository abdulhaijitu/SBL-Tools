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

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('department', 'like', "%{$search}%")
                    ->orWhere('department_en', 'like', "%{$search}%")
                    ->orWhere('department_bn', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('description_en', 'like', "%{$search}%")
                    ->orWhere('description_bn', 'like', "%{$search}%")
                    ->orWhere('badge', 'like', "%{$search}%")
                    ->orWhere('service_label_en', 'like', "%{$search}%")
                    ->orWhere('service_label_bn', 'like', "%{$search}%");
            });
        }

        $contacts = $query->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $departments = SblContact::distinct()->pluck('department');
        $activeCount = SblContact::where('is_active', true)->count();
        $categories = [
            'all' => ['en' => 'All Contacts', 'bn' => 'সকল সাপোর্ট'],
            'customer_care' => ['en' => 'Customer Care', 'bn' => 'কাস্টমার কেয়ার'],
            'dropshipping' => ['en' => 'Dropshipping', 'bn' => 'ড্রপশিপিং'],
            'business' => ['en' => 'Business & Investor', 'bn' => 'বিজনেস ও ইনভেস্টর'],
            'technical' => ['en' => 'Technical Support', 'bn' => 'টেকনিক্যাল সাপোর্ট'],
            'training' => ['en' => 'Training & Counseling', 'bn' => 'ট্রেনিং ও কাউন্সেলিং'],
            'accounts' => ['en' => 'Accounts & Payout', 'bn' => 'অ্যাকাউন্টস ও পে-আউট'],
        ];

        return view('contacts.index', compact('contacts', 'departments', 'activeCount', 'categories'));
    }

    /**
     * Store a newly created contact in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department' => 'nullable|string|max:150',
            'department_en' => 'nullable|string|max:150',
            'department_bn' => 'nullable|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'available_hours' => 'nullable|string|max:100',
            'hours_en' => 'nullable|string|max:100',
            'hours_bn' => 'nullable|string|max:100',
            'days' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'description_bn' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'service_label_en' => 'nullable|string|max:50',
            'service_label_bn' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'verification_status' => 'nullable|string|in:verified,needs_review,unverified,inactive',
            'is_official' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_24_hours' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'open_time' => 'nullable|string|max:20',
            'close_time' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $deptEn = $validated['department_en'] ?? $validated['department'] ?? 'Department';
        $deptBn = $validated['department_bn'] ?? $deptEn;

        $validated['department'] = $deptEn;
        $validated['department_en'] = $deptEn;
        $validated['department_bn'] = $deptBn;
        $validated['icon'] = !empty($validated['icon']) ? $validated['icon'] : '📞';
        $validated['available_hours'] = !empty($validated['available_hours']) ? $validated['available_hours'] : '10:00 AM - 08:00 PM';
        $validated['hours_en'] = !empty($validated['hours_en']) ? $validated['hours_en'] : $validated['available_hours'];
        $validated['hours_bn'] = !empty($validated['hours_bn']) ? $validated['hours_bn'] : $validated['available_hours'];
        $validated['service_label_en'] = $validated['service_label_en'] ?? $validated['badge'] ?? null;
        $validated['service_label_bn'] = $validated['service_label_bn'] ?? $validated['service_label_en'];
        $validated['description_en'] = $validated['description_en'] ?? $validated['description'] ?? null;
        $validated['description_bn'] = $validated['description_bn'] ?? $validated['description_en'];
        $validated['category'] = $validated['category'] ?? 'customer_care';
        $validated['verification_status'] = $validated['verification_status'] ?? 'needs_review';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['is_official'] = $request->boolean('is_official');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['is_public'] = $request->has('is_public') ? $request->boolean('is_public') : true;
        $validated['is_24_hours'] = $request->boolean('is_24_hours');

        $contact = SblContact::create($validated);

        return redirect()->back()->with('success', "Contact '{$contact->department_en}' added successfully.");
    }

    /**
     * Update the specified contact in storage.
     */
    public function update(Request $request, SblContact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'department' => 'nullable|string|max:150',
            'department_en' => 'nullable|string|max:150',
            'department_bn' => 'nullable|string|max:150',
            'contact_person' => 'nullable|string|max:150',
            'phone' => 'required|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'available_hours' => 'nullable|string|max:100',
            'hours_en' => 'nullable|string|max:100',
            'hours_bn' => 'nullable|string|max:100',
            'days' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'description_bn' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'badge' => 'nullable|string|max:50',
            'service_label_en' => 'nullable|string|max:50',
            'service_label_bn' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'verification_status' => 'nullable|string|in:verified,needs_review,unverified,inactive',
            'is_official' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_24_hours' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'open_time' => 'nullable|string|max:20',
            'close_time' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $deptEn = $validated['department_en'] ?? $validated['department'] ?? $contact->department_en ?? $contact->department;
        $deptBn = $validated['department_bn'] ?? $contact->department_bn ?? $deptEn;

        $validated['department'] = $deptEn;
        $validated['department_en'] = $deptEn;
        $validated['department_bn'] = $deptBn;
        $validated['icon'] = !empty($validated['icon']) ? $validated['icon'] : ($contact->icon ?: '📞');
        $validated['available_hours'] = !empty($validated['available_hours']) ? $validated['available_hours'] : ($contact->available_hours ?: '10:00 AM - 08:00 PM');
        $validated['hours_en'] = !empty($validated['hours_en']) ? $validated['hours_en'] : ($contact->hours_en ?: $validated['available_hours']);
        $validated['hours_bn'] = !empty($validated['hours_bn']) ? $validated['hours_bn'] : ($contact->hours_bn ?: $validated['available_hours']);
        $validated['service_label_en'] = $validated['service_label_en'] ?? $validated['badge'] ?? $contact->service_label_en ?? $contact->badge;
        $validated['service_label_bn'] = $validated['service_label_bn'] ?? $contact->service_label_bn ?? $validated['service_label_en'];
        $validated['description_en'] = $validated['description_en'] ?? $validated['description'] ?? $contact->description_en ?? $contact->description;
        $validated['description_bn'] = $validated['description_bn'] ?? $contact->description_bn ?? $validated['description_en'];
        $validated['category'] = $validated['category'] ?? $contact->category ?? 'customer_care';
        $validated['verification_status'] = $validated['verification_status'] ?? $contact->verification_status ?? 'needs_review';
        $validated['sort_order'] = $validated['sort_order'] ?? $contact->sort_order ?? 0;
        $validated['is_primary'] = $request->boolean('is_primary');
        $validated['is_official'] = $request->boolean('is_official');
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['is_public'] = $request->has('is_public') ? $request->boolean('is_public') : true;
        $validated['is_24_hours'] = $request->boolean('is_24_hours');

        $contact->update($validated);

        return redirect()->back()->with('success', "Contact '{$contact->department_en}' updated successfully.");
    }

    /**
     * Remove the specified contact from storage.
     */
    public function destroy(SblContact $contact): RedirectResponse
    {
        $dept = $contact->department;
        $dept = $contact->department_en ?: $contact->department;
        $contact->delete();

        return redirect()->back()->with('success', "Contact '{$dept}' removed successfully.");
    }
}
