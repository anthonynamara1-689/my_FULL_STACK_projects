<?php
function renderPage(string $title, string $subtitle, string $pageKey): void
{
    $page = $pageKey;
    require_once __DIR__ . '/../sidebar.php';
    echo '<div class="main">';
    echo '<header class="topbar">';
    echo '<button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>';
    echo '<div class="topbar-left"><h1>' . htmlspecialchars($title) . '</h1><p class="page-sub">' . htmlspecialchars($subtitle) . '</p></div>';
    echo '<div class="topbar-right"></div>';
    echo '</header>';
    echo '<div class="content">';
}

function endRenderPage(): void
{
    echo '</div></div>';
}
