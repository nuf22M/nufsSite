<?php
// Contact Form Submissions Viewer
// Access this file to view all contact form submissions

session_start();

// Simple authentication (change this password)
$admin_password = 'gafe2025'; // Change this to a secure password

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Incorrect password";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: view_submissions.php');
    exit;
}

if (!isset($_SESSION['logged_in'])) {
    // Show login form
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Contact Form Submissions - Login</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 50px; }
            .login-box { background: white; padding: 30px; border-radius: 10px; max-width: 400px; margin: 0 auto; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            h2 { text-align: center; color: #333; margin-bottom: 30px; }
            input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px; }
            button { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer; }
            button:hover { background: #2980b9; }
            .error { color: red; text-align: center; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔐 Admin Login</h2>
            <?php if (isset($error)) echo '<div class="error">' . $error . '</div>'; ?>
            <form method="post">
                <input type="password" name="password" placeholder="Enter password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Show submissions
$log_file = 'contact_submissions.log';
$submissions = [];

if (file_exists($log_file)) {
    $content = file_get_contents($log_file);
    $entries = explode('---', $content);
    
    foreach ($entries as $entry) {
        if (trim($entry)) {
            $lines = explode("\n", trim($entry));
            $submission = [];
            foreach ($lines as $line) {
                if (strpos($line, ' - Contact Form Submission') !== false) {
                    $submission['date'] = trim(str_replace(' - Contact Form Submission', '', $line));
                } elseif (strpos($line, 'Name: ') === 0) {
                    $submission['name'] = trim(str_replace('Name: ', '', $line));
                } elseif (strpos($line, 'Email: ') === 0) {
                    $submission['email'] = trim(str_replace('Email: ', '', $line));
                } elseif (strpos($line, 'Phone: ') === 0) {
                    $submission['phone'] = trim(str_replace('Phone: ', '', $line));
                } elseif (strpos($line, 'Message: ') === 0) {
                    $submission['message'] = trim(str_replace('Message: ', '', $line));
                }
            }
            if (!empty($submission)) {
                $submissions[] = $submission;
            }
        }
    }
    
    // Reverse to show newest first
    $submissions = array_reverse($submissions);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Submissions - GAFE</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .submission { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .submission h3 { color: #3498db; margin-top: 0; }
        .submission p { margin: 5px 0; }
        .submission .message { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 10px; }
        .logout { float: right; background: #e74c3c; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; }
        .logout:hover { background: #c0392b; }
        .count { background: #3498db; color: white; padding: 5px 10px; border-radius: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📧 Contact Form Submissions - GAFE</h1>
        <a href="?logout=1" class="logout">Logout</a>
        <p>Total submissions: <span class="count"><?php echo count($submissions); ?></span></p>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="submission">
            <h3>No submissions yet</h3>
            <p>Contact form submissions will appear here.</p>
        </div>
    <?php else: ?>
        <?php foreach ($submissions as $submission): ?>
            <div class="submission">
                <h3>📝 Submission from <?php echo htmlspecialchars($submission['name'] ?? 'Unknown'); ?></h3>
                <p><strong>📅 Date:</strong> <?php echo htmlspecialchars($submission['date'] ?? 'Unknown'); ?></p>
                <p><strong>📧 Email:</strong> <a href="mailto:<?php echo htmlspecialchars($submission['email'] ?? ''); ?>"><?php echo htmlspecialchars($submission['email'] ?? 'Not provided'); ?></a></p>
                <p><strong>📞 Phone:</strong> <a href="tel:<?php echo htmlspecialchars($submission['phone'] ?? ''); ?>"><?php echo htmlspecialchars($submission['phone'] ?? 'Not provided'); ?></a></p>
                <div class="message">
                    <strong>💬 Message:</strong><br>
                    <?php echo nl2br(htmlspecialchars($submission['message'] ?? 'No message')); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
