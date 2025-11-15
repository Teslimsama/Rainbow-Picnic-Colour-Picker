<?php
/* rainbow-picnic.php – Locked colour picker + DB + PHPMailer (8 colours only) */

session_start();

// ---------------------------------------------------------------------
// 0. Config – UPDATE THESE (you already did)
// ---------------------------------------------------------------------
define('DB_HOST', 'sdb-h.hosting.stackcp.net');
define('DB_NAME', 'rainbow-3138366a5d');
define('DB_USER', 'rainman');
define('DB_PASS', 'vt4xe19n3c');

define('SMTP_HOST', 'smtp.unibooks.com.ng');
define('SMTP_USER', 'rainbow@unibooks.com.ng');
define('SMTP_PASS', 'Xm66912ae');
define('SMTP_FROM',  'rainbow@unibooks.com.ng');
define('SMTP_NAME',  'Rainbow Picnic Plantoon 6');

// ---------------------------------------------------------------------
// 1. Composer autoload
// ---------------------------------------------------------------------
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ---------------------------------------------------------------------
// 2. PDO Connection
// ---------------------------------------------------------------------
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed. Check config.");
}

// ---------------------------------------------------------------------
// 3. Reduced Colour Palette (Only 8 colours)
// ---------------------------------------------------------------------
$palette = [
    '#FFFFFF' => 'White',
    '#000000' => 'Black',
    '#FF0000' => 'Red',
    '#FFFF00' => 'Yellow',
    '#800080' => 'Purple',
    '#0000FF' => 'Blue',
    '#FFC0CB' => 'Pink',
    '#8B4513' => 'Brown',
    '#008000' => 'Green',
    '#FF8C00' => 'Orange',
];


// ---------------------------------------------------------------------
// 4. Helper – pick random colour
// ---------------------------------------------------------------------
function pickColour(array $palette): array {
    $hex  = array_rand($palette);
    $name = $palette[$hex];
    return [$hex, $name];
}

// ---------------------------------------------------------------------
// 5. Load from DB if already picked
// ---------------------------------------------------------------------
$selectedHex   = $_SESSION['rainbow_hex']   ?? null;
$selectedName  = $_SESSION['rainbow_name']  ?? null;
$emailSent     = $_SESSION['email_sent']    ?? false;

if (!$selectedHex && isset($_POST['email'])) {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if ($email) {
        $stmt = $pdo->prepare("SELECT hex, name, email_sent FROM picnic_guests WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $selectedHex  = $row['hex'];
            $selectedName = $row['name'];
            $emailSent    = (bool)$row['email_sent'];

            $_SESSION['rainbow_hex']   = $selectedHex;
            $_SESSION['rainbow_name']  = $selectedName;
            $_SESSION['email_sent']    = $emailSent;
        }
    }
}

// ---------------------------------------------------------------------
// 6. Form submission – pick & store
// ---------------------------------------------------------------------
if (isset($_POST['pick']) && !$selectedHex) {
    $rawEmail = $_POST['email'] ?? '';
    $email    = filter_var(trim($rawEmail), FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $error = "Please enter a valid email address.";
    } else {
        [$hex, $name] = pickColour($palette);

        $stmt = $pdo->prepare("
            INSERT INTO picnic_guests (email, hex, name, email_sent)
            VALUES (?, ?, ?, 0)
            ON DUPLICATE KEY UPDATE hex = VALUES(hex), name = VALUES(name), email_sent = 0
        ");
        $stmt->execute([$email, $hex, $name]);

        $selectedHex  = $hex;
        $selectedName = $name;
        $_SESSION['rainbow_hex']   = $hex;
        $_SESSION['rainbow_name']  = $name;
        $_SESSION['email_sent']    = false;

        // Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom(SMTP_FROM, SMTP_NAME);
            $mail->addAddress($email);

            $mail->isHTML(false);
            $mail->Subject = "Your Rainbow Picnic Colour is Locked!";
            $mail->Body    = "Hello!\n\n"
                           . "Your official rainbow picnic colour is:\n\n"
                           . "   $hex  –  $name\n\n"
                           . "This colour is now LOCKED for the event.\n"
                           . "Bring something in this shade!\n\n"
                           . "See you at the picnic!\n"
                           . "-- The Rainbow Picnic Team";

            $mail->send();

            $pdo->prepare("UPDATE picnic_guests SET email_sent = 1 WHERE email = ?")
                ->execute([$email]);

            $_SESSION['email_sent'] = true;
            $emailSent = true;
        } catch (Exception $e) {
            error_log("PHPMailer error: {$mail->ErrorInfo}");
            $error = "Colour saved, but email failed. Try again later.";
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
        body{font-family:system-ui,sans-serif;text-align:center;margin:2rem;background:#fafafa;}
        .swatch{width:300px;height:300px;margin:2rem auto;border-radius:1rem;
                box-shadow:0 8px 20px rgba(0,0,0,.15);}
        .code{font-size:2rem;font-weight:bold;margin:1rem 0;}
        .name{font-size:1.5rem;margin:.5rem 0;color:#444;}
        input[type=email],button{font-size:1.2rem;padding:.6rem 1rem;margin:0 .3rem;
                border-radius:.5rem;}
        input[type=email]{border:1px solid #ccc;width:260px;}
        button{border:none;background:#333;color:#fff;cursor:pointer;}
        button:hover{background:#555;}
        .locked{color:#28a745;font-weight:bold;font-size:1.1rem;}
        .error{color:#d33;margin:1rem 0;}
        footer{margin-top:3rem;color:#777;font-size:.9rem;}
    </style>
</head>
<body>

<h1>Rainbow Picnic Colour Picker</h1>

<?php if ($selectedHex): ?>
    <div class="swatch" style="background-color:<?php echo htmlspecialchars($selectedHex); ?>;"></div>
    <p class="code"><?php echo htmlspecialchars($selectedHex); ?></p>
    <p class="name"><?php echo htmlspecialchars($selectedName); ?></p>
    <p class="locked">Your colour is LOCKED!</p>
    <?php if ($emailSent): ?>
        <p>Confirmation e-mail sent!</p>
    <?php elseif (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

<?php else: ?>
    <p>Pick a colour <strong>once</strong> – it will be locked and e‑mailed to you.</p>

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
    <p>Plantoon 6 – Bring your colour to the picnic!</p>
</footer>

</body>
</html>