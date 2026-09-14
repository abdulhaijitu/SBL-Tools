<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error | SBL Marketing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Hind Siliguri', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        .error-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 1.5rem;
            max-width: 32rem;
            width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .code {
            font-size: 3.5rem;
            font-weight: 900;
            color: #ea580c;
            line-height: 1;
            letter-spacing: -0.05em;
        }
        .title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-top: 1rem;
            color: #ffffff;
        }
        .desc {
            font-size: 0.875rem;
            color: #94a3b8;
            margin-top: 0.5rem;
            line-height: 1.5;
        }
        .debug-box {
            margin-top: 1.5rem;
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            background: #0f172a;
            border: 1px solid #475569;
            color: #fb923c;
            font-family: monospace;
            font-size: 0.75rem;
            text-align: left;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            margin-top: 1.75rem;
            padding: 0.75rem 1.5rem;
            background-color: #ea580c;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.875rem;
            border-radius: 0.75rem;
            text-decoration: none;
            transition: background 0.2s;
        }
        .btn:hover {
            background-color: #c2410c;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="code">500</div>
        <h1 class="title">সার্ভার সাময়িক সমস্যার সম্মুখীন হয়েছে</h1>
        <p class="desc">Server encountered an unexpected error. The system has automatically recorded the details for troubleshooting.</p>

        @if(isset($exception) && $exception->getMessage())
            <div class="debug-box">
                <strong>Error:</strong> {{ $exception->getMessage() }}
            </div>
        @endif

        <div style="margin-top: 1rem;">
            <a href="{{ url('/') }}" class="btn">পুনরায় চেষ্টা করুন (Home)</a>
        </div>
    </div>
</body>
</html>

