<?php

namespace App\Http\Controllers;

use App\Models\BinaryNode;
use App\Models\User;
use App\Services\BinaryTreeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BinaryTeamController extends Controller
{
    protected BinaryTreeService $treeService;

    public function __construct(BinaryTreeService $treeService)
    {
        $this->treeService = $treeService;
    }

    /**
     * Display visual binary tree canvas & team metrics.
     */
    public function index(Request $request): View
    {
        $viewMode = $request->query('view', 'tree'); // 'tree' or 'table'
        $nodeId = $request->query('node_id');
        $treeData = $this->treeService->getVisualTree($nodeId ? (int)$nodeId : null, 3);

        $packages = [
            ['name' => 'National 120k', 'price' => 120000, 'bv' => 100, 'label' => 'ন্যাশনাল প্যাকেজ (১২০,০০০/-) - ১০০ BV'],
            ['name' => 'International 550k', 'price' => 550000, 'bv' => 500, 'label' => 'ইন্টারন্যাশনাল প্যাকেজ (৫৫০,০০০/-) - ৫০০ BV'],
            ['name' => 'Executive Starter', 'price' => 25000, 'bv' => 25, 'label' => 'স্টার্টার প্যাক (২৫,০০০/-) - ২৫ BV'],
        ];

        $allNodes = BinaryNode::orderBy('member_name')->get(['id', 'member_name', 'member_code', 'rank_name']);
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'phone']);

        $tableQuery = BinaryNode::with(['parent', 'user', 'children'])->orderBy('id');
        if ($search = $request->input('search')) {
            $tableQuery->where(function ($q) use ($search) {
                $q->where('member_name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $members = $tableQuery->paginate(15)->withQueryString();

        return view('binary.index', compact('treeData', 'packages', 'allNodes', 'users', 'viewMode', 'members'));
    }

    /**
     * Update an existing team member in the binary tree.
     */
    public function update(Request $request, BinaryNode $node): RedirectResponse
    {
        $validated = $request->validate([
            'member_name' => 'required|string|max:150',
            'member_code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'package_name' => 'nullable|string|max:100',
            'point_value' => 'nullable|numeric|min:0',
            'rank_name' => 'nullable|string|max:50',
            'sponsor_id' => 'nullable|exists:binary_nodes,id',
            'left_count' => 'nullable|integer|min:0',
            'right_count' => 'nullable|integer|min:0',
            'left_bv' => 'nullable|numeric|min:0',
            'right_bv' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;

        try {
            $this->treeService->updateMember($node, $validated);

            return redirect()->back()
                ->with('success', "মেম্বার '{$node->member_name}' ({$node->member_code})-এর তথ্য সফলভাবে আপডেট করা হয়েছে।");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a member from the binary tree (leaf nodes only).
     */
    public function destroy(Request $request, BinaryNode $node): RedirectResponse
    {
        try {
            $name = $node->member_name;
            $code = $node->member_code;
            $parentId = $node->parent_id;

            $this->treeService->deleteNode($node);

            $targetUrl = $parentId ? route('binary.index', ['node_id' => $parentId]) : route('binary.index');

            return redirect($targetUrl)
                ->with('success', "মেম্বার '{$name}' ({$code}) সফলভাবে টিম থেকে রিমুভ করা হয়েছে এবং আপলাইন ভলিউম আপডেট করা হয়েছে।");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Handle visual placement of a member into the binary tree.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'member_name' => 'required|string|max:150',
            'member_code' => 'nullable|string|max:50|unique:binary_nodes,member_code',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'parent_id' => 'required|exists:binary_nodes,id',
            'sponsor_id' => 'nullable|exists:binary_nodes,id',
            'position' => 'required|in:left,right',
            'package_name' => 'required|string|max:100',
            'user_id' => 'nullable|exists:users,id',
            'rank_name' => 'nullable|string|max:50',
        ]);

        try {
            $node = $this->treeService->placeMember($validated);
            $parent = BinaryNode::find($validated['parent_id']);
            $posText = $validated['position'] === 'left' ? 'বাম টিমে (Left)' : 'ডান টিমে (Right)';

            return redirect()->route('binary.index', ['node_id' => $parent->id])
                ->with('success', "মেম্বার '{$node->member_name}' ({$node->member_code}) সফলভাবে {$parent->member_name}-এর {$posText} যুক্ত করা হয়েছে।");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Search member by name, code, or phone to focus the visual tree.
     */
    public function search(Request $request): RedirectResponse
    {
        $query = $request->input('search');
        if (! $query) {
            return redirect()->route('binary.index');
        }

        $node = BinaryNode::where('member_code', 'like', "%{$query}%")
            ->orWhere('member_name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->first();

        if ($node) {
            return redirect()->route('binary.index', ['node_id' => $node->id]);
        }

        return redirect()->back()->with('error', "'{$query}' দিয়ে কোনো টিম মেম্বার খুঁজে পাওয়া যায়নি।");
    }

    /**
     * Navigate to extreme left or right branch.
     */
    public function extreme(Request $request, BinaryNode $node, string $direction): RedirectResponse
    {
        if ($direction === 'left') {
            $target = $this->treeService->getExtremeLeft($node);
        } else {
            $target = $this->treeService->getExtremeRight($node);
        }

        return redirect()->route('binary.index', ['node_id' => $target->id]);
    }
}
