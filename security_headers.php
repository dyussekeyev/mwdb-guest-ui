<?php
// Security headers to protect against common web vulnerabilities

// Prevent clickjacking attacks
header("X-Frame-Options: DENY");

// Enable XSS protection
header("X-XSS-Protection: 1; mode=block");

// Prevent MIME type sniffing
header("X-Content-Type-Options: nosniff");

// Referrer policy
header("Referrer-Policy: strict-origin-when-cross-origin");

// Content Security Policy
// Allow inline scripts only for reCAPTCHA and the executeRecaptcha function
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com https://www.gstatic.com; frame-src https://www.google.com; style-src 'self' 'unsafe-inline'; img-src 'self' data:; connect-src 'self' https://www.google.com;");

// Permissions policy (formerly Feature-Policy)
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
?>
