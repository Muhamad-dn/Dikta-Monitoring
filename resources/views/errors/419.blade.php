<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0;url=/login">
    <title>Memperbarui Sesi...</title>
    <script>
        window.location.replace('/login');
    </script>
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #0f172a;
            color: #f8fafc;
        }
        .box {
            text-align: center;
            padding: 2.5rem;
            background: #1e293b;
            border-radius: 1rem;
            border: 1px solid #334155;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
            max-width: 420px;
            width: 90%;
        }
        .title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #f1f5f9;
        }
        .desc {
            font-size: 0.875rem;
            color: #94a3b8;
            line-height: 1.5;
        }
        a {
            color: #38bdf8;
            text-decoration: underline;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <div class="title">Sesi Sedang Diperbarui...</div>
        <div class="desc">Mengalihkan ke halaman login secara otomatis.<br>Jika halaman tidak berpindah, <a href="/login">klik di sini</a>.</div>
    </div>
</body>
</html>
