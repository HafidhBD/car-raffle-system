<?php
/**
 * Admin Sidebar
 */

$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <img src="../logos/HAMAT.png" alt="هامات" style="height: 40px; filter: brightness(0) invert(1);">
    </div>

    <nav>
        <ul class="sidebar-nav">
            <li>
                <a href="index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📊</span>
                    <span>لوحة التحكم</span>
                </a>
            </li>
            <li>
                <a href="entries.php" class="<?= $current_page === 'entries.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📋</span>
                    <span>المشاركات</span>
                </a>
            </li>
            <li>
                <a href="promoters.php" class="<?= $current_page === 'promoters.php' ? 'active' : '' ?>">
                    <span class="nav-icon">👥</span>
                    <span>المروجين</span>
                </a>
            </li>
            <li>
                <a href="malls.php" class="<?= $current_page === 'malls.php' ? 'active' : '' ?>">
                    <span class="nav-icon">🏬</span>
                    <span>المولات</span>
                </a>
            </li>
            <li>
                <a href="settings.php" class="<?= $current_page === 'settings.php' ? 'active' : '' ?>">
                    <span class="nav-icon">⚙️</span>
                    <span>الإعدادات</span>
                </a>
            </li>
            <li style="margin-top: 2rem; border-top: 1px solid var(--gray-700); padding-top: 1rem;">
                <a href="logout.php">
                    <span class="nav-icon">🚪</span>
                    <span>تسجيل الخروج</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
