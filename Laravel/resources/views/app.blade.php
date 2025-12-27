<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event App - API Backend</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            color: #fff;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        h1 {
            margin-top: 0;
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        a {
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .links {
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🚀 Event App API</h1>
    <p>Your Laravel API backend is running successfully!</p>
    <p>For the Vue.js frontend, visit:</p>

    <div class="links">
        <a href="http://localhost:5173" target="_blank">
            🔗 Vue Frontend (localhost:5173)
        </a>
        <br>
        <a href="/api/events" target="_blank">
            📡 API Endpoint: /api/events
        </a>
    </div>

    <p style="margin-top: 40px; font-size: 0.9rem; opacity: 0.8;">
        Backend: Laravel {{ app()->version() }} | PHP {{ phpversion() }}
    </p>
</div>
</body>
</html>
