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
     * Display visual 10-slot (5L + 5R) Team Explorer & member directory.
     */
    public function index(Request $request, $memberId = null): View
    {
        $currentUser = auth()->user();
        $isSuperAdmin = $currentUser?->isSuperAdmin() ?? false;

        // Determine active workspace tree owner
        $ownerId = null;
        if ($isSuperAdmin && $request->filled('owner_id')) {
            $ownerId = (int)$request->input('owner_id');
        } elseif ($currentUser) {
            $ownerId = $currentUser->id;
        }

        // Auto-ensure user root exists if authenticated
        if ($currentUser && ! BinaryNode::where('tree_owner_id', $currentUser->id)->whereNull('parent_id')->exists()) {
            $this->treeService->ensureUserRoot($currentUser);
        }

        $viewMode = $request->query('view', 'tree');
        $nodeId = $memberId ?: ($request->query('node_id') ?: $request->query('member_id'));
        if ($nodeId) {
            $requestedNode = BinaryNode::findOrFail($nodeId);
            if ($isSuperAdmin && !$request->filled('owner_id')) $ownerId = $requestedNode->tree_owner_id;
            abort_unless((int)$requestedNode->tree_owner_id === (int)$ownerId, 404);
        }
        $treeData = $this->treeService->getVisualTree($nodeId ? (int)$nodeId : null, $ownerId, 2);

        $packages = [
            ['name' => 'National 120k', 'price' => 120000, 'bv' => 100, 'label' => 'ন্যাশনাল প্যাকেজ (১২০,০০০/-) - ১০০ BV'],
            ['name' => 'International 550k', 'price' => 550000, 'bv' => 500, 'label' => 'ইন্টারন্যাশনাল প্যাকেজ (৫৫০,০০০/-) - ৫০০ BV'],
            ['name' => 'Executive Starter', 'price' => 25000, 'bv' => 25, 'label' => 'স্টার্টার প্যাক (২৫,০০০/-) - ২৫ BV'],
        ];

        $nodesQuery = BinaryNode::orderBy('member_name');
        if ($ownerId) {
            $nodesQuery->where('tree_owner_id', $ownerId);
        }
        $allNodes = $nodesQuery->get(['id', 'member_name', 'member_code', 'rank_name', 'branch', 'slot_number']);
        $users = User::when(!$isSuperAdmin, fn($query) => $query->whereKey($currentUser->id))->orderBy('name')->get(['id', 'name', 'email', 'phone']);

        $tableQuery = BinaryNode::with(['parent', 'user', 'children', 'investments'])->orderBy('id');
        if ($ownerId) {
            $tableQuery->where('tree_owner_id', $ownerId);
        }

        if ($search = $request->input('search')) {
            $tableQuery->where(function ($q) use ($search) {
                $q->where('member_name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        $members = $tableQuery->paginate(15)->withQueryString();

        return view('binary.index', compact('treeData', 'packages', 'allNodes', 'users', 'viewMode', 'members', 'ownerId', 'isSuperAdmin'));
    }

    /**
     * Show Team Explorer for a specific member ID.
     */
    public function show(Request $request, $memberId): View
    {
        return $this->index($request, (int)$memberId);
    }

    public function credentials(BinaryNode $node): \Illuminate\Http\JsonResponse
    {
        // Scoped model binding guarantees the member belongs to the caller's tree.
        return response()->json([
            'password_plain' => $node->password_plain,
            'tpin' => $node->tpin,
        ])->header('Cache-Control', 'no-store, private');
    }

    /**
     * Handle placement of a member into a specific slot (1-5) on LEFT or RIGHT.
     */
    public function store(Request $request): RedirectResponse
    {
        // Support connector_id or connector_code/name for placement parent
        if ($request->filled('connector_id')) {
            $request->merge(['parent_id' => $request->input('connector_id')]);
        } elseif ($request->filled('connector_code') || $request->filled('connector_name')) {
            $cVal = trim($request->input('connector_code') ?: $request->input('connector_name'));
            $matchedParent = BinaryNode::where('member_code', $cVal)->orWhere('member_name', $cVal)->first();
            if ($matchedParent) {
                $request->merge(['parent_id' => $matchedParent->id]);
            }
        }

        // Default to current tree root if parent_id is still missing
        if (! $request->filled('parent_id')) {
            $root = $this->treeService->getMainTeamRoot(auth()->id());
            if ($root) {
                $request->merge(['parent_id' => $root->id]);
            }
        }

        if ($request->filled('sponsor_code')) {
            $sCode = trim($request->input('sponsor_code'));
            $matchedSponsor = BinaryNode::where('member_code', $sCode)->orWhere('member_name', $sCode)->first();
            if ($matchedSponsor) {
                $request->merge(['sponsor_id' => $matchedSponsor->id, 'sponsor_name' => $matchedSponsor->member_name]);
            }
        }

        if ($request->filled('sponsor_name')) {
            $sName = trim($request->input('sponsor_name'));
            $matchedNode = BinaryNode::where('member_name', $sName)
                ->orWhere('member_code', $sName)
                ->first();
            if ($matchedNode) {
                $request->merge(['sponsor_id' => $matchedNode->id, 'sponsor_name' => $matchedNode->member_name]);
            }
        } elseif ($request->input('sponsor_id') === '' || $request->input('sponsor_id') === '0') {
            $request->merge(['sponsor_id' => null, 'sponsor_name' => null]);
        }

        // Support position or branch
        $branch = $request->input('branch') ?: $request->input('position');
        $branch = strtoupper((string)$branch);
        $request->merge(['branch' => $branch]);

        $slotNumber = (int)($request->input('slot_number') ?: ($request->input('position_slot') ?: 1));
        $request->merge(['slot_number' => $slotNumber]);

        $validated = $request->validate([
            'member_name' => 'required|string|max:150',
            'member_code' => 'nullable|string|max:50|unique:binary_nodes,member_code',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'password_plain' => 'nullable|string|max:100',
            'tpin' => 'nullable|string|max:20',
            'parent_id' => 'required|exists:binary_nodes,id',
            'sponsor_id' => 'nullable|integer|exists:binary_nodes,id',
            'sponsor_name' => 'nullable|string|max:150',
            'branch' => 'required|in:LEFT,RIGHT',
            'slot_number' => 'required|integer|between:1,5',
            'package_name' => 'required|string|max:100',
            'user_id' => 'nullable|exists:users,id',
            'rank_name' => 'nullable|string|max:50',
            'is_target' => 'nullable|boolean',
            'target_date' => 'nullable|date',
            'target_notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $parentNode = BinaryNode::findOrFail($validated['parent_id']);
        if (!empty($validated['sponsor_id'])) BinaryNode::where('tree_owner_id', $parentNode->tree_owner_id)->findOrFail($validated['sponsor_id']);
        if (!auth()->user()->isSuperAdmin() && !empty($validated['user_id'])) abort_unless((int)$validated['user_id'] === auth()->id(), 403);
        $validated['is_target'] = $request->boolean('is_target');

        try {
            $node = $this->treeService->placeMember($validated);
            $parent = BinaryNode::find($validated['parent_id']);
            $branchText = $validated['branch'] === 'LEFT' ? 'বাম টিমে (Left)' : 'ডান টিমে (Right)';
            $slotText = "{$branchText} স্লট-{$validated['slot_number']}";

            $msg = $node->is_target
                ? "পরিকল্পিত টার্গেট মেম্বার '{$node->member_name}' সফলভাবে {$parent->member_name}-এর {$slotText}-এ সংরক্ষিত হয়েছে।"
                : "মেম্বার '{$node->member_name}' ({$node->member_code}) সফলভাবে {$parent->member_name}-এর {$slotText}-এ যুক্ত করা হয়েছে।";

            return redirect()->route('team.show', ['memberId' => $parent->id])
                ->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Convert target member to confirmed active member.
     */
    public function convertTarget(Request $request, BinaryNode $node): RedirectResponse
    {
        try {
            $this->treeService->convertToActive($node);
            return redirect()->back()->with('success', "মেম্বার '{$node->member_name}' সফলভাবে টার্গেট থেকে অ্যাক্টিভ মেম্বারে রূপান্তরিত হয়েছে।");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing team member.
     */
    public function update(Request $request, BinaryNode $node): RedirectResponse
    {
        if ($request->filled('sponsor_name')) {
            $sName = trim($request->input('sponsor_name'));
            $matchedNode = BinaryNode::where('member_name', $sName)
                ->orWhere('member_code', $sName)
                ->first();
            if ($matchedNode) {
                $request->merge(['sponsor_id' => $matchedNode->id, 'sponsor_name' => $matchedNode->member_name]);
            }
        } elseif ($request->input('sponsor_id') === '' || $request->input('sponsor_id') === '0') {
            $request->merge(['sponsor_id' => null, 'sponsor_name' => null]);
        }

        if ($request->input('user_id') === '' || $request->input('user_id') === '0') {
            $request->merge(['user_id' => null]);
        }

        $validated = $request->validate([
            'member_name' => 'required|string|max:150',
            'member_code' => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('binary_nodes', 'member_code')->ignore($node->id)],
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'password_plain' => 'nullable|string|max:100',
            'tpin' => 'nullable|string|max:20',
            'package_name' => 'nullable|string|max:100',
            'point_value' => 'nullable|numeric|min:0',
            'contributions' => 'nullable',
            'rank_name' => 'nullable|string|max:50',
            'sponsor_id' => 'nullable|integer|exists:binary_nodes,id',
            'sponsor_name' => 'nullable|string|max:150',
            'is_active' => 'nullable|boolean',
            'is_target' => 'nullable|boolean',
            'target_date' => 'nullable|date',
            'target_notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:2000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->has('is_active') ? (bool)$request->input('is_active') : true;
        if (!empty($validated['sponsor_id'])) BinaryNode::where('tree_owner_id', $node->tree_owner_id)->findOrFail($validated['sponsor_id']);
        if (!auth()->user()->isSuperAdmin() && !empty($validated['user_id'])) abort_unless((int)$validated['user_id'] === auth()->id(), 403);
        $validated['is_target'] = $request->boolean('is_target');

        try {
            $this->treeService->updateMember($node, $validated);

            return redirect()->back()
                ->with('success', "মেম্বার '{$node->member_name}' ({$node->member_code})-এর তথ্য সফলভাবে আপডেট করা হয়েছে।");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a member from the team.
     */
    public function destroy(Request $request, BinaryNode $node): RedirectResponse
    {
        try {
            $name = $node->member_name;
            $code = $node->member_code;
            $parentId = $node->parent_id;
            $cascade = $request->boolean('cascade', false) || $request->has('force');

            $this->treeService->deleteNode($node, $cascade);

            $targetUrl = $parentId ? route('team.show', ['memberId' => $parentId]) : route('team.index');

            return redirect($targetUrl)
                ->with('success', "মেম্বার '{$name}' ({$code}) সফলভাবে টিম থেকে রিমুভ করা হয়েছে।");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Search member by name, code, phone, or username to open in Team Explorer.
     */
    public function search(Request $request): RedirectResponse
    {
        $query = trim((string)$request->input('search'));
        if (! $query) {
            return redirect()->route('team.index');
        }

        $cleanQuery = ltrim($query, '@');

        $ownerId = auth()->user()->isSuperAdmin() ? ($request->integer('owner_id') ?: auth()->id()) : auth()->id();
        $node = BinaryNode::where('tree_owner_id', $ownerId)
            ->where(function ($builder) use ($query, $cleanQuery) {
                $builder->where('member_code', 'like', "%{$query}%")
                    ->orWhere('member_code', 'like', "%{$cleanQuery}%")
                    ->orWhere('member_name', 'like', "%{$query}%")
                    ->orWhere('phone', 'like', "%{$query}%");
            })
            ->first();

        if ($node) {
            return redirect()->route('team.show', ['memberId' => $node->id]);
        }

        return redirect()->back()->with('error', "'{$query}' দিয়ে কোনো টিম মেম্বার খুঁজে পাওয়া যায়নি।");
    }

    /**
     * Navigate extreme direction (left or right).
     */
    public function extreme(Request $request, BinaryNode $node, string $direction): RedirectResponse
    {
        $target = $direction === 'left'
            ? $this->treeService->getExtremeLeft($node)
            : $this->treeService->getExtremeRight($node);

        return redirect()->route('team.show', ['memberId' => $target->id]);
    }

    /**
     * Update notes for a team member (direct save or AJAX).
     */
    public function updateNotes(Request $request, BinaryNode $node)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        $node->update([
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Note updated successfully.',
                'notes' => $node->notes,
            ]);
        }

        return redirect()->back()->with('success', 'Note updated successfully.');
    }
}
