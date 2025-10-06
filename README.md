# MWDB Guest UI

A lightweight, secure web interface that provides unauthenticated users with read-only access to basic [MWDB (Malware Database)](https://github.com/CERT-Polska/mwdb-core) functionalities. Perfect for sharing malware samples and analysis results with external researchers or teams without requiring full system access.

## Table of Contents

- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Technology Stack](#technology-stack)
- [Security](#security)
- [Contributing](#contributing)
- [License](#license)

## Features

- **🔍 Hash-based File Search**: Search for malware samples using MD5, SHA1, SHA256, or SHA512 hashes
- **📤 File Upload**: Upload malware samples directly to MWDB (with size limits and validation)
- **📊 Recent Files View**: Browse recently uploaded files with metadata
- **🛡️ Security First**: 
  - CSRF protection on all forms
  - CAPTCHA support (Google reCAPTCHA v3 or custom self-hosted)
  - Input validation and sanitization
  - Comprehensive security headers (CSP, X-Frame-Options, etc.)
  - SSL/TLS certificate verification for API calls
  - Configurable timeouts on API requests
- **🐳 Docker Ready**: Easy deployment with Docker and Docker Compose
- **🎨 Clean UI**: Simple, responsive interface optimized for guest access
- **🔐 Environment-based Configuration**: Secure configuration management using environment variables

## Prerequisites

Before installing MWDB Guest UI, ensure you have:

- **Docker** (20.10 or higher) and **Docker Compose** (optional, for easier deployment)
- Access to a running **MWDB API instance** ([mwdb-core](https://github.com/CERT-Polska/mwdb-core))
- A valid **MWDB API key** with appropriate permissions
- *(Optional)* **Google reCAPTCHA v3 keys** if you plan to use reCAPTCHA instead of custom CAPTCHA

## Installation

### Quick Start with Docker

1. **Clone the repository**:
   ```bash
   git clone https://github.com/dyussekeyev/mwdb-guest-ui.git
   cd mwdb-guest-ui
   ```

2. **Configure environment variables**:
   ```bash
   cp .env.example .env
   ```
   
   Edit `.env` with your configuration:
   ```env
   API_URL=http://your-mwdb-api:5000/api
   API_KEY=your_mwdb_api_key
   CAPTCHA_TYPE=recaptcha  # or 'custom' for self-hosted CAPTCHA
   RECAPTCHA_SITE_KEY=your_site_key  # if using reCAPTCHA
   RECAPTCHA_SECRET_KEY=your_secret_key  # if using reCAPTCHA
   ```

3. **Build the Docker image**:
   ```bash
   docker build -t mwdb-guest-ui .
   ```

4. **Run the container**:
   ```bash
   docker run -d -p 8000:80 --name mwdb-guest-ui mwdb-guest-ui
   ```

5. **Access the application**:
   Open your browser and navigate to `http://localhost:8000`

### Alternative: Docker Compose (Recommended)

If you have a `docker-compose.yml` file in your project:

```bash
docker-compose up -d
```

## Configuration

### Environment Variables

All configuration is managed through environment variables stored in the `.env` file:

| Variable | Required | Description | Example |
|----------|----------|-------------|---------|
| `API_URL` | ✅ Yes | URL of your MWDB API endpoint | `http://mwdb-api:5000/api` |
| `API_KEY` | ✅ Yes | Your MWDB API key (keep secret!) | `your_api_key_here` |
| `CAPTCHA_TYPE` | ✅ Yes | CAPTCHA implementation: `recaptcha` or `custom` | `recaptcha` |
| `RECAPTCHA_SITE_KEY` | ⚠️ Conditional | Google reCAPTCHA site key (required if `CAPTCHA_TYPE=recaptcha`) | `6LeIxAcTAAAAAJ...` |
| `RECAPTCHA_SECRET_KEY` | ⚠️ Conditional | Google reCAPTCHA secret key (required if `CAPTCHA_TYPE=recaptcha`) | `6LeIxAcTAAAAAG...` |

### CAPTCHA Options

**Option 1: Google reCAPTCHA v3** (Recommended for production)
- Invisible, user-friendly CAPTCHA
- Requires Google API keys ([Get them here](https://www.google.com/recaptcha))
- Better bot detection
- Sends data to Google servers

**Option 2: Custom CAPTCHA** (Self-hosted)
- No external dependencies
- Complete data privacy
- Requires manual user input
- Simpler but less sophisticated

### File Upload Limits

- Maximum file size: **10 MB** (configurable in `uploads.ini`)
- Supported hash formats: MD5, SHA1, SHA256, SHA512

## Usage

### Searching for Files

1. Navigate to the **File Search** tab
2. Enter a hash value (MD5, SHA1, SHA256, or SHA512)
3. Complete the CAPTCHA challenge
4. Click **Search** to retrieve file information

### Uploading Files

1. Navigate to the **File Upload** tab
2. Select a file (max 10 MB)
3. Complete the CAPTCHA challenge
4. Click **Upload** to submit the file to MWDB

### Viewing Recent Files

The main page displays a table of recently uploaded files with:
- File name
- File type
- Associated tags
- Upload timestamp

Click on any file to view detailed information.

## Technology Stack

- **Backend**: PHP 8.1 with Apache
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Container**: Docker with multi-stage builds
- **Security**: 
  - CSRF tokens (native PHP session management)
  - CAPTCHA (Google reCAPTCHA v3 or GD library for custom)
  - Security headers implementation
  - SSL/TLS verification via cURL
- **Dependencies**: 
  - GD library for custom CAPTCHA generation
  - Python 3 for startup scripts

## Security

Security is a top priority for MWDB Guest UI. For comprehensive security information, please see [SECURITY.md](SECURITY.md).

### Key Security Features

- ✅ **CSRF Protection**: All forms include CSRF tokens
- ✅ **CAPTCHA Support**: Bot protection with reCAPTCHA v3 or custom CAPTCHA
- ✅ **Input Validation**: Strict validation of hashes and file uploads
- ✅ **Security Headers**: CSP, X-Frame-Options, X-Content-Type-Options, etc.
- ✅ **SSL/TLS Verification**: Certificate verification for all API calls
- ✅ **Timeout Protection**: Configurable timeouts prevent hanging requests
- ✅ **Session Security**: Secure PHP session configuration
- ✅ **Non-root Container**: Runs as `www-data` user for enhanced security

### Best Practices

- Always deploy behind **HTTPS** in production
- Implement **rate limiting** at the web server level (see [SECURITY.md](SECURITY.md) for examples)
- Regularly **rotate API keys**
- Keep PHP and dependencies **up to date**
- Monitor logs for **suspicious activity**

### Reporting Security Issues

If you discover a security vulnerability:
1. **Do not** open a public issue
2. Contact the maintainer directly
3. Provide detailed information about the vulnerability
4. Allow time for a fix before public disclosure

## Contributing

Contributions are welcome! To contribute:

1. **Fork** the repository
2. **Create** a feature branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
3. **Make** your changes and commit:
   ```bash
   git commit -am 'Add: your feature description'
   ```
4. **Push** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```
5. **Open** a Pull Request with a clear description of your changes

### Development Guidelines

- Follow existing code style and conventions
- Test your changes thoroughly
- Update documentation as needed
- Ensure security best practices are maintained

## License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for full details.

---

**Note**: MWDB Guest UI is designed for unauthenticated access and should be deployed with appropriate security measures. For production use, always implement rate limiting, use HTTPS, and follow the security guidelines in [SECURITY.md](SECURITY.md).

For more information about MWDB, visit the [mwdb-core project by CERT Polska](https://github.com/CERT-Polska/mwdb-core).
