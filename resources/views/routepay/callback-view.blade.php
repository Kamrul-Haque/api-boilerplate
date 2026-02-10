<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RoutePay Callback Data</title>
    <style>
        body {
            font-family: monospace;
            background: #f9f9f9;
            padding: 20px;
        }

        pre {
            background: #fff;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
        }

        h1 {
            color: #333;
        }
    </style>
</head>

<body>
    <h1>RoutePay Callback Data</h1>
    <pre>{{ json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</body>

</html>
