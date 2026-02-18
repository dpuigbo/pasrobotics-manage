<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Vista previa del template</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: {{ $pageConfig['fontSize'] ?? 10 }}px;
            line-height: 1.4;
            color: #222;
        }
        .page {
            padding: {{ $pageConfig['margins']['top'] ?? 20 }}mm {{ $pageConfig['margins']['right'] ?? 15 }}mm {{ $pageConfig['margins']['bottom'] ?? 20 }}mm {{ $pageConfig['margins']['left'] ?? 15 }}mm;
        }
        @page {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="page">
        {!! $blocksHtml !!}
    </div>
</body>
</html>
