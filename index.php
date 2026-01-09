<?php
/**
 * Customer Registration Page
 * Car Raffle System - Hamat Campaign
 */

require_once __DIR__ . '/includes/init.php';

// Language Handling
$lang = $_GET['lang'] ?? $_SESSION['lang'] ?? 'ar';
if (!in_array($lang, ['ar', 'en'])) {
    $lang = 'ar';
}
$_SESSION['lang'] = $lang;

$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// Translations
$trans = [
    'ar' => [
        'title' => 'سحب السيارة - هامات',
        'alt_hamat' => 'هامات',
        'alt_fb' => 'Family Bonds',
        'ph_hamat' => 'شعار هامات',
        'hero_title' => 'سحب على <span>سيارة</span>',
        'hero_desc' => 'سجّل الآن واحصل على فرصة للفوز بسيارة جديدة!',
        'grand_prize' => '🎉 الجائزة الكبرى',
        'checking_loc' => 'جاري التحقق من موقعك...',
        'reg_form' => 'نموذج التسجيل',
        'full_name' => 'الاسم الكامل',
        'name_ph' => 'أدخل اسمك الكامل',
        'mobile' => 'رقم الجوال',
        'mobile_help' => 'أدخل رقم الجوال السعودي',
        'mall' => 'المول',
        'submit' => 'تسجيل المشاركة',
        'registering' => 'جاري التسجيل...',
        'success_title' => 'تم التسجيل بنجاح!',
        'success_msg1' => 'شكراً لك على المشاركة في السحب على السيارة.',
        'success_msg2' => 'سيتم التواصل معك في حال الفوز. حظاً سعيداً! 🍀',
        'retry' => 'إعادة المحاولة',
        'footer' => 'جميع الحقوق محفوظة © ' . date('Y') . ' هامات',
        'js_browser_no_geo' => 'متصفحك لا يدعم تحديد الموقع',
        'js_unable_loc' => 'تعذر تحديد موقعك',
        'js_allow_loc' => 'يرجى السماح بالوصول إلى موقعك للمتابعة',
        'js_loc_unavailable' => 'معلومات الموقع غير متاحة',
        'js_timeout' => 'انتهت مهلة طلب الموقع',
        'js_error_verify' => 'حدث خطأ في التحقق من الموقع',
        'js_loc_verified' => 'تم التحقق من موقعك - ',
        'js_generic_error' => 'حدث خطأ. يرجى المحاولة مرة أخرى.',
        'switch_lang' => 'English',
        'terms_agree' => 'بتسجيل مشاركتك انت توافق على ',
        'terms_link' => 'الشروط والأحكام',
        'terms_title' => 'الشروط والأحكام'
    ],
    'en' => [
        'title' => 'Car Raffle - Hamat',
        'alt_hamat' => 'Hamat',
        'alt_fb' => 'Family Bonds',
        'ph_hamat' => 'Hamat Logo',
        'hero_title' => 'Win a <span>Car</span>',
        'hero_desc' => 'Register now for a chance to win a new car!',
        'grand_prize' => '🎉 Grand Prize',
        'checking_loc' => 'Checking your location...',
        'reg_form' => 'Registration Form',
        'full_name' => 'Full Name',
        'name_ph' => 'Enter your full name',
        'mobile' => 'Mobile Number',
        'mobile_help' => 'Enter Saudi mobile number',
        'mall' => 'Mall',
        'submit' => 'Register Entry',
        'registering' => 'Registering...',
        'success_title' => 'Registration Successful!',
        'success_msg1' => 'Thank you for participating in the car raffle.',
        'success_msg2' => 'You will be contacted if you win. Good luck! 🍀',
        'retry' => 'Retry',
        'footer' => 'All rights reserved © ' . date('Y') . ' Hamat',
        'js_browser_no_geo' => 'Your browser does not support geolocation',
        'js_unable_loc' => 'Unable to determine your location',
        'js_allow_loc' => 'Please allow access to your location to continue',
        'js_loc_unavailable' => 'Location information unavailable',
        'js_timeout' => 'Location request timed out',
        'js_error_verify' => 'Error verifying location',
        'js_loc_verified' => 'Location verified - ',
        'js_generic_error' => 'An error occurred. Please try again.',
        'switch_lang' => 'العربية',
        'terms_agree' => 'By registering, you agree to the ',
        'terms_link' => 'Terms & Conditions',
        'terms_title' => 'Terms & Conditions'
    ]
];

$t = $trans[$lang];
$next_lang = $lang === 'ar' ? 'en' : 'ar';
$terms_content = '
<div class="terms-content">
    <h4 class="text-center mb-4">TERMS & CONDITIONS – CAR RAFFLE DRAW ACTIVATION</h4>
    
    <div class="term-item mb-3">
        <h5 class="mb-2">Data Collection | جمع البيانات</h5>
        <p class="mb-1 text-muted">By participating, visitors agree to provide their information for raffle entry purposes and future marketing communications.</p>
        <p dir="rtl">من خلال المشاركة، يوافق الزوار على تقديم معلوماتهم لغرض الدخول في السحب والاتصالات التسويقية المستقبلية.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Eligibility | الأهلية</h5>
        <p class="mb-1 text-muted">The raffle is open to all mall visitors aged 18 years and above. A valid ID is required to claim the prize.</p>
        <p dir="rtl">السحب مفتوح لجميع زوار المول الذين تبلغ أعمارهم 18 عامًا فأكثر. يلزم تقديم هوية سارية لاستلام الجائزة.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Participation | المشاركة</h5>
        <p class="mb-1 text-muted">Each visitor is entitled to one raffle entry per day after completing the registration process. Duplicate entries will be disqualified.</p>
        <p dir="rtl">يحق لكل زائر إدخال واحد في السحب يوميًا بعد إتمام عملية التسجيل. سيتم استبعاد الإدخالات المكررة.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Duration | المدة</h5>
        <p class="mb-1 text-muted">The raffle will run during the announced campaign period. The draw date will be communicated through official mall channels.</p>
        <p dir="rtl">سيستمر السحب طوال فترة الحملة المعلنة. سيتم الإعلان عن تاريخ السحب عبر القنوات الرسمية للمول.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Prize | الجائزة</h5>
        <p class="mb-1 text-muted">The prize is one car for each winner (model and specifications as announced). The prize is non-transferable and cannot be exchanged for cash or other items.</p>
        <p dir="rtl">الجائزة عبارة عن سيارة واحدة لكل فائز (الموديل والمواصفات كما هو معلن). الجائزة غير قابلة للتحويل ولا يمكن استبدالها نقدًا أو بمنتجات أخرى.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Prize Specifications | مواصفات الجائزة</h5>
        <p class="mb-1 text-muted">The car’s specifications, and color are subject to availability and may differ from any images or representations shown in promotional materials.</p>
        <p dir="rtl">مواصفات السيارة ولونها تخضع للتوافر وقد تختلف عن أي صور أو تمثيلات معروضة في المواد الترويجية.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Winner Announcement & Collection | إعلان واستلام الجائزة</h5>
        <p class="mb-1 text-muted">The winner will be selected randomly under supervision of relevant authorities and announced publicly. The winner must claim the prize within 14 days of announcement and provide all required documents. Failure to do so will result in forfeiture.</p>
        <p dir="rtl">سيتم اختيار الفائز عشوائيًا تحت إشراف الجهات المختصة والإعلان عنه علنًا. يجب على الفائز استلام الجائزة خلال 14 يومًا من الإعلان وتقديم جميع المستندات المطلوبة، وإلا تعتبر الجائزة ملغاة.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Winner Eligibility & Prize Transfer | أهلية الفائز وتحويل الجائزة</h5>
        <p class="mb-1 text-muted">If the winner is over 18 years old but does not hold a valid driving license, they may register the car under the name of a first-degree relative only.</p>
        <p dir="rtl">يحق للفائز الذي يبلغ من العمر أكثر من 18 سنة أو لا يملك رخصة قيادة، تسجيل السيارة باسم قريب من الدرجة الأولى فقط.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Car Insurance | تأمين السيارة</h5>
        <p class="mb-1 text-muted">Car insurance is not provided. It is the responsibility of the winner to insure the car.</p>
        <p dir="rtl">لا يتم توفير تأمين السيارة. تقع مسؤولية تأمين السيارة على الفائز.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Car Registration Fees | رسوم تسجيل السيارة</h5>
        <p class="mb-1 text-muted">The registration cost for the car will be managed by the winner.</p>
        <p dir="rtl">ستكون تكلفة رسوم تسجيل السيارة على عاتق الفائز.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Event Changes | تغييرات الفعالية</h5>
        <p class="mb-1 text-muted">The organizers reserve the right to modify or cancel the event at any time without prior notice.</p>
        <p dir="rtl">يحتفظ المنظمون بالحق في تعديل أو إلغاء الفعالية في أي وقت دون إشعار مسبق.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Disqualification | الاستبعاد</h5>
        <p class="mb-1 text-muted">Any attempt to manipulate or interfere with the event mechanism will result in disqualification.</p>
        <p dir="rtl">أي محاولة للتلاعب أو التدخل في آلية الفعالية ستؤدي إلى الاستبعاد.</p>
    </div>

    <div class="term-item mb-3">
        <h5 class="mb-2">Additional Clauses | البنود الإضافية</h5>
        <p class="mb-1 text-muted">Company shall not be liable for any technical malfunctions, system errors, or failures that may affect participation in the activation, including but not limited to network issues, device compatibility, or unforeseen interruptions. The Company is also not responsible for any misinterpretation of the rules, errors in participation, or disputes arising from the activation.</p>
        <p dir="rtl" class="mb-2">لا تتحمل الشركة أي مسؤولية عن أي أعطال تقنية أو أخطاء في النظام أو إخفاقات قد تؤثر على المشاركة في الفعالية، بما في ذلك، على سبيل المثال لا الحصر، مشكلات الشبكة، توافق الأجهزة مع النظام، أو الانقطاعات غير المتوقعة. كما أن الشركة غير مسؤولة عن أي سوء فهم لقواعد الفعالية، أخطاء في المشاركة، أو أي نزاعات تنشأ عنها.</p>
        
        <p class="mb-1 text-muted">Furthermore, the Company does not guarantee or warrant the functionality, security, or uninterrupted operation of the activation process. The Company reserves the absolute right to modify, suspend, or cancel the activation at any time without prior notice and without incurring any liability.</p>
        <p dir="rtl" class="mb-2">علاوة على ذلك، لا تضمن الشركة تشغيل الفعالية دون انقطاع أو خلوها من الأخطاء أو المشكلات الأمنية. وتحتفظ الشركة بالحق المطلق في تعديل أو تعليق أو إلغاء الفعالية في أي وقت دون إشعار مسبق ودون أي مسؤولية قانونية.</p>
        
        <p class="mb-1 text-muted">By participating in this activation, participants acknowledge and agree that the Company’s decisions shall be final and binding in all matters related to the activation, and no claims, disputes, or challenges shall be entertained.</p>
        <p dir="rtl" class="mb-2">من خلال المشاركة في هذه الفعالية، يقر المشاركون ويوافقون على أن قرارات الشركة نهائية وملزمة في جميع الأمور المتعلقة بالفعالية، ولن يتم قبول أي مطالبات أو نزاعات أو اعتراضات.</p>

        <p class="mb-1 text-muted">By filling your registration, you automatically agree to the above conditions.</p>
        <p dir="rtl">من خلال ملء استمارة التسجيل الخاصة بك، فإنك توافق تلقائيًا على الشروط المذكورة أعلاه.</p>
    </div>
</div>';


$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $t['title'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --brand-orange: #f97630;
            --brand-blue: #193a63;
            --brand-dark: #0f2440;
            --brand-light: #f0f4f8;
            --surface: #ffffff;
            --text-main: #193a63;
            --text-light: #64748b;
            --border: #e2e8f0;
            --radius-sm: 12px;
            --radius-md: 20px;
            --radius-lg: 32px;
            --shadow-sm: 0 4px 6px -1px rgba(25, 58, 99, 0.05);
            --shadow-md: 0 10px 15px -3px rgba(25, 58, 99, 0.08);
            --shadow-lg: 0 25px 50px -12px rgba(25, 58, 99, 0.15);
        }
        
        body {
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            color: var(--text-main);
            font-family: 'Tajawal', sans-serif;
            overflow-x: hidden;
        }

        /* LTR Overrides */
        html[dir="ltr"] body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: ltr;
        }
        
        html[dir="ltr"] .form-control {
            direction: ltr;
        }

        /* Language Switcher */
        .lang-switch {
            position: absolute;
            top: 2rem;
            <?= $lang === 'ar' ? 'left' : 'right' ?>: 2rem;
            z-index: 100;
        }
        
        .lang-btn {
            background: rgba(255, 255, 255, 0.9);
            color: var(--brand-blue);
            border: 1px solid rgba(255, 255, 255, 0.5);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .lang-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            background: white;
        }
        
        .register-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(249, 118, 48, 0.03) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(25, 58, 99, 0.03) 0%, transparent 40%);
        }
        
        /* Header with Logos */
        .header-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background: transparent;
            gap: 3rem;
            position: relative;
            z-index: 10;
        }
        
        .logo {
            height: 70px;
            max-width: 180px;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.02);
        }
        
        .logo-placeholder {
            height: 60px;
            padding: 0.5rem 1rem;
            background: rgba(25, 58, 99, 0.05);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-blue);
            font-weight: 700;
            font-size: 0.875rem;
            border: 2px dashed rgba(25, 58, 99, 0.2);
        }
        
        /* Hero Section */
        .hero {
            padding: 2rem 2rem 3rem;
            text-align: center;
            position: relative;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .car-visual-img {
            max-width: 280px;
            width: 100%;
            height: auto;
            margin-bottom: 2rem;
            filter: drop-shadow(0 20px 40px rgba(25, 58, 99, 0.15));
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }
        
        .hero h1 {
            color: var(--brand-blue);
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 1rem;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }
        
        .hero h1 span {
            color: var(--brand-orange);
            position: relative;
            display: inline-block;
        }
        
        .hero h1 span::after {
            content: '';
            position: absolute;
            bottom: 10px;
            left: 0;
            width: 100%;
            height: 12px;
            background: rgba(249, 118, 48, 0.15);
            z-index: -1;
            border-radius: 4px;
        }
        
        .hero p {
            color: var(--text-light);
            font-size: 1.25rem;
            max-width: 500px;
            margin: 0 auto;
            font-weight: 500;
        }
        
        /* Prize Badge */
        .prize-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--brand-orange) 0%, #ff8f4d 100%);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 1.5rem;
            box-shadow: 0 8px 20px rgba(249, 118, 48, 0.3);
            animation: pulse 2s ease-in-out infinite;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(249, 118, 48, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(249, 118, 48, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(249, 118, 48, 0); }
        }
        
        /* Registration Content */
        .register-content {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0 2rem 4rem;
        }
        
        .register-card {
            width: 100%;
            max-width: 480px;
            perspective: 1000px;
        }
        
        /* Card Styling */
        .card {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255,255,255,0.5);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -15px rgba(25, 58, 99, 0.2);
        }
        
        .card-header {
            background: var(--surface);
            padding: 2rem 2rem 1rem;
            border-bottom: none;
            margin: 0;
        }
        
        .card-title {
            color: var(--brand-blue);
            margin: 0;
            font-weight: 800;
            font-size: 1.75rem;
            text-align: center;
        }
        
        form {
            padding: 1rem 2rem 2.5rem;
        }
        
        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            background: #f8fafc;
            border: 2px solid transparent;
            border-radius: var(--radius-md);
            padding: 1.1rem 1.25rem;
            font-size: 1.05rem;
            font-weight: 500;
            color: var(--brand-dark);
            transition: all 0.2s ease;
            width: 100%;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
        }
        
        .form-control:focus {
            background: white;
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 4px rgba(249, 118, 48, 0.1);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #94a3b8;
        }
        
        .form-label {
            color: var(--brand-blue);
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.75rem;
            display: block;
        }
        
        .form-text {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        /* Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-orange) 0%, #e55a1b 100%);
            border: none;
            border-radius: var(--radius-md);
            padding: 1.1rem 2rem;
            font-size: 1.15rem;
            font-weight: 800;
            color: white;
            box-shadow: 0 10px 25px -5px rgba(249, 118, 48, 0.4);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(255,255,255,0.2), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(249, 118, 48, 0.5);
        }
        
        .btn-primary:hover::after {
            opacity: 1;
        }

        .btn-primary:disabled {
            background: #cbd5e1;
            box-shadow: none;
            cursor: not-allowed;
        }
        
        /* Location Status */
        .location-status {
            border-radius: var(--radius-md);
            padding: 1.25rem;
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-align: center;
            box-shadow: var(--shadow-sm);
            backdrop-filter: blur(5px);
        }
        
        .location-status.checking {
            background: rgba(255, 255, 255, 0.8);
            color: var(--brand-blue);
            border: 1px solid var(--border);
        }
        
        .location-status.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .location-status.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .location-status .spinner {
            width: 20px;
            height: 20px;
            border: 2.5px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        
        /* Success Card */
        .success-animation {
            text-align: center;
            padding: 3rem 2rem;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 4rem;
            margin: 0 auto 2rem;
            box-shadow: 0 15px 30px -5px rgba(34, 197, 94, 0.4);
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        @keyframes popIn {
            0% { transform: scale(0) rotate(-45deg); opacity: 0; }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }
        
        .success-animation h2 {
            color: var(--brand-blue);
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
            font-size: 0.9rem;
            background: white;
            border-top: 1px solid var(--border);
        }
        
        /* Terms Styles */
        .terms-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            color: var(--text-main);
            cursor: pointer;
        }
        
        .terms-content {
            max-height: 60vh;
            overflow-y: auto;
            padding: 0.5rem;
        }
        
        .term-item {
            background: #f8fafc;
            border-radius: var(--radius-sm);
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }
        
        .term-item h5 {
            color: var(--brand-blue);
            font-weight: 800;
        }

        /* Modal Redesign */
        .modal-overlay {
            background: rgba(15, 36, 64, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal {
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            border: none;
        }

        .modal-header {
            background: var(--brand-blue);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
        }

        .modal-title {
            color: white;
        }

        .modal-close {
            color: white;
            opacity: 0.8;
        }

        .modal-close:hover {
            opacity: 1;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .header-logos {
                padding: 1.5rem;
                gap: 1.5rem;
            }
            .logo {
                height: 50px;
            }
            .hero {
                padding-bottom: 2rem;
            }
            .hero h1 {
                font-size: 2.25rem;
            }
            .card-title {
                font-size: 1.5rem;
            }
            .register-content {
                padding: 0 1rem 3rem;
            }
            .car-visual-img {
                max-width: 200px;
            }
            .lang-switch {
                top: 1.5rem;
                <?= $lang === 'ar' ? 'left' : 'right' ?>: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-page">
        <!-- Header with Logos -->
        <header class="header-logos">
            <div class="lang-switch">
                <a href="?lang=<?= $next_lang ?>" class="lang-btn"><?= $t['switch_lang'] ?></a>
            </div>
            <img src="logos/HAMAT.png" alt="<?= $t['alt_hamat'] ?>" class="logo" onerror="this.outerHTML='<div class=\'logo-placeholder\'><?= $t['ph_hamat'] ?></div>'">
            <img src="logos/logo -Family Bonds.png" alt="<?= $t['alt_fb'] ?>" class="logo" onerror="this.style.display='none'">
        </header>

        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <img src="logos/logo -Family Bonds.png" alt="<?= $t['alt_fb'] ?>" class="car-visual-img">
                <h1><?= $t['hero_title'] ?></h1>
                <p><?= $t['hero_desc'] ?></p>
                <div class="prize-badge"><?= $t['grand_prize'] ?></div>
            </div>
        </div>

        <!-- Registration Content -->
        <div class="register-content">
            <div class="register-card">
                <!-- Location Check -->
                <div id="locationStatus" class="location-status checking">
                    <div class="spinner"></div>
                    <span><?= $t['checking_loc'] ?></span>
                </div>

                <!-- Registration Form -->
                <div class="card" id="registrationCard" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title"><?= $t['reg_form'] ?></h3>
                    </div>

                    <form id="registrationForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="mall_id" id="mall_id">

                        <div class="form-group">
                            <label class="form-label"><?= $t['full_name'] ?></label>
                            <input type="text" name="name" class="form-control" placeholder="<?= $t['name_ph'] ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= $t['mobile'] ?></label>
                            <input type="tel" name="phone" class="form-control" placeholder="05xxxxxxxx" required pattern="^(05|5|9665|00966)[0-9]{8}$">
                            <span class="form-text"><?= $t['mobile_help'] ?></span>
                        </div>

                        <div class="form-group">
                            <label class="form-label"><?= $t['mall'] ?></label>
                            <input type="text" id="mallName" class="form-control" readonly>
                        </div>

                        <div class="form-group mb-4" style="text-align: center; font-size: 0.9rem;">
                            <span><?= $t['terms_agree'] ?> <a href="#" onclick="openTerms(event)" style="color: var(--brand-orange); text-decoration: underline;"><?= $t['terms_link'] ?></a></span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                            <span><?= $t['submit'] ?></span>
                        </button>
                    </form>
                </div>

                <!-- Success Message -->
                <div class="card" id="successCard" style="display: none;">
                    <div class="success-animation">
                        <div class="success-icon">✓</div>
                        <h2><?= $t['success_title'] ?></h2>
                        <p class="mb-3"><?= $t['success_msg1'] ?></p>
                        <p><?= $t['success_msg2'] ?></p>
                    </div>
                </div>

                <!-- Error Message -->
                <div class="card" id="errorCard" style="display: none;">
                    <div class="alert alert-danger" id="errorMessage">
                        <span>⚠️</span>
                        <span id="errorText"></span>
                    </div>
                    <button class="btn btn-secondary btn-block" onclick="location.reload()"><?= $t['retry'] ?></button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p><?= $t['footer'] ?></p>
        </footer>
    </div>

    <!-- Terms Modal -->
    <div class="modal-overlay" id="termsModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title"><?= $t['terms_title'] ?></h3>
                <button class="modal-close" onclick="closeTerms()">&times;</button>
            </div>
            <div class="modal-body">
                <?= $terms_content ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-block" onclick="closeTerms()">I Agree / موافق</button>
            </div>
        </div>
    </div>

    <script>
        const locationStatus = document.getElementById('locationStatus');
        const registrationCard = document.getElementById('registrationCard');
        const successCard = document.getElementById('successCard');
        const errorCard = document.getElementById('errorCard');
        const errorText = document.getElementById('errorText');
        const registrationForm = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');

        function openTerms(e) {
            e.preventDefault();
            document.getElementById('termsModal').classList.add('active');
        }

        function closeTerms() {
            document.getElementById('termsModal').classList.remove('active');
        }

        // Translations for JS
        const lang = '<?= $lang ?>';
        const jsTrans = {
            browser_no_geo: '<?= $t['js_browser_no_geo'] ?>',
            unable_loc: '<?= $t['js_unable_loc'] ?>',
            allow_loc: '<?= $t['js_allow_loc'] ?>',
            loc_unavailable: '<?= $t['js_loc_unavailable'] ?>',
            timeout: '<?= $t['js_timeout'] ?>',
            error_verify: '<?= $t['js_error_verify'] ?>',
            loc_verified: '<?= $t['js_loc_verified'] ?>',
            generic_error: '<?= $t['js_generic_error'] ?>',
            registering: '<?= $t['registering'] ?>',
            submit: '<?= $t['submit'] ?>'
        };

        // Check location on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkLocation();
        });

        function checkLocation() {
            if (!navigator.geolocation) {
                showLocationError(jsTrans.browser_no_geo);
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                    
                    verifyLocation(lat, lng);
                },
                function(error) {
                    let message = jsTrans.unable_loc;
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            message = jsTrans.allow_loc;
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = jsTrans.loc_unavailable;
                            break;
                        case error.TIMEOUT:
                            message = jsTrans.timeout;
                            break;
                    }
                    showLocationError(message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function verifyLocation(lat, lng) {
            fetch('api/check-location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Lang': lang
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng,
                    csrf_token: document.querySelector('input[name="csrf_token"]').value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    locationStatus.className = 'location-status success';
                    // Use localized mall name if available (API needs to support it or we just show the name)
                    // Assuming API returns 'mall_name' (Arabic) and maybe 'mall_name_en'
                    // For now, we use the name provided by API
                    const mallName = lang === 'en' && data.data.mall_name_en ? data.data.mall_name_en : data.data.mall_name;
                    
                    locationStatus.innerHTML = '<span>✓</span><span>' + jsTrans.loc_verified + mallName + '</span>';
                    
                    document.getElementById('mall_id').value = data.data.mall_id;
                    document.getElementById('mallName').value = mallName;
                    
                    registrationCard.style.display = 'block';
                } else {
                    showLocationError(data.message);
                }
            })
            .catch(error => {
                showLocationError(jsTrans.error_verify);
            });
        }

        function showLocationError(message) {
            locationStatus.className = 'location-status error';
            locationStatus.innerHTML = '<span>⚠️</span><span>' + message + '</span>';
        }

        // Form submission
        registrationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner" style="width: 20px; height: 20px;"></div><span>' + jsTrans.registering + '</span>';
            
            const formData = new FormData(registrationForm);
            
            fetch('api/register.php', {
                method: 'POST',
                headers: {
                    'X-Lang': lang
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    registrationCard.style.display = 'none';
                    locationStatus.style.display = 'none';
                    successCard.style.display = 'block';
                } else {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span>' + jsTrans.submit + '</span>';
                    alert(data.message);
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>' + jsTrans.submit + '</span>';
                alert(jsTrans.generic_error);
            });
        });
    </script>
</body>
</html>
