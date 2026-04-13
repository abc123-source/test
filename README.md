# PHP File Upload Vulnerability Demo

> **Warning:** This application is **intentionally vulnerable**. It is for educational, CTF, and security research purposes only. **Never deploy it on a public or production server.**

## What This Demonstrates

This demo illustrates **CWE-434: Unrestricted Upload of File with Dangerous Type**, listed under [OWASP A04:2021 – Insecure Design](https://owasp.org/Top10/A04_2021-Insecure_Design/).

### Files

| File | Purpose |
|------|---------|
| `index.php` | Upload form + vulnerable PHP backend |
| `webshell_example.php` | Example webshell an attacker would upload |
| `uploads/` | Web-accessible directory where uploads land |

## How the Vulnerability Works

The upload handler in `index.php` does **not**:

- Check the file extension (`.php`, `.phtml`, `.phar`, etc.)
- Validate the MIME type or magic bytes
- Rename the file to remove executable extensions

This means an attacker can upload `webshell_example.php` through the form. Because `uploads/` is served by the web server, visiting `http://host/uploads/webshell_example.php?cmd=id` causes PHP to **execute the shell** with the web server's privileges.

### Attack Flow

```
1. Attacker visits /index.php
2. Uploads webshell_example.php via the form
3. Server saves file to /uploads/webshell_example.php
4. Attacker visits /uploads/webshell_example.php?cmd=whoami
5. PHP executes whoami and returns the output → Remote Code Execution
```

## Running Locally (Docker – Isolated)

```bash
docker run --rm -p 8080:80 \
  -v "$(pwd)":/var/www/html \
  php:8.2-apache
```

Then open [http://localhost:8080](http://localhost:8080).

## How to Fix It (Secure Version)

1. **Allowlist extensions only** – accept `.jpg`, `.png`, `.pdf`, etc.
2. **Validate MIME type** using `finfo_file()` (not the user-supplied `Content-Type`).
3. **Rename the file** to a random UUID with a safe extension – never preserve the original name.
4. **Store uploads outside the web root** and serve them through a PHP proxy that sets `Content-Disposition: attachment`.
5. **Disable PHP execution** in the upload directory via `.htaccess`:

```apache
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
```

## References

- [OWASP: Unrestricted File Upload](https://owasp.org/www-community/vulnerabilities/Unrestricted_File_Upload)
- [CWE-434](https://cwe.mitre.org/data/definitions/434.html)
- [PortSwigger: File Upload Vulnerabilities](https://portswigger.net/web-security/file-upload)
