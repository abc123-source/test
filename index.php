<?php
/**
 * ============================================================
 * INTENTIONALLY VULNERABLE PHP FILE UPLOAD DEMO
 * FOR EDUCATIONAL / SECURITY RESEARCH PURPOSES ONLY
 *
 * DO NOT DEPLOY THIS ON A PRODUCTION SERVER.
 * This page demonstrates a classic unrestricted file upload
 * vulnerability (CWE-434 / OWASP A04:2021).
 * ============================================================
 */

$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['userfile']) && $_FILES['userfile']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // VULNERABILITY: The filename is taken directly from user input with
        // only minimal sanitisation (path separators are stripped), but the
        // file EXTENSION is never checked.  A PHP webshell can therefore be
        // uploaded and executed by visiting /uploads/<filename>.
        $filename  = basename($_FILES['userfile']['name']);
        $destPath  = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $destPath)) {
            $message      = "File uploaded successfully: <a href=\"uploads/" . htmlspecialchars($filename) . "\">" . htmlspecialchars($filename) . "</a>";
            $messageClass = 'success';
        } else {
            $message      = 'Upload failed – could not move file.';
            $messageClass = 'error';
        }
    } else {
        $message      = 'No file received or upload error.';
        $messageClass = 'error';
    }
}

// List previously uploaded files
$uploads = [];
$uploadDir = __DIR__ . '/uploads/';
if (is_dir($uploadDir)) {
    foreach (new DirectoryIterator($uploadDir) as $f) {
        if (!$f->isDot()) {
            $uploads[] = $f->getFilename();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload – Vulnerability Demo</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #1a1a2e; color: #e0e0e0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 40px 16px; }
        .banner { background: #c0392b; color: #fff; border-radius: 8px; padding: 14px 24px; margin-bottom: 32px; max-width: 720px; width: 100%; font-size: 0.9rem; line-height: 1.6; }
        .banner strong { font-size: 1rem; display: block; margin-bottom: 4px; }
        .card { background: #16213e; border-radius: 12px; padding: 36px; max-width: 720px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,.4); }
        h1 { font-size: 1.6rem; margin-bottom: 8px; color: #e94560; }
        .subtitle { color: #888; margin-bottom: 28px; font-size: 0.9rem; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #a0aec0; }
        input[type=file] { display: block; margin-bottom: 20px; color: #e0e0e0; }
        button { background: #e94560; color: #fff; border: none; padding: 12px 28px; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: background .2s; }
        button:hover { background: #c0392b; }
        .msg { margin-top: 20px; padding: 12px 18px; border-radius: 8px; font-size: 0.9rem; }
        .msg.success { background: #1a472a; color: #6fcf97; }
        .msg.error   { background: #4a1010; color: #e57373; }
        .msg a       { color: #81d4fa; }
        .vuln-box { margin-top: 32px; background: #0d1b2a; border: 1px solid #e94560; border-radius: 8px; padding: 20px 24px; }
        .vuln-box h2 { color: #e94560; font-size: 1rem; margin-bottom: 10px; }
        .vuln-box code { display: block; background: #111; padding: 12px; border-radius: 6px; font-size: 0.82rem; color: #f8f8f2; white-space: pre-wrap; margin-bottom: 10px; }
        .vuln-box ul { padding-left: 18px; color: #a0aec0; font-size: 0.88rem; line-height: 1.8; }
        .uploaded-list { margin-top: 28px; }
        .uploaded-list h2 { color: #a0aec0; font-size: 1rem; margin-bottom: 10px; }
        .uploaded-list ul { list-style: none; padding: 0; }
        .uploaded-list li { padding: 6px 0; border-bottom: 1px solid #0d1b2a; }
        .uploaded-list a { color: #81d4fa; text-decoration: none; }
        .uploaded-list a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="banner">
    <strong>⚠ Intentionally Vulnerable Demo – Educational Use Only</strong>
    This application deliberately contains an unrestricted file upload vulnerability (CWE-434).
    Do <em>not</em> deploy this on a public or production server.
    It is intended for local security research, CTF practice, or classroom demonstrations.
</div>

<div class="card">
    <h1>File Upload Demo</h1>
    <p class="subtitle">Unrestricted File Upload Vulnerability (CWE-434)</p>

    <form method="POST" enctype="multipart/form-data">
        <label for="userfile">Select a file to upload:</label>
        <input type="file" id="userfile" name="userfile">
        <button type="submit">Upload</button>
    </form>

    <?php if ($message): ?>
        <div class="msg <?= $messageClass ?>"><?= $message ?></div>
    <?php endif; ?>

    <!-- ======================================================
         VULNERABILITY EXPLANATION
         ====================================================== -->
    <div class="vuln-box">
        <h2>Vulnerability Details</h2>
        <code><?php highlight_string('<?php
// No extension or MIME-type check – any file is accepted.
$filename = basename($_FILES["userfile"]["name"]);
move_uploaded_file($_FILES["userfile"]["tmp_name"],
    __DIR__ . "/uploads/" . $filename);
?>'); ?></code>
        <ul>
            <li>The upload handler accepts <strong>any file type</strong>, including <code>.php</code>.</li>
            <li>Uploaded files land inside <code>/uploads/</code>, a web-accessible directory.</li>
            <li>Visiting the uploaded file's URL causes PHP to <strong>execute it</strong>.</li>
            <li>An attacker can upload a webshell and gain remote code execution on the server.</li>
        </ul>
    </div>

    <?php if (!empty($uploads)): ?>
    <div class="uploaded-list">
        <h2>Uploaded Files</h2>
        <ul>
            <?php foreach ($uploads as $name): ?>
                <li>
                    <a href="uploads/<?= htmlspecialchars($name) ?>" target="_blank">
                        <?= htmlspecialchars($name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
