<?php

namespace Database\Seeders;

use App\Models\SblContact;
use Illuminate\Database\Seeder;

class SblContactSeeder extends Seeder
{
    public function run(): void
    {
        $contacts = [
            [
                'department' => 'কাস্টমার কেয়ার ও সাপোর্ট সেল',
                'contact_person' => 'সাপোর্ট টিম',
                'phone' => '01700000000',
                'whatsapp' => '01700000000',
                'email' => 'support@sbl.com.bd',
                'available_hours' => '২৪/৭ সাপোর্ট (সার্বক্ষণিক)',
                'description' => 'যেকোনো সাধারণ তথ্য, সার্ভিস সংক্রান্ত প্রশ্ন ও তাত্ক্ষণিক অনুসন্ধানের জন্য যোগাযোগ করুন।',
                'icon' => '📞',
                'badge' => '২৪/৭ হেল্পলাইন',
                'is_primary' => true,
                'sort_order' => 1,
            ],
            [
                'department' => 'ড্রপশিপিং ও মার্চেন্ট ডেস্ক',
                'contact_person' => 'মার্চেন্ট সাপোর্ট অফিসার',
                'phone' => '01800000001',
                'whatsapp' => '01800000001',
                'email' => 'dropship@sbl.com.bd',
                'available_hours' => 'সকাল ১০:০০ - রাত ০৮:০০',
                'description' => 'ড্রপশিপিং পণ্য স্টক, অর্ডার প্রসেসিং, কুরিয়ার স্ট্যাটাস ও মার্চেন্ট সংক্রান্ত সমস্যার সমাধান।',
                'icon' => '🛍️',
                'badge' => 'ড্রপশিপিং সাপোর্ট',
                'is_primary' => false,
                'sort_order' => 2,
            ],
            [
                'department' => 'ইনভেস্টর ও ক্রাউডফান্ডিং সেল',
                'contact_person' => 'ইনভেস্টমেন্ট রিলেশন অফিসার',
                'phone' => '01900000002',
                'whatsapp' => '01900000002',
                'email' => 'invest@sbl.com.bd',
                'available_hours' => 'সকাল ১০:০০ - রাত ০৮:০০',
                'description' => '১২০,০০০/- ও ৫৫০,০০০/- টাকার ক্রাউডফান্ডিং প্যাকেজ ও সাপ্তাহিক প্রফিট শেয়ারিং সংক্রান্ত তথ্য।',
                'icon' => '📈',
                'badge' => 'ইনভেস্টর হটলাইন',
                'is_primary' => false,
                'sort_order' => 3,
            ],
            [
                'department' => 'আইটি ও মেম্বার পোর্টাল সাপোর্ট',
                'contact_person' => 'সিস্টেম অ্যাডমিন',
                'phone' => '01600000003',
                'whatsapp' => '01600000003',
                'email' => 'tech@sbl.com.bd',
                'available_hours' => 'সকাল ১০:০০ - রাত ১০:০০',
                'description' => 'অ্যাপ লগইন সমস্যা, ব্যাকঅফিস ড্যাশবোর্ড বা টেকনিক্যাল সহযোগিতার জন্য।',
                'icon' => '💻',
                'badge' => 'আইটি টিম',
                'is_primary' => false,
                'sort_order' => 4,
            ],
            [
                'department' => 'কাউন্সেলিং ও লিডারশিপ ট্রেনিং',
                'contact_person' => 'ট্রেনিং কো-অর্ডিনেটর',
                'phone' => '01500000004',
                'whatsapp' => '01500000004',
                'email' => 'training@sbl.com.bd',
                'available_hours' => 'সকাল ১০:০০ - সন্ধ্যা ০৭:০০',
                'description' => 'কাউন্সেলিং-১ এবং লিডারশিপ ট্রেইনিং সেশন বুকিং ও শিডিউল নিশ্চিতকরণ।',
                'icon' => '🎓',
                'badge' => 'ট্রেনিং ডেস্ক',
                'is_primary' => false,
                'sort_order' => 5,
            ],
            [
                'department' => 'অ্যাকাউন্টস ও কমিশন পে-আউট ডেস্ক',
                'contact_person' => 'ফাইন্যান্স এক্সিকিউটিভ',
                'phone' => '01700000005',
                'whatsapp' => '01700000005',
                'email' => 'accounts@sbl.com.bd',
                'available_hours' => 'বেলা ১১:০০ - বিকাল ০৬:০০ (রবি-বৃহস্পতি)',
                'description' => 'উইথড্রয়াল, রেফারেল কমিশন স্টেটমেন্ট ও ফাইন্যান্সিয়াল ট্রানজ্যাকশন সংক্রান্ত যোগাযোগ।',
                'icon' => '💳',
                'badge' => 'ফাইন্যান্স ডেস্ক',
                'is_primary' => false,
                'sort_order' => 6,
            ],
        ];

        foreach ($contacts as $contact) {
            SblContact::updateOrCreate(
                ['department' => $contact['department']],
                $contact
            );
        }
    }
}
