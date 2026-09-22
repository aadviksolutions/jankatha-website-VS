<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'super_admin')->first() ?? User::first();
        if (! $admin) {
            return;
        }

        $categories = Category::pluck('id', 'slug')->toArray();

        $tags = [
            'Breaking' => Tag::firstOrCreate(['slug' => 'breaking'], ['name' => 'Breaking']),
            'Chhattisgarh' => Tag::firstOrCreate(['slug' => 'chhattisgarh'], ['name' => 'Chhattisgarh']),
            'Bilaspur' => Tag::firstOrCreate(['slug' => 'bilaspur'], ['name' => 'Bilaspur']),
            'Raipur' => Tag::firstOrCreate(['slug' => 'raipur'], ['name' => 'Raipur']),
            'Development' => Tag::firstOrCreate(['slug' => 'development'], ['name' => 'विकास कार्य']),
            'Education' => Tag::firstOrCreate(['slug' => 'education'], ['name' => 'शिक्षा']),
            'Health' => Tag::firstOrCreate(['slug' => 'health'], ['name' => 'स्वास्थ्य']),
            'Crime' => Tag::firstOrCreate(['slug' => 'crime'], ['name' => 'अपराध']),
            'CitizenVoice' => Tag::firstOrCreate(['slug' => 'citizen-voice'], ['name' => 'जनता की आवाज़']),
        ];

        $articles = [
            [
                'headline' => 'बिलासपुर-रायपुर फोरलेन कॉरिडोर का विस्तार कार्य अंतिम चरण में: अगले माह से शुरू होगा ट्रायल रन',
                'category_slug' => 'chhattisgarh',
                'short_description' => 'राष्ट्रीय राजमार्ग प्राधिकरण (NHAI) ने बिलासपुर और रायपुर के बीच यात्रा समय को घटाकर केवल 90 मिनट करने वाले नए एक्सप्रेसवे खंड का निरीक्षण पूरा कर लिया है।',
                'content' => '<p><strong>बिलासपुर।</strong> छत्तीसगढ़ के दो प्रमुख आर्थिक केंद्रों—बिलासपुर और रायपुर के बीच आवागमन को सुगम बनाने के लिए निर्माणाधीन आधुनिक फोरलेन कॉरिडोर का कार्य अब 95 प्रतिशत पूरा हो चुका है। राष्ट्रीय राजमार्ग प्राधिकरण के क्षेत्रीय अधिकारियों ने मंगलवार को सड़क का विस्तृत तकनीकी निरीक्षण किया।</p><p>परियोजना निदेशक के अनुसार, सुरक्षा मानकों और ब्लैक स्पॉट्स के सुधार के बाद आगामी 15 अक्टूबर से भारी और हल्के वाहनों के लिए ट्रायल रन शुरू किया जाएगा। इस नए मार्ग से दोनों शहरों के बीच यात्रा समय दो घंटे से घटकर मात्र डेढ़ घंटा रह जाएगा।</p><p>स्थानीय व्यापारियों और दैनिक यात्रियों ने इस गति का स्वागत किया है। इसके अलावा सड़क किनारे स्मार्ट टोलिंग और इमरजेंसी मेडिकल बूथ्स की व्यवस्था भी की जा रही है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1200&q=80',
                'location' => 'मंगला चौक',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => true,
                'is_breaking' => true,
                'published_at' => now()->subHours(2),
                'tag_keys' => ['Breaking', 'Chhattisgarh', 'Bilaspur', 'Development'],
            ],
            [
                'headline' => 'छत्तीसगढ़ विधानसभा का विशेष सत्र कल से, किसानों के बकाया बोनस और नई योजनाओं पर होगी चर्चा',
                'category_slug' => 'politics',
                'short_description' => 'सत्र में राज्य सरकार अनुपूरक बजट पेश करेगी; विपक्ष ने कानून व्यवस्था और कृषि मूल्य समर्थन को लेकर सरकार को घेरने की रणनीति बनाई।',
                'content' => '<p><strong>रायपुर।</strong> छत्तीसगढ़ विधानसभा का त्रिदिवसीय विशेष सत्र कल सुबह 11 बजे से शुरू हो रहा है। सत्र के हंगामेदार रहने के आसार हैं। सत्तापक्ष जहां किसानों के खाते में लंबित धान बोनस राशि और जनहितैषी योजनाओं की घोषणा की तैयारी में है, वहीं मुख्य विपक्षी दल ने बेरोजगारी और कानून व्यवस्था को लेकर स्थगन प्रस्ताव लाने की घोषणा की है।</p><p>विधानसभा अध्यक्ष ने सर्वदलीय बैठक बुलाकर सत्र के सुचारू संचालन में सभी दलों से सहयोग की अपील की है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1541872703-74c5e44368f9?w=1200&q=80',
                'location' => 'विधानसभा परिसर',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => true,
                'published_at' => now()->subHours(4),
                'tag_keys' => ['Breaking', 'Politics', 'Raipur'],
            ],
            [
                'headline' => 'स्मार्ट सिटी प्रोजेक्ट: बिलासपुर में 12 नए पार्कों और ग्रीन वॉकवे का लोकार्पण',
                'category_slug' => 'bilaspur',
                'short_description' => 'शहरवासियों को सुबह की सैर और स्वच्छ पर्यावरण के लिए नगर निगम ने आधुनिक ओपन जिम और सौर ऊर्जा चालित प्रकाश व्यवस्था से सुसज्जित पार्क समर्पित किए।',
                'content' => '<p><strong>बिलासपुर।</strong> अरपा नदी के तटवर्ती क्षेत्रों और शहर के विभिन्न वार्डों में पर्यावरण संरक्षण को बढ़ावा देने के उद्देश्य से 12 नव-निर्मित पार्कों का उद्घाटन नगर निगम आयुक्त एवं महापौर द्वारा संयुक्त रूप से किया गया।</p><p>इन सभी पार्कों में बच्चों के लिए झूले, वृद्धजनों के लिए विशेष पाथवे और 24 घंटे सुरक्षा निगरानी के लिए सीसीटीवी कैमरे स्थापित किए गए हैं। स्थानीय नागरिकों ने इस पहल की सराहना की है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1519331379826-f10be5486c6f?w=1200&q=80',
                'location' => 'नेहरू नगर / अरपा तट',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subHours(6),
                'tag_keys' => ['Bilaspur', 'Development'],
            ],
            [
                'headline' => 'भिलाई स्टील प्लांट ने दर्ज किया रिकॉर्ड उत्पादन, नवीकरणीय ऊर्जा से चलेंगी नई रोलिंग मिलें',
                'category_slug' => 'business',
                'short_description' => 'सेल की प्रमुख इकाई बीएसपी ने चालू वित्त वर्ष की तीसरी तिमाही में हॉट मेटल और उच्च श्रेणी रेल उत्पादन में नया कीर्तिमान स्थापित किया।',
                'content' => '<p><strong>भिलाई।</strong> स्टील अथॉरिटी ऑफ इंडिया लिमिटेड (SAIL) के प्रमुख संयंत्र भिलाई इस्पात संयंत्र ने वैश्विक इस्पात मांग के बीच उत्पादन में ऐतिहासिक उपलब्धि हासिल की है। प्रबंधन ने पुष्टि की है कि आधुनिक 130 मीटर लंबी रेल पटरियों का निर्यात यूरोप और मध्य पूर्व को शुरू हो गया है।</p><p>संयंत्र निदेशक ने बताया कि कार्बन फुटप्रिंट कम करने के लिए 50 मेगावाट का नया सोलर पार्क भी चालू किया जा रहा है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1200&q=80',
                'location' => 'बीएसपी सेक्टर',
                'district' => 'दुर्ग',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subHours(8),
                'tag_keys' => ['Chhattisgarh', 'Development'],
            ],
            [
                'headline' => 'साइबर ठगी के बड़े गिरोह का पर्दाफाश: अंतरराज्यीय साइबर गिरोह के 6 सदस्य रायपुर से गिरफ्तार',
                'category_slug' => 'crime',
                'short_description' => 'फर्जी बैंक कॉल और लॉटरी का झांसा देकर करोड़ों की ठगी करने वाले गिरोह से 32 मोबाइल, 45 एटीएम कार्ड और ₹18 लाख नकद बरामद।',
                'content' => '<p><strong>रायपुर।</strong> राज्य साइबर सेल और सिविल लाइंस पुलिस की संयुक्त टीम ने तकनीकी सर्विलांस के आधार पर ऑनलाइन ठगी करने वाले एक शातिर अंतरराज्यीय गिरोह का भंडाफोड़ किया है। आरोपी खुद को बैंक अधिकारी बताकर ओटीपी हासिल करते थे।</p><p>एसएसपी ने नागरिकों से किसी भी अज्ञात व्यक्ति के साथ बैंक विवरण और ओटीपी साझा न करने की सख्त चेतावनी जारी की है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=1200&q=80',
                'location' => 'पंडरी',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subHours(10),
                'tag_keys' => ['Crime', 'Raipur'],
            ],
            [
                'headline' => 'बस्तर के सुदूर अंचलों में मोबाइल हेल्थ क्लिनिक का विस्तार: 150 गांवों तक पहुंची आधुनिक स्वास्थ्य सेवाएं',
                'category_slug' => 'health',
                'short_description' => 'मुख्यमंत्री हाट बाजार क्लिनिक योजना के तहत सुदूर वनांचल क्षेत्रों में डॉक्टरों की टीम ने निःशुल्क जांच, दवाइयां और टेली-परामर्श उपलब्ध कराया।',
                'content' => '<p><strong>जगदलपुर।</strong> बस्तर संभाग के दूरस्थ वनांचलों में रहने वाले ग्रामीणों को बेहतर स्वास्थ्य सुविधाएं पहुंचाने के लिए 25 नए सचल चिकित्सा वाहन रवाना किए गए हैं। इन वाहनों में आधुनिक डायग्नोस्टिक उपकरण, ईसीजी और आपातकालीन ऑक्सीजन की सुविधा उपलब्ध है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=1200&q=80',
                'location' => 'जगदलपुर',
                'district' => 'बस्तर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subHours(14),
                'tag_keys' => ['Chhattisgarh', 'Health', 'CitizenVoice'],
            ],
            [
                'headline' => '[Video Report] अरपा नदी संवर्धन अभियान: जनसहभागिता से बदला नदी का स्वरूप, देखें ग्राउंड रिपोर्ट',
                'category_slug' => 'video-news',
                'short_description' => 'Jankatha की विशेष वीडियो रिपोर्ट: युवाओं और पर्यावरण कार्यकर्ताओं ने मिलकर अरपा नदी के 5 किलोमीटर के दायरे से कचरा हटाकर जल प्रवाह को किया पुनर्जीवित।',
                'content' => '<p><strong>बिलासपुर।</strong> बिलासपुर की जीवनरेखा कही जाने वाली अरपा नदी को बचाने के लिए शुरू हुआ जन-आंदोलन अब रंग लाने लगा है। स्थानीय नागरिकों, छात्र संगठनों और पर्यावरणविदों ने शनिवार को वृहद श्रमदान अभियान चलाया।</p><p>देखें हमारी ग्राउंड रिपोर्ट और जानें कैसे स्थानीय लोगों ने अपनी संकल्प शक्ति से प्रशासन को भी इस दिशा में सक्रिय कदम उठाने के लिए प्रेरित किया।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=1200&q=80',
                'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4',
                'location' => 'शनिचरी पड़ाव',
                'district' => 'बिलासपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subHours(18),
                'tag_keys' => ['Bilaspur', 'CitizenVoice'],
            ],
            [
                'headline' => '[Photo Story] तीजा-पोरा पर्व पर छत्तीसगढ़ की सांस्कृतिक छटा: पारंपरिक वेशभूषा में सजी मातृशक्ति',
                'category_slug' => 'photo-news',
                'short_description' => 'राज्यभर में पारंपरिक उल्लास और श्रद्धा के साथ मनाया गया तीजा-पोरा; देखें विभिन्न जिलों से मनमोहक तस्वीरों की विशेष गैलरी।',
                'content' => '<p><strong>छत्तीसगढ़।</strong> लोक संस्कृति के सबसे बड़े उत्सव तीजा-पोरा की धूम पूरे राज्य में देखने को मिली। महिलाओं ने अखंड सौभाग्य की कामना के साथ निर्जला व्रत रखा और पारंपरिक गीतों के साथ शिव-पार्वती की आराधना की।</p><p>ग्रामीण क्षेत्रों में बैलों की पूजा-अर्चना और नंदी बैल दौड़ का भी भव्य आयोजन किया गया। Jankatha के नागरिक पत्रकारों द्वारा भेजी गई विशेष तस्वीरें।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f?w=1200&q=80',
                'location' => 'गोलबाजार',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subDay(),
                'tag_keys' => ['Chhattisgarh', 'CitizenVoice'],
            ],
            [
                'headline' => 'स्वामी आत्मानंद अंग्रेजी माध्यम स्कूलों में 500 नए शिक्षकों की भर्ती प्रक्रिया शुरू',
                'category_slug' => 'education',
                'short_description' => 'स्कूल शिक्षा विभाग ने सभी जिला मुख्यालयों के लिए पदवार विज्ञप्ति जारी की; पारदर्शी चयन प्रक्रिया के लिए ऑनलाइन पोर्टल खुला।',
                'content' => '<p><strong>रायपुर।</strong> राज्य सरकार की महत्वाकांक्षी स्वामी आत्मानंद उत्कृष्ट अंग्रेजी माध्यम विद्यालय योजना के तहत रिक्त पदों पर संविदा शिक्षकों की भर्ती का विस्तृत नोटिफिकेशन जारी कर दिया गया है।</p><p>गणित, विज्ञान और अंग्रेजी विषयों के व्याख्याताओं के लिए वॉक-इन इंटरव्यू अगले सप्ताह से निर्धारित किए गए हैं। आवेदन की अंतिम तिथि 30 सितंबर तय की गई है।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1200&q=80',
                'location' => 'पेंशन बाड़ा',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subDays(2),
                'tag_keys' => ['Education', 'Raipur'],
            ],
            [
                'headline' => 'रणजी ट्रॉफी: छत्तीसगढ़ ने विदर्भ को रोमांचक मुकाबले में 4 विकेट से दी शिकस्त',
                'category_slug' => 'sports',
                'short_description' => 'शहीद वीर नारायण सिंह अंतरराष्ट्रीय क्रिकेट स्टेडियम में खेले गए मैच में कप्तान हरप्रीत सिंह भाटिया ने जमाया शानदार नाबाद शतक।',
                'content' => '<p><strong>नवा रायपुर।</strong> छत्तीसगढ़ की रणजी क्रिकेट टीम ने आज घरेलू मैदान पर विदर्भ के खिलाफ ऐतिहासिक जीत दर्ज की। 240 रनों के लक्ष्य का पीछा करते हुए टीम ने 6 विकेट खोकर जीत का मुकाम हासिल कर लिया।</p><p>गेंदबाजी में शशांक सिंह ने दोनों पारियों में मिलाकर 7 विकेट चटकाए और मैन ऑफ द मैच चुने गए।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1540747913346-19e32dc3e97e?w=1200&q=80',
                'location' => 'शहीद वीर नारायण सिंह स्टेडियम',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subDays(2),
                'tag_keys' => ['Chhattisgarh', 'Sports'],
            ],
            [
                'headline' => 'दुर्ग-राजनांदगांव रेलवे ट्रैक पर ऑटोमैटिक सिग्नलिंग सिस्टम चालू, ट्रेनों की गति बढ़ेगी',
                'category_slug' => 'durg',
                'short_description' => 'दक्षिण पूर्व मध्य रेलवे (SECR) ने 45 किमी खंड पर नए इलेक्ट्रॉनिक इंटरलॉकिंग सिस्टम की कमीशनिंग पूरी की।',
                'content' => '<p><strong>दुर्ग।</strong> हावड़ा-मुंबई मुख्य रेल मार्ग पर ट्रेनों की समयबद्धता और सुरक्षा सुनिश्चित करने के लिए अत्याधुनिक ऑटोमैटिक सिग्नलिंग प्रणाली को सफलतापूर्वक चालू कर दिया गया है। इससे मालगाड़ियों और सुपरफास्ट ट्रेनों के ठहराव समय में 20% तक की कमी आएगी।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1474487548417-781cb71495f3?w=1200&q=80',
                'location' => 'दुर्ग जंक्शन',
                'district' => 'दुर्ग',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subDays(3),
                'tag_keys' => ['Chhattisgarh', 'Development'],
            ],
            [
                'headline' => 'AIIMS रायपुर में रोबोटिक सर्जरी यूनिट स्थापित, जटिल कैंसर ऑपरेशन होंगे और अधिक सटीक',
                'category_slug' => 'health',
                'short_description' => 'मध्य भारत के मरीजों के लिए बड़ी राहत: न्यूनतम चीरा और त्वरित रिकवरी वाली रोबोटिक तकनीक का शुभारंभ।',
                'content' => '<p><strong>रायपुर।</strong> अखिल भारतीय आयुर्विज्ञान संस्थान (AIIMS) रायपुर में आज अत्याधुनिक रोबोटिक सर्जिकल सिस्टम का विधिवत शुभारंभ किया गया। इस प्रणाली की मदद से यूरोलॉजी, ऑन्कोलॉजी और कार्डियक सर्जरी न्यूनतम जोखिम और रक्तस्त्राव के साथ संभव हो सकेगी।</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?w=1200&q=80',
                'location' => 'एम्स टाटीबंध',
                'district' => 'रायपुर',
                'state' => 'छत्तीसगढ़',
                'is_featured' => false,
                'is_breaking' => false,
                'published_at' => now()->subDays(3),
                'tag_keys' => ['Health', 'Raipur'],
            ],
        ];

        foreach ($articles as $data) {
            $catId = $categories[$data['category_slug']] ?? reset($categories);
            $slug = Str::slug($data['headline']) ?: 'news-'.Str::random(6);

            $news = News::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $catId,
                    'author_id' => $admin->id,
                    'headline' => $data['headline'],
                    'short_description' => $data['short_description'],
                    'content' => $data['content'],
                    'featured_image' => $data['featured_image'],
                    'location' => $data['location'],
                    'district' => $data['district'],
                    'state' => $data['state'],
                    'video_url' => $data['video_url'] ?? null,
                    'status' => 'published',
                    'is_breaking' => $data['is_breaking'] ?? false,
                    'is_featured' => $data['is_featured'] ?? false,
                    'published_at' => $data['published_at'] ?? now(),
                    'seo_title' => $data['headline'],
                    'seo_description' => $data['short_description'],
                ]
            );

            // Attach tags
            if (! empty($data['tag_keys'])) {
                $tagIds = [];
                foreach ($data['tag_keys'] as $k) {
                    if (isset($tags[$k])) {
                        $tagIds[] = $tags[$k]->id;
                    }
                }
                $news->tagsRelation()->sync($tagIds);
            }
        }
    }
}
