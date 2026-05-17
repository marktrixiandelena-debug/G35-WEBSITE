<?php
/**
 * Resident Registration Page
 * Allows new residents to create an account for incident reporting.
 * Submits to process_register.php for validation and storage.
 */
session_start();

// Redirect if already authenticated
if (isset($_SESSION['user_id'])) {
    header("Location: ../{$_SESSION['role']}/dashboard/{$_SESSION['role']}_dashboard.php");
    exit();
}

// Retrieve persistence data from session
$errors = $_SESSION['reg_errors'] ?? [];
$old    = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_errors'], $_SESSION['reg_old']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="../assets/css/global/loginreg.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Registration-specific overrides */
        .auth-container {
            max-width: 530px;
            padding: 1.75rem;
        }

        .auth-header {
            margin-bottom: 1.5rem;
        }

        .auth-header h2 {
            margin-bottom: 0.2rem;
        }

        .subtitle {
            color: var(--text-color);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .microcopy {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            margin-bottom: 0.375rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.125rem;
        }

        @media (min-width: 500px) {
            .form-row {
                grid-template-columns: 1fr 1fr;
                gap: 1.25rem;
            }
        }

        .username-field {
            position: relative;
        }

        .username-status {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
        }

        .username-status.available { color: #16a34a; }
        .username-status.taken { color: #ef4444; }

        .alert-danger ul {
            margin: 0;
            padding-left: 1.25rem;
            list-style: disc;
        }

        .register-link {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .register-link a {
            color: var(--link-color);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .form-hint {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
    </style>
</head>

<body class="bg-login">
    <div class="auth-container">
        <div class="auth-header" style="margin-bottom: 1rem;">
            <img src="../assets/images/barangay_logo.jpg" alt="Barangay Calauag Seal"
                style="width: 80px; height: 80px; margin: 0 auto 0.65rem auto; display: block; border-radius: 50%; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2>Create Account</h2>
            <p class="subtitle">Register as a Barangay Calauag resident</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="process_register.php" id="registerForm">

            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name">Full Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="full_name" name="full_name" class="form-control" required
                    placeholder="e.g. Juan Dela Cruz"
                    value="<?php echo htmlspecialchars($old['full_name'] ?? ''); ?>"
                    autocomplete="off">
            </div>

            <!-- Username (auto-filled) -->
            <div class="form-group">
                <label for="username">Username <span style="color: #ef4444;">*</span></label>
                <div class="username-field">
                    <input type="text" id="username" name="username" class="form-control" required
                        placeholder="Auto-generated from name" maxlength="50"
                        value="<?php echo htmlspecialchars($old['username'] ?? ''); ?>"
                        autocomplete="off">
                    <span id="usernameStatus" class="username-status"></span>
                </div>
                <p class="form-hint"><i class="fas fa-info-circle"></i> Auto-filled from your name. You may edit it.</p>
            </div>

            <!-- Contact Number & Address -->
            <div class="form-row">
                <div class="form-group">
                    <label for="contact_number">Contact Number <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="contact_number" name="contact_number" class="form-control" required
                        placeholder="09xxxxxxxxx" maxlength="11"
                        value="<?php echo htmlspecialchars($old['contact_number'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Address / Street <span style="color: #ef4444;">*</span></label>
                    <select id="address" name="address" class="form-control" required>
                        <option value="">Select Street...</option>
                        <option value="Acacia Street" <?php echo ($old['address'] ?? '') === 'Acacia Street' ? 'selected' : ''; ?>>Acacia Street</option>
                        <option value="Agate Street" <?php echo ($old['address'] ?? '') === 'Agate Street' ? 'selected' : ''; ?>>Agate Street</option>
                        <option value="Anahaw Street" <?php echo ($old['address'] ?? '') === 'Anahaw Street' ? 'selected' : ''; ?>>Anahaw Street</option>
                        <option value="Antipolo Street" <?php echo ($old['address'] ?? '') === 'Antipolo Street' ? 'selected' : ''; ?>>Antipolo Street</option>
                        <option value="Apitong St" <?php echo ($old['address'] ?? '') === 'Apitong St' ? 'selected' : ''; ?>>Apitong St</option>
                        <option value="Banaba St" <?php echo ($old['address'] ?? '') === 'Banaba St' ? 'selected' : ''; ?>>Banaba St</option>
                        <option value="Blacer Street" <?php echo ($old['address'] ?? '') === 'Blacer Street' ? 'selected' : ''; ?>>Blacer Street</option>
                        <option value="Buhi St" <?php echo ($old['address'] ?? '') === 'Buhi St' ? 'selected' : ''; ?>>Buhi St</option>
                        <option value="Cacao Street" <?php echo ($old['address'] ?? '') === 'Cacao Street' ? 'selected' : ''; ?>>Cacao Street</option>
                        <option value="Chico St" <?php echo ($old['address'] ?? '') === 'Chico St' ? 'selected' : ''; ?>>Chico St</option>
                        <option value="Cypress Street" <?php echo ($old['address'] ?? '') === 'Cypress Street' ? 'selected' : ''; ?>>Cypress Street</option>
                        <option value="Dapdap St" <?php echo ($old['address'] ?? '') === 'Dapdap St' ? 'selected' : ''; ?>>Dapdap St</option>
                        <option value="Dapo St" <?php echo ($old['address'] ?? '') === 'Dapo St' ? 'selected' : ''; ?>>Dapo St</option>
                        <option value="Diamond Street" <?php echo ($old['address'] ?? '') === 'Diamond Street' ? 'selected' : ''; ?>>Diamond Street</option>
                        <option value="Dita St" <?php echo ($old['address'] ?? '') === 'Dita St' ? 'selected' : ''; ?>>Dita St</option>
                        <option value="Emerald Street" <?php echo ($old['address'] ?? '') === 'Emerald Street' ? 'selected' : ''; ?>>Emerald Street</option>
                        <option value="Garnet Street" <?php echo ($old['address'] ?? '') === 'Garnet Street' ? 'selected' : ''; ?>>Garnet Street</option>
                        <option value="Hamoraon St" <?php echo ($old['address'] ?? '') === 'Hamoraon St' ? 'selected' : ''; ?>>Hamoraon St</option>
                        <option value="J. Antonio M. Carpio Street" <?php echo ($old['address'] ?? '') === 'J. Antonio M. Carpio Street' ? 'selected' : ''; ?>>J. Antonio M. Carpio Street</option>
                        <option value="Jade Street" <?php echo ($old['address'] ?? '') === 'Jade Street' ? 'selected' : ''; ?>>Jade Street</option>
                        <option value="Kamagong St" <?php echo ($old['address'] ?? '') === 'Kamagong St' ? 'selected' : ''; ?>>Kamagong St</option>
                        <option value="Lawaan St" <?php echo ($old['address'] ?? '') === 'Lawaan St' ? 'selected' : ''; ?>>Lawaan St</option>
                        <option value="Lukban St" <?php echo ($old['address'] ?? '') === 'Lukban St' ? 'selected' : ''; ?>>Lukban St</option>
                        <option value="Mahogany Street" <?php echo ($old['address'] ?? '') === 'Mahogany Street' ? 'selected' : ''; ?>>Mahogany Street</option>
                        <option value="Narra St" <?php echo ($old['address'] ?? '') === 'Narra St' ? 'selected' : ''; ?>>Narra St</option>
                        <option value="Onyx Street" <?php echo ($old['address'] ?? '') === 'Onyx Street' ? 'selected' : ''; ?>>Onyx Street</option>
                        <option value="Opal Street" <?php echo ($old['address'] ?? '') === 'Opal Street' ? 'selected' : ''; ?>>Opal Street</option>
                        <option value="Palomaria St" <?php echo ($old['address'] ?? '') === 'Palomaria St' ? 'selected' : ''; ?>>Palomaria St</option>
                        <option value="Papua Street" <?php echo ($old['address'] ?? '') === 'Papua Street' ? 'selected' : ''; ?>>Papua Street</option>
                        <option value="Pili Street" <?php echo ($old['address'] ?? '') === 'Pili Street' ? 'selected' : ''; ?>>Pili Street</option>
                        <option value="Rimas St" <?php echo ($old['address'] ?? '') === 'Rimas St' ? 'selected' : ''; ?>>Rimas St</option>
                        <option value="Ruby Street" <?php echo ($old['address'] ?? '') === 'Ruby Street' ? 'selected' : ''; ?>>Ruby Street</option>
                        <option value="Sapphire Street" <?php echo ($old['address'] ?? '') === 'Sapphire Street' ? 'selected' : ''; ?>>Sapphire Street</option>
                        <option value="Talisay St" <?php echo ($old['address'] ?? '') === 'Talisay St' ? 'selected' : ''; ?>>Talisay St</option>
                        <option value="Topaz Street" <?php echo ($old['address'] ?? '') === 'Topaz Street' ? 'selected' : ''; ?>>Topaz Street</option>
                        <option value="Villafrancia Street" <?php echo ($old['address'] ?? '') === 'Villafrancia Street' ? 'selected' : ''; ?>>Villafrancia Street</option>
                        <option value="Yakal St" <?php echo ($old['address'] ?? '') === 'Yakal St' ? 'selected' : ''; ?>>Yakal St</option>
                        <option value="Other" <?php echo ($old['address'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <p class="form-hint" style="margin-top: -0.5rem; margin-bottom: 1.125rem;"><i class="fas fa-lock" style="color: #10b981;"></i> Your information is secure and for official use only.</p>

            <!-- Password -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span style="color: #ef4444;">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" required
                            placeholder="Min 6 characters" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span style="color: #ef4444;">*</span></label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                            placeholder="Re-enter password" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary" style="margin-top: 0.5rem;">Create Account</button>
        </form>

        <div class="register-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>

        <div class="auth-footer">
            <p>&copy; <?php echo date('Y'); ?> Barangay Calauag</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/auth/password_toggle.js"></script>
    <script>
        const fullNameInput = document.getElementById('full_name');
        const usernameInput = document.getElementById('username');
        const statusSpan = document.getElementById('usernameStatus');
        let debounceTimer;

        fullNameInput.addEventListener('input', function () {
            const name = this.value.trim();
            // "Joseph John Capuz" -> "joseph.jc"
            const parts = name.split(/\s+/).filter(Boolean);
            if (parts.length === 0) {
                usernameInput.value = '';
                statusSpan.textContent = '';
                return;
            }
            if (parts.length === 1) {
                usernameInput.value = parts[0].toLowerCase();
            } else {
                const first = parts[0].toLowerCase();
                const initials = parts.slice(1).map(p => p[0].toLowerCase()).join('');
                usernameInput.value = first + '.' + initials;
            }
            checkUsername(usernameInput.value);
        });

        usernameInput.addEventListener('input', function () {
            checkUsername(this.value.trim());
        });

        function checkUsername(uname) {
            clearTimeout(debounceTimer);
            if (!uname) {
                statusSpan.textContent = '';
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch('check_username.php?username=' + encodeURIComponent(uname))
                    .then(r => r.json())
                    .then(data => {
                        statusSpan.textContent = data.message;
                        statusSpan.className = 'username-status ' + (data.available ? 'available' : 'taken');
                    });
            }, 400);
        }
    </script>
    <?php require_once "../includes/flash_toast.php"; ?>
</body>

</html>
