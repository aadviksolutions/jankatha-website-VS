<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CitizenSubmission;
use App\Models\SubmissionStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class CitizenSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first() ?? User::first();
        $citizen = User::where('role', 'citizen')->first() ?? $admin;

        $categories = Category::pluck('id', 'slug')->toArray();
        $chhattisgarhId = $categories['chhattisgarh'] ?? Category::first()->id;
        $bilaspurId = $categories['bilaspur'] ?? $chhattisgarhId;
        $raipurId = $categories['raipur'] ?? $chhattisgarhId;

        $submissionsData = [
            [
                'headline' => 'मंगला चौक पर ट्रैफिक सिग्नल 4 दिनों से खराब, शाम को लग रहा भारी जाम',
                'description' => 'बिलासपुर के व्यस्ततम मंगला चौक पर मुख्य ट्रैफिक सिग्नल पिछले चार दिनों से काम नहीं कर रहा है। इसके कारण हर शाम 6 से 9 बजे के बीच भयंकर जाम की स्थिति बन रही है। स्कूली बसों और एंबुलेंस को भी निकलने में परेशानी हो रही है। प्रशासन से त्वरित मरम्मत की मांग है।',
                'category_id' => $bilaspurId,
                'location' => 'मंगला चौक',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'contributor_name' => 'राजेश कुमार वर्मा',
                'mobile' => '9827154321',
                'email' => 'rajesh.verma@example.com',
                'status' => 'pending',
                'editor_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'history' => [
                    ['status' => 'pending', 'note' => 'Submission received through citizen portal.', 'user_id' => null, 'time' => now()->subHours(3)],
                ],
            ],
            [
                'headline' => 'वार्ड क्रमांक 14 में दूषित पेयजल की आपूर्ति, दर्जनों लोग बीमार',
                'description' => 'पिछले एक हफ्ते से पाइपलाइन लीकेज के कारण नलों में गंदा और बदबूदार पानी आ रहा है। कई बच्चों को उल्टी-दस्त की शिकायत के बाद अस्पताल में भर्ती कराया गया है। स्वास्थ्य विभाग और नगर निगम को लिखित शिकायत देने के बाद भी कोई जांच टीम नहीं पहुंची।',
                'category_id' => $raipurId,
                'location' => 'सरोना, वार्ड 14',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'contributor_name' => 'अनिता साहू',
                'mobile' => '9406212345',
                'email' => 'anita.sahu@example.com',
                'status' => 'under_review',
                'editor_note' => 'वरिष्ठ संवाददाता को नगर निगम जल विभाग से आधिकारिक बयान लेने के लिए निर्देशित किया गया।',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subHours(5),
                'history' => [
                    ['status' => 'pending', 'note' => 'Submission received.', 'user_id' => null, 'time' => now()->subHours(8)],
                    ['status' => 'under_review', 'note' => 'Under review by news desk.', 'user_id' => $admin?->id, 'time' => now()->subHours(5)],
                ],
            ],
            [
                'headline' => 'ग्राम पंचायत कोनी में सड़क निर्माण में घटिया सामग्री का उपयोग',
                'description' => 'प्रधानमंत्री ग्राम सड़क योजना के तहत बन रही 3 किमी सड़क में ठेकेदार द्वारा गुणवत्ताहीन डामर और गिट्टी का प्रयोग किया जा रहा है। ग्रामीणों ने काम रुकवाकर प्रदर्शन किया।',
                'category_id' => $bilaspurId,
                'location' => 'कोनी ग्राम पंचायत',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'contributor_name' => 'महेश चंद्र देवांगन',
                'mobile' => '9179834567',
                'email' => 'm.dewangan@example.com',
                'status' => 'needs_more_information',
                'editor_note' => 'कृपया सड़क निर्माण की स्पष्ट तस्वीरें और संबंधित टेंडर नंबर या प्रदर्शन का वीडियो साझा करें ताकि खबर को पुख्ता किया जा सके।',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subHours(12),
                'history' => [
                    ['status' => 'pending', 'note' => 'Submission received.', 'user_id' => null, 'time' => now()->subDay()],
                    ['status' => 'under_review', 'note' => 'Desk started review.', 'user_id' => $admin?->id, 'time' => now()->subHours(16)],
                    ['status' => 'needs_more_information', 'note' => 'Requested video evidence and tender specifics from citizen reporter.', 'user_id' => $admin?->id, 'time' => now()->subHours(12)],
                ],
            ],
            [
                'headline' => 'स्थानीय युवाओं ने पेश की मिसाल: रक्तदान शिविर में 150 यूनिट रक्त एकत्र',
                'description' => 'युवा शक्ति संगठन द्वारा आयोजित एक दिवसीय रक्तदान शिविर में स्थानीय युवाओं और महिलाओं ने बढ़-चढ़कर हिस्सा लिया। सिम्स ब्लड बैंक के सहयोग से यह शिविर सफलता पूर्वक संपन्न हुआ।',
                'category_id' => $bilaspurId,
                'location' => 'गोलबाजार कम्युनिटी हॉल',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'contributor_name' => 'विकास कश्यप',
                'mobile' => '9826312345',
                'email' => 'vikas.k@example.com',
                'status' => 'verified',
                'editor_note' => 'सिम्स ब्लड बैंक प्रभारी से आंकड़ों का सत्यापन संपन्न।',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subHours(20),
                'history' => [
                    ['status' => 'pending', 'note' => 'Submission received.', 'user_id' => null, 'time' => now()->subDays(2)],
                    ['status' => 'under_review', 'note' => 'Desk verification assigned.', 'user_id' => $admin?->id, 'time' => now()->subDay()],
                    ['status' => 'verified', 'note' => 'Blood bank coordinator verified statistics.', 'user_id' => $admin?->id, 'time' => now()->subHours(20)],
                ],
            ],
            [
                'headline' => 'पचपेड़ी नाका चौक पर आवारा मवेशियों का जमावड़ा, आए दिन हो रहे सड़क हादसे',
                'description' => 'नेशनल हाईवे पर स्थित पचपेड़ी नाका चौक पर रात के समय 50 से अधिक आवारा मवेशी सड़कों पर बैठे रहते हैं। प्रकाश की कमी के कारण दोपहिया चालक लगातार दुर्घटनाग्रस्त हो रहे हैं।',
                'category_id' => $raipurId,
                'location' => 'पचपेड़ी नाका चौक',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'contributor_name' => 'दिनेश कुमार त्रिपाठी',
                'mobile' => '9425567890',
                'email' => 'dinesh.tripathi@example.com',
                'status' => 'approved',
                'editor_note' => 'खबर प्रकाशन के लिए स्वीकृत है। आज शाम के बुलेटिन में प्रकाशित की जाएगी।',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subHours(8),
                'history' => [
                    ['status' => 'pending', 'note' => 'Submission received.', 'user_id' => null, 'time' => now()->subDays(2)],
                    ['status' => 'under_review', 'note' => 'Assigned to municipal desk.', 'user_id' => $admin?->id, 'time' => now()->subDay()],
                    ['status' => 'verified', 'note' => 'Local correspondent verified scene.', 'user_id' => $admin?->id, 'time' => now()->subHours(12)],
                    ['status' => 'approved', 'note' => 'Approved for publication.', 'user_id' => $admin?->id, 'time' => now()->subHours(8)],
                ],
            ],
            [
                'headline' => 'अवैध शराब की बिक्री के विरोध में महिलाओं ने घेरा थाना',
                'description' => 'गांव में खुलेआम चल रही कच्ची शराब की भट्ठियों से परेशान होकर क्षेत्र की 200 से अधिक महिलाओं ने स्थानीय थाने के सामने प्रदर्शन किया और ज्ञापन सौंपा।',
                'category_id' => $chhattisgarhId,
                'location' => 'कोटा',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'contributor_name' => 'पुष्पा बाई',
                'mobile' => '9754123456',
                'email' => 'pushpa.bai@example.com',
                'status' => 'rejected',
                'editor_note' => 'यह खबर बिना ठोस नाम, तिथि और संबंधित थाना प्रभारी के पक्ष के भेजी गई थी। नियमानुसार पक्ष जानने के उपरांत पुनः भेजें।',
                'reviewed_by' => $admin?->id,
                'reviewed_at' => now()->subDays(3),
                'history' => [
                    ['status' => 'pending', 'note' => 'Submission received.', 'user_id' => null, 'time' => now()->subDays(4)],
                    ['status' => 'under_review', 'note' => 'Checking legal facts.', 'user_id' => $admin?->id, 'time' => now()->subDays(3)],
                    ['status' => 'rejected', 'note' => 'Uncorroborated allegations without official rebuttal.', 'user_id' => $admin?->id, 'time' => now()->subDays(3)],
                ],
            ],
        ];

        foreach ($submissionsData as $data) {
            $history = $data['history'];
            unset($data['history']);

            $submission = CitizenSubmission::create([
                ...$data,
                'user_id' => $citizen?->id,
                'consent_at' => now()->subDays(1),
            ]);

            foreach ($history as $h) {
                SubmissionStatusHistory::create([
                    'submission_id' => $submission->id,
                    'user_id' => $h['user_id'],
                    'old_status' => null,
                    'new_status' => $h['status'],
                    'note' => $h['note'],
                    'created_at' => $h['time'],
                ]);
            }
        }
    }
}
