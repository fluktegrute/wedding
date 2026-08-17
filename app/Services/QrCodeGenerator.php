<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Renders QR codes as inline SVG markup. Nothing is written to disk or
 * cached: the SVG is built fresh from the given value on every call, so a
 * QR code encoding a URL always reflects that URL as of the current
 * request rather than a domain baked in ahead of time.
 */
class QrCodeGenerator
{
    public function svg(string $value, int $size = 320): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, margin: 1),
            new SvgImageBackEnd,
        );

        $svg = (new Writer($renderer))->writeString($value);

        // Strip the XML prolog: it's invalid to embed inline in an HTML document.
        return trim(preg_replace('/^<\?xml.*?\?>\s*/', '', $svg));
    }
}
