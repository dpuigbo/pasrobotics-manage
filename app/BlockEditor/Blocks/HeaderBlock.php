<?php

namespace App\BlockEditor\Blocks;

class HeaderBlock extends BaseBlock
{
    public static function type(): string { return 'header'; }
    public static function label(): string { return 'Cabecera'; }
    public static function icon(): string { return 'document-text'; }
    public static function category(): string { return 'layout'; }

    public static function defaultConfig(): array
    {
        return [
            'title' => 'Informe de Mantenimiento',
            'subtitle' => '',
            'showLogo' => true,
            'showDate' => true,
            'showReference' => true,
            'logoPosition' => 'left',
        ];
    }

    public static function configSchema(): array
    {
        return [
            static::configField('text', 'title', 'Título del informe'),
            static::configField('text', 'subtitle', 'Subtítulo'),
            static::configField('toggle', 'showLogo', 'Mostrar logo'),
            static::configField('toggle', 'showDate', 'Mostrar fecha'),
            static::configField('toggle', 'showReference', 'Mostrar referencia'),
            static::configField('select', 'logoPosition', 'Posición del logo', [
                'options' => ['left' => 'Izquierda', 'right' => 'Derecha', 'center' => 'Centro'],
            ]),
        ];
    }

    public static function renderPreview(array $config): string
    {
        $title = static::e($config['title'] ?? 'Informe de Mantenimiento');
        $subtitle = static::e($config['subtitle'] ?? '');
        $showLogo = $config['showLogo'] ?? true;
        $showDate = $config['showDate'] ?? true;

        $logoHtml = $showLogo
            ? '<div style="width:60px;height:60px;background:#f1f5f9;border:1px dashed #cbd5e1;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:10px;color:#94a3b8;">LOGO</div>'
            : '';

        $dateHtml = $showDate
            ? '<div style="font-size:11px;color:#64748b;">Fecha: ____/____/________</div>'
            : '';

        $subtitleHtml = $subtitle
            ? '<div style="font-size:12px;color:#64748b;margin-top:2px;">' . $subtitle . '</div>'
            : '';

        return <<<HTML
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:3px solid #f59e0b;background:linear-gradient(135deg,#fffbeb,#ffffff);">
            <div style="display:flex;align-items:center;gap:16px;">
                {$logoHtml}
                <div>
                    <div style="font-size:18px;font-weight:700;color:#1e293b;letter-spacing:-0.025em;">{$title}</div>
                    {$subtitleHtml}
                </div>
            </div>
            <div style="text-align:right;">
                {$dateHtml}
                <div style="font-size:10px;color:#94a3b8;margin-top:2px;">Ref: ____________</div>
            </div>
        </div>
        HTML;
    }

    public static function renderPdf(array $config, array $data = []): string
    {
        $title = static::e($config['title'] ?? 'Informe de Mantenimiento');
        $subtitle = static::e($config['subtitle'] ?? '');
        $showDate = $config['showDate'] ?? true;
        $date = $data['date'] ?? date('d/m/Y');
        $reference = $data['reference'] ?? '';

        $subtitleHtml = $subtitle ? "<div style=\"font-size:11px;color:#555;margin-top:2px;\">{$subtitle}</div>" : '';
        $dateHtml = $showDate ? "<div style=\"font-size:10px;color:#333;\">Fecha: {$date}</div>" : '';
        $refHtml = $reference ? "<div style=\"font-size:10px;color:#666;\">Ref: {$reference}</div>" : '';

        return <<<HTML
        <table style="width:100%;border-bottom:2px solid #f59e0b;margin-bottom:12px;" cellpadding="0" cellspacing="0">
            <tr>
                <td style="padding:10px 0;">
                    <div style="font-size:16px;font-weight:bold;color:#111;">{$title}</div>
                    {$subtitleHtml}
                </td>
                <td style="text-align:right;padding:10px 0;vertical-align:top;">
                    {$dateHtml}
                    {$refHtml}
                </td>
            </tr>
        </table>
        HTML;
    }
}
