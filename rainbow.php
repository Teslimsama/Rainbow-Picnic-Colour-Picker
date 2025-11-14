<?php
/* rainbow-picnic.php – Random rainbow color selector */

/* 1. Define a rainbow-friendly palette (you can add/remove colors) */
$rainbow = [
    '#FF6F61', // Coral
    '#FFB366', // Peach
    '#FFFF99', // Lemon
    '#B2E69A', // Mint
    '#88D8E8', // Sky
    '#B19CD9', // Lavender
    '#FF99CC', // Bubblegum
];

/* 2. Pick a random color (or keep the previous one if the form was submitted) */
$selected = $rainbow[array_rand($rainbow)];

/* 3. If the user pressed the button, pick a fresh one */
if (isset($_POST['new'])) {
    $selected = $rainbow[array_rand($rainbow)];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Rainbow Picnic Color Picker</title>
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

        button {
            font-size: 1.2rem;
            padding: .8rem 1.5rem;
            border: none;
            border-radius: .5rem;
            background: #333;
            color: #fff;
            cursor: pointer;
        }

        button:hover {
            background: #555;
        }

        footer {
            margin-top: 3rem;
            color: #777;
            font-size: .9rem;
        }
    </style>
</head>

<body>

    <h1>🌈 Rainbow Picnic Color Picker</h1>

    <div class="swatch" style="background-color:<?php echo htmlspecialchars($selected); ?>;"></div>

    <p class="code"><?php echo htmlspecialchars($selected); ?></p>

    <form method="post">
        <button type="submit" name="new" value="1">Pick another!</button>
    </form>

    <footer>
        <p>Perfect for planning your next rainbow-themed picnic! 🎉</p>
    </footer>

</body>

</html>