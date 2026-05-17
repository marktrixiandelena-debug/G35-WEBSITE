<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Flood and Drainage Incident Reporting and Management System</title>
    <link rel="stylesheet" href="assets/css/global/loginreg.css">
    <style>
        .landing-container {
            max-width: 600px;
            /* Slightly wider for reading comfort */
        }

        .info-section {
            text-align: left;
            margin: 1.5rem 0;
            color: var(--text-color);
        }

        .info-section h3 {
            color: var(--primary-color);
            margin-bottom: 0.4rem;
            font-size: 1rem;
        }

        .info-section ul {
            list-style-type: none;
            padding-left: 0;
        }

        .info-section li {
            margin-bottom: 0.25rem;
            padding-left: 1.5rem;
            position: relative;
        }

        .info-section li::before {
            content: "✔";
            color: var(--primary-color);
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .contact-box {
            background-color: #f3f4f6;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="bg-landing">
    <!-- Landing Page Container -->
    <div class="auth-container landing-container">
        <!-- Header Branding -->
        <div class="auth-header">
            <img src="assets/images/barangay_logo.jpg" alt="Seal of Barangay Calauag"
                style="width: 110px; height: 110px; margin: 0 auto 0.85rem auto; display: block; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.15); border: 3px solid white;">
            <h2 style="font-size: 1.875rem; font-weight: 800; letter-spacing: -0.5px; color: #065f46; text-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 0.15rem;">
                Barangay Calauag
            </h2>
            <p style="font-size: 1rem; font-weight: 500; color: var(--text-color); margin-top: 0.4rem;">
                Flood and Drainage Incident Reporting and Management System
            </p>
        </div>

        <!-- System Description -->
        <div class="info-section">
            <p style="text-align: center; margin-bottom: 1.25rem; font-size: 0.95rem; line-height: 1.5;">
                This system enables residents and barangay officials to report and monitor flooding, drainage issues,
                and water-related concerns in our community.
            </p>

            <h3>Who can use this system?</h3>
            <ul>
                <li>Registered residents</li>
                <li>Authorized barangay officials</li>
            </ul>
        </div>

        <!-- Registration Info -->
        <div class="info-section">
            <h3>How do I get an account?</h3>
            <ul style="margin-top: 0.5rem; margin-bottom: 1.5rem;">
                <li>Residents may <strong>register online</strong> using the link below.</li>
                <li>New accounts require verification by the Barangay Office.</li>
                <li>Barangay Office assistance is available.</li>
            </ul>
        </div>

        <!-- Action Buttons -->
        <div style="margin-top: 1.5rem; margin-bottom: 1rem;">
            <a href="auth/login.php" class="btn-primary"
                style="text-decoration: none; display: block; text-align: center;">Login</a>
                
            <p style="text-align: center; margin-top: 1rem; font-size: 0.95rem; color: var(--text-color);">
                No account yet? <a href="auth/register.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Register here</a>
            </p>
        </div>

        <!-- Support Info -->
        <div class="contact-box">
            <strong>Need assistance?</strong>
            <p style="margin-top: 0.5rem; margin-bottom: 0;">
                Please contact the Barangay Office at:
                <b style="display: block; margin-top: 0.25rem;">0928-689-5198</b>
            </p>
        </div>

        <div class="auth-footer">
            <p>&copy; <?php echo date('Y'); ?> Barangay Calauag</p>
        </div>
    </div>
</body>

</html>