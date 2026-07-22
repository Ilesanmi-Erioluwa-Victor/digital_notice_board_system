<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Notice Board — Kiosk Mode</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        body{margin:0;overflow:hidden;background:#0F172A}.kiosk-wrapper{transition:background 500ms}.kiosk-notice .priority-high{color:#fca5a5}.kiosk-notice .priority-medium{color:#fcd34d}.kiosk-notice .priority-low{color:#94a3b8}
    </style>
</head>
<body>
    <div class="kiosk-wrapper">
        <div id="kiosk-clock" class="kiosk-clock"></div>
        <div id="kiosk-title" class="kiosk-title" style="transition: opacity 300ms;">Digital Notice Board</div>
        <div id="kiosk-content" class="kiosk-notice" style="transition: opacity 300ms;">
            <p>Loading notices...</p>
        </div>
        <div style="position:absolute;bottom:1.5rem;font-size:0.85rem;opacity:0.5;">
            Press &#8592; &#8593; &#8594; &#8595; to navigate manually
        </div>
    </div>
    <script src="/assets/js/kiosk.js"></script>
</body>
</html>
