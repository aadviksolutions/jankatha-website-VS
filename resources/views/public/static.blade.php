@extends('public.layout')
@section('content')
<section class="page-hero"><div class="container"><p class="section-kicker">JANKATHA.COM</p><h1>{{ $pageTitle }}</h1><p>Real stories. Real people.</p></div></section>
<section class="content-section">
    <div class="container article-wrap">
        <div class="article-content">
            @if($pageKey === 'about')
                <h2>About Jankatha.com</h2>
                <p><strong>Jankatha.com</strong> is a citizen-driven digital news portal rooted in the ethos of <em>"Real Stories. Real People."</em> We believe that true journalism begins at the grassroots—in the streets, villages, neighborhoods, and community corridors of Chhattisgarh and across India.</p>
                <p>Every citizen has a voice, and every community has stories that deserve to be heard. At Jankatha, we combine modern digital journalism with grassroots citizen reporting, empowering ordinary citizens to act as reporters and watchdogs for their local communities.</p>
                <h3>Our Editorial Integrity</h3>
                <p>Unlike unmoderated social platforms, <strong>submitted content on Jankatha.com is never published directly</strong>. Every citizen report undergoes a multi-stage editorial moderation process—including fact verification, source corroboration, image review, and legal compliance checks before publication.</p>
            @elseif($pageKey === 'privacy')
                <h2>Privacy Policy</h2>
                <p>At Jankatha.com, we are committed to safeguarding the privacy and digital rights of our visitors, readers, and citizen contributors.</p>
                <h3>Information We Collect</h3>
                <p>When you browse our portal, read articles, or submit a story tip, we may collect:</p>
                <ul>
                    <li>Contact details provided during story submission (name, mobile number, email address).</li>
                    <li>Uploaded multimedia (photographs, video footage, supporting documents).</li>
                    <li>Technical metadata strictly for fraud prevention, spam protection, and security analytics.</li>
                </ul>
                <h3>Confidentiality & Source Protection</h3>
                <p>We take journalistic source protection seriously. Your personal mobile number and private email address will <strong>never</strong> be published publicly or traded with third parties without your explicit prior consent.</p>
            @elseif($pageKey === 'terms')
                <h2>Terms & Conditions</h2>
                <p>By accessing or using Jankatha.com, you agree to comply with and be bound by the following terms:</p>
                <h3>Citizen Journalism Submissions</h3>
                <p>When submitting content through our citizen portal:</p>
                <ul>
                    <li>You affirm that the factual information provided is accurate and truthful to the best of your knowledge.</li>
                    <li>You warrant that you own or hold the necessary rights, permissions, and licenses for all submitted photos, videos, and media.</li>
                    <li>You grant Jankatha.com a non-exclusive license to edit, moderate, publish, and syndicate the story across our platforms.</li>
                    <li>Defamatory, hateful, obscene, communally sensitive, or legally prohibited content will be immediately rejected and may be reported to law enforcement authorities.</li>
                </ul>
            @elseif($pageKey === 'disclaimer')
                <h2>Editorial Disclaimer</h2>
                <p>News reports published on Jankatha.com are verified by our editorial desk prior to publication. However, reports originating from citizen journalists reflect eyewitness accounts at the time of reporting. Jankatha makes every effort to ensure accuracy and fairness.</p>
                <p>If you notice any factual inaccuracy or wish to request a correction or clarification, please reach out directly to our grievance and editorial desk at <strong>newsroom@jankatha.com</strong>.</p>
            @else
                <h2>Contact the Jankatha Newsroom</h2>
                <p>For story tips, breaking news alerts, corrections, and editorial enquiries, contact our news desk:</p>
                <p><strong>Editorial Desk:</strong> newsroom@jankatha.com</p>
                <p><strong>Head Office:</strong> Bilaspur, Chhattisgarh, India</p>
                <p><strong>WhatsApp News Helpline:</strong> +91 98765 43210</p>
                <p>To submit your own news report with photos and video, use our <a href="{{ route('submit-news') }}" style="color: var(--accent); font-weight: bold;">Citizen Submission Desk</a>.</p>
            @endif
        </div>
    </div>
</section>
@endsection
