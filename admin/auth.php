<?php
require_once dirname(__DIR__) . '/includes/config.php';

function requireAdmin() {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}
