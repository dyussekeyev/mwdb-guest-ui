# Security Considerations

This document outlines important security considerations when deploying and using MWDB Guest UI.

## Configuration

### Environment Variables

All sensitive configuration should be stored in environment variables, never in code:

- `API_URL`: The URL of your MWDB API endpoint
- `API_KEY`: Your MWDB API key (keep this secret!)
- `CAPTCHA_TYPE`: Either `recaptcha` or `custom`
- `RECAPTCHA_SITE_KEY`: Your reCAPTCHA site key (if using reCAPTCHA)
- `RECAPTCHA_SECRET_KEY`: Your reCAPTCHA secret key (if using reCAPTCHA)

### SSL/TLS Configuration

The application now enforces SSL/TLS certificate verification for all CURL requests to the API. If your API uses self-signed certificates, you should:

1. Use a proper CA-signed certificate in production
2. If testing with self-signed certificates, you can disable verification by setting:
   - `CURLOPT_SSL_VERIFYPEER` to `false`
   - `CURLOPT_SSL_VERIFYHOST` to `0`
   
   **WARNING**: Only do this in development environments, NEVER in production!

## Security Headers

The application implements several security headers:

- **X-Frame-Options**: Prevents clickjacking attacks
- **X-XSS-Protection**: Enables browser XSS protection
- **X-Content-Type-Options**: Prevents MIME type sniffing
- **Content-Security-Policy**: Restricts resource loading
- **Referrer-Policy**: Controls referrer information
- **Permissions-Policy**: Restricts browser features

## CAPTCHA

Two CAPTCHA options are available:

### Google reCAPTCHA v3
- More user-friendly (invisible)
- Requires Google API keys
- Sends data to Google servers

### Custom CAPTCHA
- Self-hosted, no third-party dependencies
- Requires manual user input
- Simpler but less sophisticated

## Input Validation

The application validates:
- Hash formats (MD5, SHA1, SHA256, SHA512)
- File upload sizes (max 10MB)
- CSRF tokens on all forms
- CAPTCHA responses

## Best Practices

1. **Use HTTPS**: Always deploy behind HTTPS in production
2. **Rate Limiting**: Consider implementing rate limiting at the web server level (nginx/apache)
3. **File Uploads**: The application limits uploads to 10MB. Adjust based on your needs
4. **Session Security**: Configure PHP session settings securely:
   ```ini
   session.cookie_httponly = 1
   session.cookie_secure = 1
   session.cookie_samesite = Strict
   ```
5. **API Key Security**: Rotate API keys regularly
6. **Logging**: Monitor error logs for suspicious activity
7. **Updates**: Keep PHP and all dependencies up to date

## Timeout Configuration

CURL requests have timeouts configured:
- Connection timeout: 10 seconds
- Total timeout: 30 seconds (60 seconds for file uploads)

Adjust these in the code if your API requires longer timeouts.

## Known Limitations

1. No built-in rate limiting (implement at web server level)
2. No account management (this is intentional for guest access)
3. Session hijacking protection relies on PHP session configuration
4. CAPTCHA can be bypassed by determined attackers (consider additional protections)

## Reporting Security Issues

If you discover a security vulnerability, please report it responsibly:
1. Do not open a public issue
2. Contact the maintainer directly
3. Provide detailed information about the vulnerability
4. Allow time for a fix before public disclosure
