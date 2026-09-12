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

class DemoDataSeeder extends Seeder
{
    /**
     * Run the demo data seeds (only when explicitly requested).
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@sbl.test')->first() ?? User::first();
        if (! $admin) {
            return;
        }

        $campaign = Campaign::first();

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
                'lead_tag' => 'P1',
                'stage' => LeadStage::PRESENTATION,
                'temperature' => LeadTemperature::HOT,
                'score' => 85,
                'budget_range' => '100,000+ BDT',
                'decision_timeline' => 'Immediate',
                'next_action_type' => 'Online Presentation',
                'next_action_at' => now()->setHour(16)->setMinute(30),
                'last_contact_at' => now()->subHours(4),
                'notes' => 'Very interested in passive income and affiliate tier incentives.',
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
                'lead_tag' => 'VIP',
                'stage' => LeadStage::QUALIFIED,
                'temperature' => LeadTemperature::HOT,
                'score' => 90,
                'budget_range' => '500,000+ BDT',
                'decision_timeline' => 'Immediate',
                'next_action_type' => 'WhatsApp Discussion',
                'next_action_at' => now()->subDay(),
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
                'next_action_at' => null,
                'last_contact_at' => now()->subDays(10),
                'notes' => 'No response after initial contact 10 days ago.',
            ],
        ];

        foreach ($leadsData as $data) {
            $sourceModel = LeadSource::where('name', $data['lead_source'])->first() 
                ?? LeadSource::firstOrCreate(['name' => $data['lead_source']]);

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

            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => $admin->id,
                'type' => 'lead_created',
                'title' => 'Lead Created',
                'description' => "Lead added via {$data['lead_source']}",
                'performed_at' => $lead->created_at,
            ]);

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
        }
    }
}

