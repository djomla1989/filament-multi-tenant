<?php

namespace App\Services\QrCode;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeGenerator
{
    /**
     * Generate a QR code SVG for the given content.
     *
     * @param string $content The content to encode in the QR code
     * @param int $size The size of the QR code
     * @return string The SVG representation of the QR code
     */
    public function generate(string $content, int $size = 200): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($content);
    }
}
