<?php

namespace Database\Seeders;

use App\Enums\ContentPlatform;
use App\Enums\ContentStatus;
use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use App\Enums\PresentationOutcome;
use App\Enums\PresentationType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Activity;
use App\Models\Campaign;
use App\Models\ContentItem;
use App\Models\Lead;
use App\Models\LeadInterest;
use App\Models\LeadSource;
use App\Models\Presentation;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Roles, Permissions, and Team Structure
        $this->call([
            RoleAndPermissionSeeder::class,
            MemberRoleSeeder::class,
            EcosystemLinkSeeder::class,
            SblContactSeeder::class,
            MarketingResourceSeeder::class,
            SblPdfDataSeeder::class,
            BinaryTeamSeeder::class,
        ]);

        // 1. Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@sbl.test'],
            [
                'name' => 'SBL Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Default Lead Sources
        $sources = [
            'Facebook Profile',
            'Facebook Page',
            'Reel',
            'Story',
            'Messenger',
            'WhatsApp',
            'Referral',
            'Offline Meeting',
            'Event',
            'Existing Client',
            'Personal Network',
            'Other',
        ];

        $sourceModels = [];
        foreach ($sources as $index => $sourceName) {
            $sourceModels[$sourceName] = LeadSource::firstOrCreate(
                ['name' => $sourceName],
                ['order' => $index + 1, 'is_active' => true]
            );
        }

        // 3. Campaigns
        $campaign = Campaign::firstOrCreate(
            ['name' => 'Q3 E-commerce & Dropshipping Growth'],
            [
                'platform' => 'Facebook & Reels',
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'budget' => 25000.00,
                'status' => 'Active',
                'notes' => 'Targeting young entrepreneurs and aspiring dropshippers.',
            ]
        );

        // 4. Sample Leads
        $leadsData = [
            [
                'name' => 'Md. Nayim',
                'mobile' => '01777000001',
                'whatsapp' => '01777000001',
                'email' => 'nayim@example.com',
                'facebook_url' => 'https://facebook.com/nayim.demo',
                'location' => 'Dhaka, Mirpur',
                'profession_or_business' => 'Entrepreneur',
                'lead_source' => 'Facebook Page',
                'interests' => ['E-commerce', 'Dropshipping'],
                'lead_tag' => null,
                'stage' => LeadStage::INTERESTED,
                'temperature' => LeadTemperature::WARM,
                'score' => 65,
                'budget_range' => '50,000 - 100,000 BDT',
                'decision_timeline' => 'Within 15 Days',
                'next_action_type' => 'Follow-up Call',
                'next_action_at' => now()->addDay()->setHour(11)->setMinute(0),
                'last_contact_at' => now()->subDay(),
                'notes' => 'Looking to expand his business to online dropshipping.',
            ],
            [
                'name' => 'Farhana Akter',
                'mobile' => '01812334455',
                'whatsapp' => '01812334455',
                'email' => 'farhana@example.com',
                'facebook_url' => 'https://facebook.com/farhana.demo',
                'location' => 'Chittagong, Agrabad',
                'profession_or_business' => 'Corporate Executive',
                'lead_source' => 'Reel',
                'interests' => ['Affiliate', 'Network'],
                'lead_tag' => 'A1',
                'stage' => LeadStage::PRESENTATION,
                'temperature' => LeadTemperature::HOT,
                'score' => 85,
                'budget_range' => '100,000+ BDT',
                'decision_timeline' => 'Within 7 Days',
                'next_action_type' => 'Online Presentation',
                'next_action_at' => now()->setHour(16)->setMinute(30), // Today
                'last_contact_at' => now()->subHours(4),
                'notes' => 'Very keen on passive income stream via affiliate ecosystem.',
            ],
            [
                'name' => 'Kamal Hossain',
                'mobile' => '01933445566',
                'whatsapp' => '01933445566',
                'email' => 'kamal@example.com',
                'facebook_url' => null,
                'location' => 'Sylhet, Zindabazar',
                'profession_or_business' => 'Investor / Expatriate',
                'lead_source' => 'Referral',
                'interests' => ['Investment', 'Partnership'],
                'lead_tag' => 'I1',
                'stage' => LeadStage::QUALIFIED,
                'temperature' => LeadTemperature::HOT,
                'score' => 90,
                'budget_range' => '500,000+ BDT',
                'decision_timeline' => 'Immediate',
                'next_action_type' => 'WhatsApp Discussion',
                'next_action_at' => now()->subDay(), // Overdue follow-up for test!
                'last_contact_at' => now()->subDays(3),
                'notes' => 'High ticket investor looking for verified asset-backed return model.',
            ],
            [
                'name' => 'Tanvir Ahmed',
                'mobile' => '01677889900',
                'whatsapp' => '01677889900',
                'email' => 'tanvir@example.com',
                'facebook_url' => 'https://facebook.com/tanvir.demo',
                'location' => 'Rajshahi',
                'profession_or_business' => 'University Student',
                'lead_source' => 'Messenger',
                'interests' => ['Product', 'Affiliate'],
                'lead_tag' => 'P1',
                'stage' => LeadStage::NEW,
                'temperature' => LeadTemperature::COLD,
                'score' => 30,
                'budget_range' => 'Under 10,000 BDT',
                'decision_timeline' => 'Within 30 Days',
                'next_action_type' => 'Intro Call',
                'next_action_at' => now()->setHour(15)->setMinute(0),
                'last_contact_at' => now()->subHours(2),
                'notes' => 'Inquired via Messenger regarding health products & affiliate start.',
            ],
            [
                'name' => 'Mahmudul Hasan',
                'mobile' => '01511223344',
                'whatsapp' => '01511223344',
                'email' => 'mahmud@example.com',
                'facebook_url' => null,
                'location' => 'Khulna',
                'profession_or_business' => 'Banker',
                'lead_source' => 'Offline Meeting',
                'interests' => ['Partnership', 'Network'],
                'lead_tag' => 'B1',
                'stage' => LeadStage::CONVERTED,
                'temperature' => LeadTemperature::HOT,
                'score' => 100,
                'budget_range' => '200,000 BDT',
                'decision_timeline' => 'Immediate',
                'next_action_type' => 'Onboarding Call',
                'next_action_at' => now()->addDays(2),
                'last_contact_at' => now(),
                'converted_at' => now(),
                'notes' => 'Successfully joined and started orientation.',
            ],
            [
                'name' => 'Nazmul Huda',
                'mobile' => '01799887766',
                'whatsapp' => null,
                'email' => null,
                'facebook_url' => null,
                'location' => 'Gazipur',
                'profession_or_business' => 'Small Trader',
                'lead_source' => 'Facebook Profile',
                'interests' => ['Product'],
                'lead_tag' => 'P1',
                'stage' => LeadStage::CONTACTED,
                'temperature' => LeadTemperature::STALE,
                'score' => 20,
                'budget_range' => null,
                'decision_timeline' => null,
                'next_action_type' => null,
                'next_action_at' => null, // Needs Next Action!
                'last_contact_at' => now()->subDays(10),
                'notes' => 'No response after initial contact 10 days ago.',
            ],
        ];

        if (Lead::count() === 0) {
            foreach ($leadsData as $data) {
                $sourceModel = $sourceModels[$data['lead_source']] ?? $sourceModels['Other'];

                $lead = Lead::create([
                    'name' => $data['name'],
                    'mobile' => $data['mobile'],
                    'whatsapp' => $data['whatsapp'],
                    'email' => $data['email'],
                    'facebook_url' => $data['facebook_url'],
                    'location' => $data['location'],
                    'profession_or_business' => $data['profession_or_business'],
                    'lead_source_id' => $sourceModel->id,
                    'lead_source_detail' => 'Sample Inbound Lead',
                    'interest_types' => $data['interests'],
                    'lead_tag' => $data['lead_tag'],
                    'stage' => $data['stage'],
                    'temperature' => $data['temperature'],
                    'score' => $data['score'],
                    'budget_range' => $data['budget_range'],
                    'decision_timeline' => $data['decision_timeline'],
                    'owner_user_id' => $admin->id,
                    'next_action_type' => $data['next_action_type'],
                    'next_action_at' => $data['next_action_at'],
                    'last_contact_at' => $data['last_contact_at'],
                    'converted_at' => $data['converted_at'] ?? null,
                    'notes' => $data['notes'],
                ]);

                foreach ($data['interests'] as $interest) {
                    LeadInterest::create([
                        'lead_id' => $lead->id,
                        'interest' => $interest,
                    ]);
                }

                // Create initial activity
                Activity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $admin->id,
                    'type' => 'lead_created',
                    'title' => 'Lead Created',
                    'description' => "Lead added via {$data['lead_source']}",
                    'performed_at' => $lead->created_at,
                ]);

                // Add sample activities and tasks
                if ($lead->stage === LeadStage::PRESENTATION) {
                    Presentation::create([
                        'lead_id' => $lead->id,
                        'user_id' => $admin->id,
                        'date_time' => now()->setHour(16)->setMinute(30),
                        'type' => PresentationType::ONLINE,
                        'topic' => 'SBL Ecosystem Overview & Affiliate Compensation Plan',
                        'interest_focus' => 'Affiliate / Network Opportunity',
                        'questions' => 'What is the minimum weekly commitment?',
                        'objections' => null,
                        'outcome' => PresentationOutcome::HOT,
                        'next_follow_up_at' => now()->addDay(),
                        'notes' => 'Scheduled 1-on-1 Zoom call presentation.',
                    ]);

                    Task::create([
                        'title' => 'Host 1-on-1 Presentation with Farhana',
                        'type' => TaskType::PRESENTATION,
                        'related_lead_id' => $lead->id,
                        'user_id' => $admin->id,
                        'due_at' => now()->setHour(16)->setMinute(30),
                        'priority' => TaskPriority::HIGH,
                        'status' => TaskStatus::PENDING,
                        'notes' => 'Prepare slides on dropshipping margin and affiliate model.',
                    ]);
                }

                if ($lead->stage === LeadStage::INTERESTED) {
                    Task::create([
                        'title' => 'Follow-up Call with Rafiqul regarding product catalogue',
                        'type' => TaskType::FOLLOW_UP,
                        'related_lead_id' => $lead->id,
                        'user_id' => $admin->id,
                        'due_at' => now()->addDay()->setHour(11)->setMinute(0),
                        'priority' => TaskPriority::MEDIUM,
                        'status' => TaskStatus::PENDING,
                        'notes' => 'Send wholesale price list on WhatsApp before calling.',
                    ]);
                }

                if ($lead->stage === LeadStage::QUALIFIED) {
                    Task::create([
                        'title' => 'Overdue Follow-up with Kamal Hossain',
                        'type' => TaskType::CALL,
                        'related_lead_id' => $lead->id,
                        'user_id' => $admin->id,
                        'due_at' => now()->subDay(),
                        'priority' => TaskPriority::HIGH,
                        'status' => TaskStatus::PENDING,
                        'notes' => 'Clarify capital protection terms and legal structure.',
                    ]);
                }
            }
        }

        // 5. Content Calendar Items
        if (ContentItem::count() === 0) {
            ContentItem::create([
                'campaign_id' => $campaign->id,
                'user_id' => $admin->id,
                'title' => 'How to Start E-commerce Without Big Inventory in 2026',
                'platform' => ContentPlatform::REEL,
                'content_type' => 'Short Video Reel',
                'topic' => 'Dropshipping & SBL Supply Chain',
                'caption' => 'Start your e-commerce business in 3 simple steps without purchasing any inventory! Details in comments.',
                'scheduled_at' => now()->addDays(1)->setHour(19)->setMinute(0),
                'status' => ContentStatus::READY,
                'cta' => 'Comment "INFO" or DM to get free guideline',
                'notes' => 'High quality vertical video, ready for publishing.',
            ]);

            ContentItem::create([
                'campaign_id' => $campaign->id,
                'user_id' => $admin->id,
                'title' => 'Case Study: From 0 to 50k BDT monthly income',
                'platform' => ContentPlatform::FACEBOOK_PAGE,
                'content_type' => 'Storytelling Post',
                'topic' => 'Member Success Story',
                'caption' => 'Real-world success story: How Shafiqul started part-time and built a sustainable career.',
                'scheduled_at' => now()->addDays(3)->setHour(20)->setMinute(0),
                'status' => ContentStatus::PLANNED,
                'cta' => 'Send WhatsApp Message',
                'notes' => 'Needs graphics from designer.',
            ]);
        }
    }
}
