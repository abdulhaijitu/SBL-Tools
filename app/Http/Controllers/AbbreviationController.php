<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AbbreviationController extends Controller
{
    /**
     * Display a listing of SBL Dropshipping & E-Commerce Abbreviations.
     */
    public function index(Request $request): View
    {
        $abbreviations = \App\Models\Abbreviation::orderBy('id')->get()->toArray();

        $categories = [
            ['slug' => 'all', 'name' => 'All Terms', 'icon' => '✨', 'count' => count($abbreviations)],
            ['slug' => 'ecommerce', 'name' => 'E-Commerce Core', 'icon' => '🛒', 'count' => count(array_filter($abbreviations, fn($a) => $a['category_slug'] === 'ecommerce'))],
            ['slug' => 'marketing', 'name' => 'Marketing & Ads', 'icon' => '🎯', 'count' => count(array_filter($abbreviations, fn($a) => $a['category_slug'] === 'marketing'))],
            ['slug' => 'logistics', 'name' => 'Logistics & Delivery', 'icon' => '🚚', 'count' => count(array_filter($abbreviations, fn($a) => $a['category_slug'] === 'logistics'))],
            ['slug' => 'network', 'name' => 'SBL Network & System', 'icon' => '🌐', 'count' => count(array_filter($abbreviations, fn($a) => $a['category_slug'] === 'network'))],
            ['slug' => 'finance', 'name' => 'Finance & Operations', 'icon' => '💼', 'count' => count(array_filter($abbreviations, fn($a) => $a['category_slug'] === 'finance'))],
        ];

        return view('abbreviations.index', compact('abbreviations', 'categories'));
    }
    public function store(Request $request)
    {
        $term = \App\Models\Abbreviation::create($this->validated($request));
        return response()->json($term, 201);
    }

    public function update(Request $request, \App\Models\Abbreviation $abbreviation)
    {
        $abbreviation->update($this->validated($request, $abbreviation->id));
        return response()->json($abbreviation);
    }

    public function destroy(\App\Models\Abbreviation $abbreviation)
    {
        $abbreviation->delete();
        return response()->noContent();
    }

    private function validated(Request $request, ?int $id = null): array
    {
        if (! $request->filled('category_slug')) {
            $request->merge(['category_slug' => 'ecommerce']);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('abbreviations')->ignore($id)],
            'name' => 'required|string|max:200',
            'category_slug' => 'required|in:ecommerce,marketing,logistics,network,finance',
            'meaning_bn' => 'required|string|max:1000',
            'description_bn' => 'required|string|max:3000',
            'icon' => 'nullable|string|max:20',
            'tag' => 'nullable|string|max:100',
        ]);
        $data['category'] = ['ecommerce' => 'E-Commerce Core', 'marketing' => 'Marketing & Ads', 'logistics' => 'Logistics & Delivery', 'network' => 'SBL Network & System', 'finance' => 'Finance & Operations'][$data['category_slug']];
        $data['icon'] = ($data['icon'] ?? '') ?: '📖';
        $data['tag'] = $data['tag'] ?? '';
        return $data;
    }
}
