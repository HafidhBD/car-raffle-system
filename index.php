<?php
/**
 * Customer Registration Page
 * Car Raffle System - Hamat Campaign
 */

require_once __DIR__ . '/includes/init.php';

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سحب السيارة - حمات</title>
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
        
        .register-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header with Logos */
        .header-logos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: var(--brand-blue);
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
        
        .car-visual {
            font-size: 6rem;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 10px 30px rgba(249, 118, 48, 0.4));
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
            .car-visual {
                font-size: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-page">
        <!-- Header with Logos -->
        <header class="header-logos">
            <img src="logos/HAMAT.png" alt="حمات" class="logo" onerror="this.outerHTML='<div class=\'logo-placeholder\'>شعار حمات</div>'">
            <img src="logos/logo -Family Bonds.png" alt="الحملة" class="logo" onerror="this.outerHTML='<div class=\'logo-placeholder\'>شعار الحملة</div>'">
        </header>

        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <div class="car-visual">🚗</div>
                <h1>سحب على <span>سيارة</span></h1>
                <p>سجّل الآن واحصل على فرصة للفوز بسيارة جديدة!</p>
                <div class="prize-badge">🎉 الجائزة الكبرى</div>
            </div>
        </div>

        <!-- Registration Content -->
        <div class="register-content">
            <div class="register-card">
                <!-- Location Check -->
                <div id="locationStatus" class="location-status checking">
                    <div class="spinner"></div>
                    <span>جاري التحقق من موقعك...</span>
                </div>

                <!-- Registration Form -->
                <div class="card" id="registrationCard" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">نموذج التسجيل</h3>
                    </div>

                    <form id="registrationForm" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">
                        <input type="hidden" name="mall_id" id="mall_id">

                        <div class="form-group">
                            <label class="form-label">الاسم الكامل</label>
                            <input type="text" name="name" class="form-control" placeholder="أدخل اسمك الكامل" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">رقم الجوال</label>
                            <input type="tel" name="phone" class="form-control" placeholder="05xxxxxxxx" required pattern="^(05|5|9665|00966)[0-9]{8}$">
                            <span class="form-text">أدخل رقم الجوال السعودي</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">المول</label>
                            <input type="text" id="mallName" class="form-control" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submitBtn">
                            <span>تسجيل المشاركة</span>
                        </button>
                    </form>
                </div>

                <!-- Success Message -->
                <div class="card" id="successCard" style="display: none;">
                    <div class="success-animation">
                        <div class="success-icon">✓</div>
                        <h2>تم التسجيل بنجاح!</h2>
                        <p class="mb-3">شكراً لك على المشاركة في السحب على السيارة.</p>
                        <p>سيتم التواصل معك في حال الفوز. حظاً سعيداً! 🍀</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div class="card" id="errorCard" style="display: none;">
                    <div class="alert alert-danger" id="errorMessage">
                        <span>⚠️</span>
                        <span id="errorText"></span>
                    </div>
                    <button class="btn btn-secondary btn-block" onclick="location.reload()">إعادة المحاولة</button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="footer-logos">
                <img src="logos/HAMAT.png" alt="حمات" class="footer-logo" onerror="this.style.display='none'">
            </div>
            <p>جميع الحقوق محفوظة © <?= date('Y') ?> حمات</p>
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

        // Check location on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkLocation();
        });

        function checkLocation() {
            if (!navigator.geolocation) {
                showLocationError('متصفحك لا يدعم تحديد الموقع');
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
                    let message = 'تعذر تحديد موقعك';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            message = 'يرجى السماح بالوصول إلى موقعك للمتابعة';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message = 'معلومات الموقع غير متاحة';
                            break;
                        case error.TIMEOUT:
                            message = 'انتهت مهلة طلب الموقع';
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
                    'Content-Type': 'application/json'
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
                    locationStatus.innerHTML = '<span>✓</span><span>تم التحقق من موقعك - ' + data.data.mall_name + '</span>';
                    
                    document.getElementById('mall_id').value = data.data.mall_id;
                    document.getElementById('mallName').value = data.data.mall_name;
                    
                    registrationCard.style.display = 'block';
                } else {
                    showLocationError(data.message);
                }
            })
            .catch(error => {
                showLocationError('حدث خطأ في التحقق من الموقع');
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
            submitBtn.innerHTML = '<div class="spinner" style="width: 20px; height: 20px;"></div><span>جاري التسجيل...</span>';
            
            const formData = new FormData(registrationForm);
            
            fetch('api/register.php', {
                method: 'POST',
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
                    submitBtn.innerHTML = '<span>تسجيل المشاركة</span>';
                    alert(data.message);
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>تسجيل المشاركة</span>';
                alert('حدث خطأ. يرجى المحاولة مرة أخرى.');
            });
        });
    </script>
</body>
</html>
