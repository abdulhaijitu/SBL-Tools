<?php

namespace App\Http\Controllers;

use App\Models\Abbreviation;
use App\Models\BinaryNode;
use App\Models\EcosystemLink;
use App\Models\Lead;
use App\Models\MarketingResource;
use App\Models\SblContact;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * Unified Global Search Endpoint
     * Searches across Navigation Tools, Leads, Team Members, Resources, Abbreviations, Contacts, Links, and Users.
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', $request->input('search', '')));
        if (mb_strlen($q) < 1) {
            return response()->json([
                'query' => $q,
                'total' => 0,
                'results' => [],
                'categories' => [],
            ]);
        }

        $cleanQ = ltrim($q, '@');
        $user = auth()->user();
        $isSuperAdmin = $user && $user->isSuperAdmin();

        $results = [];

        // 1. Navigation Pages & Tools
        $tools = [
            ['title' => 'Dashboard', 'subtitle' => 'System Overview & Quick Stats', 'url' => route('dashboard'), 'icon' => '📊', 'category' => 'Tools', 'badge' => 'Page', 'keywords' => 'home stats analytics overview metrics'],
            ['title' => 'Leads CRM', 'subtitle' => 'Manage Prospect Leads, Stages & Follow-ups', 'url' => route('leads.index'), 'icon' => '👥', 'category' => 'Tools', 'badge' => 'Page', 'keywords' => 'crm prospect follow up kanban table clients'],
            ['title' => 'Packages', 'subtitle' => 'Starter, National & International Packages', 'url' => route('packages.index'), 'icon' => '📦', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'membership drop shipping starter national international trajectory pricing'],
            ['title' => 'Ranks & Earnings', 'subtitle' => 'Official Ranks, Incentives & Career Path', 'url' => route('ranks.index'), 'icon' => '🏆', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'executive manager director president compensation affiliate streams'],
            ['title' => 'Counseling Guide', 'subtitle' => 'Investor vs Networker & Conversion Pitch', 'url' => route('counseling.index'), 'icon' => '🎯', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'sales pitch objection handling conversion script psychology'],
            ['title' => 'Commission Calculator', 'subtitle' => '100-Week ROI & 10-Gen Matrix Simulation', 'url' => route('commission.index'), 'icon' => '🧮', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'roi calculator simulation earnings payout binary projection'],
            ['title' => 'Official Links', 'subtitle' => 'SBL Web Directory, Portals & Stores', 'url' => route('links.index'), 'icon' => '🔗', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'websites ecosystem portal shop login store link'],
            ['title' => 'Marketing Resources', 'subtitle' => 'Official Leaflets, Slides, PDF & Brochures', 'url' => route('resources.index'), 'icon' => '📁', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'leaflet brochure catalog pdf documents download official marketing'],
            ['title' => 'Team Explorer', 'subtitle' => '10-Slot Placement Engine, Mindmap & Directory', 'url' => route('team.index'), 'icon' => '👥', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'network binary tree mindmap genealogy placement member downline'],
            ['title' => 'Abbreviation & Glossary', 'subtitle' => 'SBL Terminology & Short Forms Directory', 'url' => route('abbreviations.index'), 'icon' => '📖', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'glossary terms dictionary abbreviation meanings definitions'],
            ['title' => 'Official Contacts', 'subtitle' => 'Support Hotlines & WhatsApp Directory', 'url' => route('contacts.index'), 'icon' => '📞', 'category' => 'Tools', 'badge' => 'Marketing', 'keywords' => 'helpline whatsapp phone support hotline desk customer care'],
        ];

        if ($isSuperAdmin) {
            $tools[] = ['title' => 'Users & Accounts', 'subtitle' => 'Manage system users, login credentials & roles', 'url' => route('users.index'), 'icon' => '👤', 'category' => 'Admin', 'badge' => 'Admin', 'keywords' => 'users admin roles credentials staff affiliate account'];
            $tools[] = ['title' => 'Roles & Permissions', 'subtitle' => 'Configure RBAC access permissions', 'url' => route('roles.index'), 'icon' => '🛡️', 'category' => 'Admin', 'badge' => 'Admin', 'keywords' => 'security roles permissions access rbac'];
        }

        foreach ($tools as $tool) {
            if (
                stripos($tool['title'], $q) !== false ||
                stripos($tool['subtitle'], $q) !== false ||
                stripos($tool['keywords'], $q) !== false
            ) {
                $results[] = $tool;
            }
        }

        // 2. Leads (Scope to user if not super admin)
        $leadsQuery = Lead::query();
        if (!$isSuperAdmin && $user) {
            $leadsQuery->where(function ($b) use ($user) {
                $b->where('assigned_to', $user->id)
                  ->orWhere('owner_user_id', $user->id);
            });
        }
        $leads = $leadsQuery->where(function ($b) use ($q) {
            $b->where('name', 'like', "%{$q}%")
                ->orWhere('mobile', 'like', "%{$q}%")
                ->orWhere('whatsapp', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('location', 'like', "%{$q}%");
        })->limit(6)->get();

        foreach ($leads as $lead) {
            $phone = $lead->mobile ?: $lead->whatsapp ?: '';
            $sub = $phone;
            if ($lead->location) $sub .= ($sub ? ' • ' : '') . $lead->location;
            if ($lead->profession_or_business) $sub .= ($sub ? ' • ' : '') . $lead->profession_or_business;

            $results[] = [
                'title' => $lead->name,
                'subtitle' => $sub ?: 'Lead',
                'url' => route('leads.show', $lead->id),
                'icon' => '👤',
                'category' => 'Leads',
                'badge' => $lead->stage ? $lead->stage->label() : 'Lead',
            ];
        }

        // 3. Team Members (Binary Nodes)
        $teamQuery = BinaryNode::query();
        if (!$isSuperAdmin && $user) {
            $teamQuery->where('tree_owner_id', $user->id);
        }
        $nodes = $teamQuery->where(function ($b) use ($q, $cleanQ) {
            $b->where('member_name', 'like', "%{$q}%")
                ->orWhere('member_code', 'like', "%{$q}%")
                ->orWhere('member_code', 'like', "%{$cleanQ}%")
                ->orWhere('phone', 'like', "%{$q}%");
        })->limit(6)->get();

        foreach ($nodes as $node) {
            $sub = "Code: {$node->member_code}";
            if ($node->phone) $sub .= " • Phone: {$node->phone}";
            if ($node->rank_title) $sub .= " • {$node->rank_title}";

            $results[] = [
                'title' => $node->member_name,
                'subtitle' => $sub,
                'url' => route('team.show', ['memberId' => $node->id]),
                'icon' => '🌳',
                'category' => 'Team',
                'badge' => $node->member_code,
            ];
        }

        // 4. Marketing Resources
        $resources = MarketingResource::where('is_active', true)
            ->where(function ($b) use ($q) {
                $b->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            })->limit(5)->get();

        foreach ($resources as $res) {
            $results[] = [
                'title' => $res->title,
                'subtitle' => ucfirst($res->category) . ($res->description ? " • " . Str::limit($res->description, 50) : ''),
                'url' => route('resources.index'),
                'icon' => '📁',
                'category' => 'Resources',
                'badge' => ucfirst($res->category),
            ];
        }

        // 5. Abbreviations
        $abbreviations = Abbreviation::where(function ($b) use ($q) {
            $b->where('term', 'like', "%{$q}%")
                ->orWhere('meaning', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%");
        })->limit(5)->get();

        foreach ($abbreviations as $abbr) {
            $results[] = [
                'title' => "{$abbr->term} - {$abbr->meaning}",
                'subtitle' => $abbr->description ?: 'SBL Terminology',
                'url' => route('abbreviations.index'),
                'icon' => '📖',
                'category' => 'Abbreviations',
                'badge' => $abbr->term,
            ];
        }

        // 6. Helplines & Contacts
        $contacts = SblContact::where('is_active', true)
            ->where(function ($b) use ($q) {
                $b->where('department', 'like', "%{$q}%")
                    ->orWhere('contact_person', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })->limit(5)->get();

        foreach ($contacts as $contact) {
            $results[] = [
                'title' => $contact->department . ($contact->contact_person ? " ({$contact->contact_person})" : ''),
                'subtitle' => "📞 {$contact->phone}" . ($contact->whatsapp ? " • WA: {$contact->whatsapp}" : ''),
                'url' => route('contacts.index'),
                'icon' => '📞',
                'category' => 'Contacts',
                'badge' => $contact->badge ?: 'Support',
            ];
        }

        // 7. Official Links
        $links = EcosystemLink::where('is_active', true)
            ->where(function ($b) use ($q) {
                $b->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhere('url', 'like', "%{$q}%");
            })->limit(5)->get();

        foreach ($links as $link) {
            $results[] = [
                'title' => $link->title,
                'subtitle' => $link->url . ($link->description ? " • " . Str::limit($link->description, 45) : ''),
                'url' => $link->url,
                'icon' => '🔗',
                'category' => 'Links',
                'badge' => ucfirst($link->category),
                'external' => true,
            ];
        }

        // Group by category for quick client organization
        $categories = [];
        foreach ($results as $item) {
            $cat = $item['category'];
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $categories[$cat][] = $item;
        }

        return response()->json([
            'query' => $q,
            'total' => count($results),
            'results' => $results,
            'categories' => $categories,
        ]);
    }
}
