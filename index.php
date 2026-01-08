<?php
/**
 * Customer Registration Page
 * Car Raffle System
 */

require_once __DIR__ . '/includes/init.php';

$csrf_token = Security::generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سحب السيارة - تسجيل المشاركة</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .car-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="register-page">
        <!-- Hero Section -->
        <div class="hero">
            <div class="hero-content">
                <div class="car-icon">🚗</div>
                <h1>سحب على سيارة</h1>
                <p>سجّل الآن واحصل على فرصة للفوز بسيارة جديدة!</p>
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
                        <p>سيتم التواصل معك في حال الفوز. حظاً سعيداً!</p>
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
        <footer style="text-align: center; padding: 1rem; color: var(--gray-500); font-size: 0.875rem;">
            جميع الحقوق محفوظة © <?= date('Y') ?>
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
