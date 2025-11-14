<?php
/* rainbow-picnic.php – Secure, locked colour picker with PHPMailer */

session_start();

// --- 1. Autoload PHPMailer -------------------------------------------------
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- 2. Rainbow palette ----------------------------------------------------
$rainbow = [
    '#FF6F61', // Coral
    '#FFB366', // Peach
    '#FFFF99', // Lemon
    '#B2E69A', // Mint
    '#88D8E8', // Sky
    '#B19CD9', // Lavender
    '#FF99CC', // Bubblegum
];

// --- 3. Helper: pick random colour -----------------------------------------
function pickColour(array $palette): string
{
    return $palette[array_rand($palette)];
}

// --- 4. Get or set locked colour -------------------------------------------
$selected = $_SESSION['rainbow_colour'] ?? null;
$emailSent = $_SESSION['email_sent'] ?? false;

// --- 5. Process form submission (only if no colour locked) ---------------
if (isset($_POST['pick']) && $selected === null) {
    $rawEmail = $_POST['email'] ?? '';

    // ---- Validate & sanitize email ---------------------------------------
    $email = filter_var(trim($rawEmail), FILTER_VALIDATE_EMAIL);
    if (!$email) {
        $error = "Please enter a valid email address.";
    } else {
        // ---- Pick and lock colour ----------------------------------------
        $selected = pickColour($rainbow);
        $_SESSION['rainbow_colour'] = $selected;

        // ---- Send email securely via PHPMailer (only once) ---------------
        if (!$emailSent) {
            $mail = new PHPMailer(true);
            try {
                // --- Server settings (edit for your SMTP) -------------------
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';     // Change to your SMTP
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your-email@gmail.com';   // SMTP username
                $mail->Password   = 'your-app-password';      // App password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // --- Recipients ------------------------------------------------
                $mail->setFrom('no-reply@picnic.example', 'Rainbow Picnic Bot');
                $mail->addAddress($email);

                // --- Content ---------------------------------------------------
                $mail->isHTML(false);
                $mail->Subject = "Your Rainbow Picnic Colour is Locked!";
                $mail->Body    = "Hello!\n\n"
                    . "Your official rainbow picnic colour is:\n\n"
                    . "   $selected\n\n"
                    . "This colour is now LOCKED for the event.\n"
                    . "Bring something in this shade! 🌈\n\n"
                    . "See you at the picnic!\n"
                    . "-- The Rainbow Picnic Team";

                $mail->send();
                $_SESSION['email_sent'] = true;
                $emailSent = true;
            } catch (Exception $e) {
                error_log("Mailer Error: {$mail->ErrorInfo}");
                $error = "Sorry, we couldn't send the email. Try again later.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rainbow Picnic – Locked Colour</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: system-ui, sans-serif;
            text-align: center;
            margin: 2rem;
            background: #fafafa;
        }

        .swatch {
            width: 300px;
            height: 300px;
            margin: 2rem auto;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, .15);
        }

        .code {
            font-size: 2rem;
            font-weight: bold;
            margin: 1rem 0;
        }

        input[type=email],
        button {
            font-size: 1.2rem;
            padding: .6rem 1rem;
            margin: 0 .3rem;
            border-radius: .5rem;
        }

        input[type=email] {
            border: 1px solid #ccc;
            width: 260px;
        }

        button {
            border: none;
            background: #333;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background: #555;
        }

        .locked {
            color: #28a745;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .error {
            color: #d33;
            margin: 1rem 0;
        }

        footer {
            margin-top: 3rem;
            color: #777;
            font-size: .9rem;
        }
    </style>
</head>

<body>

    <h1>🌈 Rainbow Picnic Colour Picker</h1>

    <?php if ($selected): ?>
        <!-- Colour is LOCKED -->
        <div class="swatch" style="background-color:<?php echo htmlspecialchars($selected); ?>;"></div>
        <p class="code"><?php echo htmlspecialchars($selected); ?></p>
        <p class="locked">Your colour is LOCKED!</p>
        <?php if ($emailSent): ?>
            <p>Confirmation email sent!</p>
        <?php endif; ?>

    <?php else: ?>
        <!-- First visit – show form -->
        <p>Pick a colour <strong>once</strong> – it will be locked and emailed to you.</p>

        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="email" name="email" placeholder="your@email.com" required
                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <button type="submit" name="pick" value="1">Pick My Colour!</button>
        </form>
    <?php endif; ?>

    <footer>
        <p>Perfect for planning your next rainbow-themed picnic! 🎉</p>
    </footer>

</body>

</html>