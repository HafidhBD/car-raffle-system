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
        'switch_lang' => 'English'
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
        'switch_lang' => 'العربية'
    ]
];

$t = $trans[$lang];
$next_lang = $lang === 'ar' ? 'en' : 'ar';

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
            --brand-orange-light: #ffede5;
            --brand-blue-light: #e8edf3;
        }
        
        body {
            background: #f8fafc;
            min-height: 100vh;
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
            top: 1.5rem;
            <?= $lang === 'ar' ? 'left' : 'right' ?>: 2rem;
            z-index: 100;
        }
        
        .lang-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .register-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header with Logos */
        .header-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem 2rem;
            background: var(--brand-blue);
            gap: 2rem;
            position: relative;
        }
        
        .logo {
            height: 60px;
            max-width: 150px;
            object-fit: contain;
        }
        
        .logo-placeholder {
            height: 60px;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.875rem;
            border: 2px dashed rgba(255,255,255,0.3);
        }
        
        /* Hero Section */
        .hero {
            background: transparent;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
        }
        
        .hero::before {
            display: none;
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
        }
        
        .car-visual-img {
            max-width: 200px;
            height: auto;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 10px 30px rgba(249, 118, 48, 0.3));
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .hero h1 {
            color: var(--brand-blue);
            font-size: 2.75rem;
            font-weight: 900;
            margin-bottom: 0.75rem;
        }
        
        .hero h1 span {
            color: var(--brand-orange);
        }
        
        .hero p {
            color: #64748b;
            font-size: 1.25rem;
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Prize Badge */
        .prize-badge {
            display: inline-block;
            background: linear-gradient(135deg, var(--brand-orange) 0%, #e55a1b 100%);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 1.5rem;
            box-shadow: 0 8px 25px rgba(249, 118, 48, 0.4);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.03); }
        }
        
        /* Registration Content */
        .register-content {
            flex: 1;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 0 2rem 2rem;
        }
        
        .register-card {
            width: 100%;
            max-width: 450px;
        }
        
        /* Card Styling */
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: none;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--brand-blue) 0%, #0d2847 100%);
            border-radius: 20px 20px 0 0;
            padding: 1.25rem 1.5rem;
            margin: -2rem -2rem 1.5rem -2rem;
        }
        
        .card-title {
            color: white;
            margin: 0;
            font-weight: 700;
        }
        
        /* Form Styling */
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--brand-orange);
            box-shadow: 0 0 0 4px rgba(249, 118, 48, 0.15);
        }
        
        .form-label {
            color: var(--brand-blue);
            font-weight: 600;
        }
        
        /* Button */
        .btn-primary {
            background: linear-gradient(135deg, var(--brand-orange) 0%, #e55a1b 100%);
            border: none;
            border-radius: 12px;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(249, 118, 48, 0.35);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(249, 118, 48, 0.45);
        }
        
        /* Location Status */
        .location-status {
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .location-status.checking {
            background: var(--brand-blue-light);
            color: var(--brand-blue);
        }
        
        .location-status.success {
            background: #dcfce7;
            color: #166534;
        }
        
        .location-status.error {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .location-status .spinner {
            border-color: var(--brand-blue);
            border-top-color: transparent;
        }
        
        /* Success Card */
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--brand-orange) 0%, #e55a1b 100%);
            font-size: 3rem;
        }
        
        .success-animation h2 {
            color: var(--brand-blue);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 1.5rem;
            color: #64748b;
            font-size: 0.875rem;
        }
        
        .footer-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            margin-bottom: 1rem;
        }
        
        .footer-logo {
            height: 40px;
            opacity: 0.8;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .header-logos {
                padding: 1rem;
            }
            .logo, .logo-placeholder {
                height: 45px;
            }
            .hero h1 {
                font-size: 2rem;
            }
            .hero p {
                font-size: 1rem;
            }
            .car-visual-img {
                max-width: 150px;
            }
            .lang-switch {
                top: 1rem;
                <?= $lang === 'ar' ? 'left' : 'right' ?>: 1rem;
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

    <script>
        const locationStatus = document.getElementById('locationStatus');
        const registrationCard = document.getElementById('registrationCard');
        const successCard = document.getElementById('successCard');
        const errorCard = document.getElementById('errorCard');
        const errorText = document.getElementById('errorText');
        const registrationForm = document.getElementById('registrationForm');
        const submitBtn = document.getElementById('submitBtn');

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
