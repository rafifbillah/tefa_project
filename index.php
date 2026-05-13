<?php
require_once 'core/Auth.php';

if (Auth::isLoggedIn()) {
    header('Location: ' . Auth::getDashboardUrl(Auth::getRole()));
} else {
    header('Location: login.php');
}
exit;