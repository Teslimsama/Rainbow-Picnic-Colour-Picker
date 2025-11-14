<?php
/* rainbow-picnic.php – Locked rainbow colour picker with e-mail */

/* ---- 0. Start session --------------------------------------------------- */
session_start();

/* ---- 1. Rainbow palette ------------------------------------------------ */
$rainbow = [
    '#FF6F61', // Coral
    '#FFB366', // Peach
    '#FFFF99', // Lemon
    '#B2E69A', // Mint
    '#88D8E8', // Sky
    '#B19CD9', // Lavender
    '#FF99CC', // Bubblegum
];

/* ---- 2. Helper: pick a colour (only once) ----------------------------- */
function pickColour(array $palette): string
{
    return $palette[array_rand($palette)];
}

/* ---- 3. Decide what colour to show ------------------------------------ */
$selected = $_SESSION['rainbow_colour'] ?? null;

/* If the user pressed the button AND no colour is stored yet → pick + lock */
if (isset($_POST['pick']) && $selected === null) {
    $selected = pickColour($rainbow);
    $_SESSION['rainbow_colour'] = $selected;

    /* ---- 4. Send e-mail (first time only) --------------------------- */
    $email = $_POST['email'] ?? '';
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $subject = "Your Rainbow Picnic Colour";
        $message = "Hello!\n\nYour locked rainbow colour for the picnic is:\n\n"
            . "$selected\n\n"
            . "See you under the rainbow!\n"
            . "-- The Picnic Bot";
        $headers = "From: no-reply@yourdomain.com\r\nReply-To: no-reply@yourdomain.com";

        // For demo purposes we just log it – replace with mail() in prod
        error_log("MAIL TO: $email | SUBJECT: $subject | BODY: $message");
        // mail($email, $subject, $message, $headers);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rainbow Picnic – Locked Colour</title>
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
        }

        button {
            border: none;
            border-radius: .5rem;
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
        }

        footer {
            margin-top: 3rem;
            color: #777;
            font-size: .9rem;
        }
    </style>
</head>

<body>

    <h1>Platoon 6 Rainbow Picnic Colour Picker</h1>

    <?php if ($selected): ?>
        <!-- ---- Colour is already locked --------------------------------- -->
        <div class="swatch" style="background-color:<?php echo htmlspecialchars($selected); ?>;"></div>
        <p class="code"><?php echo htmlspecialchars($selected); ?></p>
        <p class="locked">Your colour is LOCKED for this picnic!</p>

    <?php else: ?>
        <!-- ---- First visit – show form ----------------------------------- -->
        <p>Pick a colour **once** – it will be locked and e-mailed to you.</p>

        <form method="post">
            <input type="email" name="email" placeholder="your@email.com" required
                style="width:260px;">
            <button type="submit" name="pick" value="1">Pick My Colour!</button>
        </form>
    <?php endif; ?>

    <footer>
        <p>Perfect for planning your next rainbow-themed picnic!</p>
    </footer>

</body>

</html>