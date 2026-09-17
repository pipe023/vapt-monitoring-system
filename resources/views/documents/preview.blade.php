<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fileName ?: 'Document preview' }}</title>
    <style>body{margin:0;padding:24px;background:#f8fafc;color:#334155;font-family:ui-sans-serif,system-ui,sans-serif}h2{font-size:16px;margin:0 0 18px;color:#0f172a}pre{white-space:pre-wrap;word-break:break-word;margin:0;padding:20px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;line-height:1.6;font:14px/1.6 ui-monospace,SFMono-Regular,Consolas,monospace}</style>
</head>
<body>
    @if($fileName)<h2>{{ $fileName }}</h2>@endif
    <pre>{{ implode("\n\n", $content) }}</pre>
</body>
</html>