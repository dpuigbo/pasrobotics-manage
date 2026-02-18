<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe #{{ $report->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #222;
        }
        .page {
            padding: 20mm 15mm;
        }
        .report-meta {
            margin-bottom: 12px;
            padding: 8px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .report-meta table {
            width: 100%;
            font-size: 9px;
        }
        .report-meta td {
            padding: 2px 6px;
            vertical-align: top;
        }
        .report-meta .label {
            font-weight: bold;
            color: #555;
            width: 120px;
        }
        .component-separator {
            margin: 16px 0;
            border-top: 2px solid #f59e0b;
            padding-top: 8px;
        }
        .component-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8px;
            padding: 4px 8px;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
        }
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            font-size: 8px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 4px;
        }
        .footer .page-number:after {
            content: counter(page);
        }
        @page {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Report meta information --}}
        <div class="report-meta">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td class="label">Cliente:</td>
                    <td>{{ $report->intervention?->client?->name ?? '-' }}</td>
                    <td class="label">Sistema:</td>
                    <td>{{ $report->system?->display_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Intervención:</td>
                    <td>{{ $report->intervention?->title ?? $report->intervention?->reference ?? '-' }}</td>
                    <td class="label">Estado:</td>
                    <td>{{ ucfirst($report->status) }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha inicio:</td>
                    <td>{{ $report->performed_start_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="label">Fecha fin:</td>
                    <td>{{ $report->performed_end_at?->format('d/m/Y H:i') ?? '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- Components --}}
        @foreach($componentsHtml as $index => $componentHtml)
            @if($index > 0)
                <div class="component-separator"></div>
            @endif

            @if(isset($report->components[$index]))
                <div class="component-title">
                    {{ $report->components[$index]->label ?? 'Componente ' . ($index + 1) }}
                    <span style="font-size:9px;font-weight:normal;color:#666;">
                        ({{ $report->components[$index]->component_type ?? '' }})
                    </span>
                </div>
            @endif

            {!! $componentHtml !!}
        @endforeach
    </div>

    <div class="footer">
        PAS Robotics &middot; Informe generado el {{ now()->format('d/m/Y H:i') }}
        &middot; Página <span class="page-number"></span>
    </div>
</body>
</html>
