<?php
/**
 * auth/register.php
 * Lets a new person sign up as either a Client or a Freelancer.
 * (Admin accounts are created directly in the database - see the
 *  demo data in database/revive_technologies.sql)
 */
require_once '../includes/functions.php';
require_once '../config/database.php';

if (is_logged_in()) {
    redirect('../' . $_SESSION['role'] . '/dashboard.php');
}

$errors = [];
$old = ['role' => 'client', 'first_name' => '', 'last_name' => '', 'email' => '', 'phone' => ''];

// Allow linking straight into the freelancer application via ?role=freelancer
if (($_GET['role'] ?? '') === 'freelancer') {
    $old['role'] = 'freelancer';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['role']       = $_POST['role'] === 'freelancer' ? 'freelancer' : 'client';
    $old['first_name'] = trim($_POST['first_name'] ?? '');
    $old['last_name']  = trim($_POST['last_name'] ?? '');
    $old['email']      = trim($_POST['email'] ?? '');
    $old['phone']      = trim($_POST['phone'] ?? '');
    $password          = $_POST['password'] ?? '';
    $skill             = trim($_POST['skill'] ?? '');

    // ---- Validation ----
    if ($old['first_name'] === '' || $old['last_name'] === '') {
        $errors[] = 'Please enter your first and last name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if ($old['role'] === 'freelancer' && $skill === '') {
        $errors[] = 'Please tell us your main skill (e.g. Floral Design, Lighting).';
    }

    // ---- Portfolio upload (freelancers only, optional) ----
    $portfolioPaths = [];
    if ($old['role'] === 'freelancer' && !empty($_FILES['portfolio']['name'][0])) {
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $maxBytes     = 5 * 1024 * 1024; // 5MB per file
        $uploadDir    = __DIR__ . '/../uploads/portfolio/';
        $fileCount    = count($_FILES['portfolio']['name']);

        if ($fileCount > 6) {
            $errors[] = 'Please upload a maximum of 6 portfolio images.';
        } else {
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['portfolio']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if ($_FILES['portfolio']['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = 'One of your portfolio files failed to upload. Please try again.';
                    break;
                }
                if ($_FILES['portfolio']['size'][$i] > $maxBytes) {
                    $errors[] = 'Each portfolio image must be smaller than 5MB.';
                    break;
                }
                $tmpPath  = $_FILES['portfolio']['tmp_name'][$i];
                $mimeType = mime_content_type($tmpPath);
                if (!isset($allowedTypes[$mimeType])) {
                    $errors[] = 'Portfolio files must be JPG, PNG or WEBP images.';
                    break;
                }
                $portfolioPaths[] = ['tmp' => $tmpPath, 'ext' => $allowedTypes[$mimeType]];
            }
        }
    }

    if (empty($errors)) {
        // Check the email is not already registered.
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'That email address is already registered. Please sign in instead.';
        }
    }

    if (empty($errors)) {
        $full_name    = $old['first_name'] . ' ' . $old['last_name'];
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([$full_name, $old['email'], $old['phone'], $passwordHash, $old['role']]);
            $newUserId = $pdo->lastInsertId();

            if ($old['role'] === 'freelancer') {
                // Move any uploaded portfolio images into place now that we
                // have a user_id to name them with.
                $savedPortfolioFiles = [];
                foreach ($portfolioPaths as $index => $file) {
                    $filename    = 'freelancer_' . $newUserId . '_' . ($index + 1) . '_' . bin2hex(random_bytes(4)) . '.' . $file['ext'];
                    $destination = __DIR__ . '/../uploads/portfolio/' . $filename;
                    if (move_uploaded_file($file['tmp'], $destination)) {
                        $savedPortfolioFiles[] = 'uploads/portfolio/' . $filename;
                    }
                }
                $portfolioFilesValue = !empty($savedPortfolioFiles) ? implode(',', $savedPortfolioFiles) : null;

                $stmt = $pdo->prepare(
                    'INSERT INTO freelancers (user_id, skill, bio, hourly_rate, rating, portfolio_files) VALUES (?, ?, ?, ?, 0, ?)'
                );
                $stmt->execute([$newUserId, $skill, '', 0, $portfolioFilesValue]);
            }

            $pdo->commit();

            // Log the new user straight in.
            $_SESSION['user_id']   = $newUserId;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['role']      = $old['role'];
            $_SESSION['email']     = $old['email'];

            set_flash('Account created successfully. Welcome, ' . $full_name . '!');
            redirect('../' . $old['role'] . '/dashboard.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong creating your account. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Bee's Events & Decor</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-logo">
            <img src="../assets/img/logo.png" alt="Bee's Events & Decor" class="logo-img">
        </div>

        <div class="tab-switch">
            <a href="login.php">Sign In</a>
            <a href="register.php" class="active">Register</a>
        </div>

        <?php foreach ($errors as $error): ?>
            <div class="alert alert-error"><?php echo h($error); ?></div>
        <?php endforeach; ?>

        <form method="POST" action="register.php" id="registerForm" enctype="multipart/form-data">
            <label>I am registering as a</label>
            <div class="role-select">
                <label class="role-option <?php echo $old['role'] === 'client' ? 'selected' : ''; ?>" id="roleClientBox">
                    <input type="radio" name="role" value="client" style="display:none;" <?php echo $old['role'] === 'client' ? 'checked' : ''; ?> onclick="selectRole('client')">
                    <div class="emoji">🤍</div>
                    <strong>Client</strong><br><span style="font-size:12px;color:var(--text-muted);">Book décor services</span>
                </label>
                <label class="role-option <?php echo $old['role'] === 'freelancer' ? 'selected' : ''; ?>" id="roleFreelancerBox">
                    <input type="radio" name="role" value="freelancer" style="display:none;" <?php echo $old['role'] === 'freelancer' ? 'checked' : ''; ?> onclick="selectRole('freelancer')">
                    <div class="emoji">🛠️</div>
                    <strong>Freelancer</strong><br><span style="font-size:12px;color:var(--text-muted);">Work events with us</span>
                </label>
            </div>

            <div class="form-grid">
                <div class="field"><label>First name</label><input type="text" name="first_name" placeholder="First name" value="<?php echo h($old['first_name']); ?>" required></div>
                <div class="field"><label>Last name</label><input type="text" name="last_name" placeholder="Last name" value="<?php echo h($old['last_name']); ?>" required></div>
            </div>
            <div class="field"><label>Email address</label><input type="email" name="email" placeholder="you@example.com" value="<?php echo h($old['email']); ?>" required></div>
            <div class="field"><label>Phone number</label><input type="tel" name="phone" placeholder="+27 XX XXX XXXX" value="<?php echo h($old['phone']); ?>"></div>

            <div class="field" id="skillField" style="<?php echo $old['role'] === 'freelancer' ? '' : 'display:none;'; ?>">
                <label>Main skill</label>
                <input type="text" name="skill" placeholder="e.g. Floral Design, Lighting, Balloon Art">
            </div>

            <div class="field" id="portfolioField" style="<?php echo $old['role'] === 'freelancer' ? '' : 'display:none;'; ?>">
                <label>Upload your work (optional)</label>
                <input type="file" name="portfolio[]" id="portfolioInput" accept="image/jpeg,image/png,image/webp" multiple onchange="updatePortfolioLabel()">
                <span id="portfolioLabel" style="display:block;font-size:12px;color:var(--text-muted);margin-top:6px;">Up to 6 photos of past décor work (JPG, PNG or WEBP, max 5MB each). This helps clients and admin see your style.</span>
            </div>

            <div class="field"><label>Create password</label><input type="password" name="password" placeholder="At least 6 characters" required></div>

            <button type="submit" class="btn btn-primary btn-block" id="submitBtn"><?php echo $old['role'] === 'freelancer' ? 'Create Freelancer Account' : 'Create Client Account'; ?></button>
        </form>
    </div>
</div>

<script>
function selectRole(role) {
    document.getElementById('roleClientBox').classList.toggle('selected', role === 'client');
    document.getElementById('roleFreelancerBox').classList.toggle('selected', role === 'freelancer');
    document.getElementById('skillField').style.display = role === 'freelancer' ? 'block' : 'none';
    document.getElementById('portfolioField').style.display = role === 'freelancer' ? 'block' : 'none';
    document.getElementById('submitBtn').textContent = role === 'freelancer' ? 'Create Freelancer Account' : 'Create Client Account';
}
function updatePortfolioLabel() {
    const files = document.getElementById('portfolioInput').files;
    const label = document.getElementById('portfolioLabel');
    if (files.length > 0) {
        label.textContent = files.length + ' file' + (files.length > 1 ? 's' : '') + ' selected.';
    } else {
        label.textContent = 'Up to 6 photos of past décor work (JPG, PNG or WEBP, max 5MB each). This helps clients and admin see your style.';
    }
}
</script>
</body>
</html>
