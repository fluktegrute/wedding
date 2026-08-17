<?php

namespace Tests\Unit\Services;

use App\Services\QrCodeGenerator;
use Tests\TestCase;

class QrCodeGeneratorTest extends TestCase
{
    public function test_svg_renders_inline_markup_without_an_xml_prolog(): void
    {
        $svg = (new QrCodeGenerator)->svg('https://example.com');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringNotContainsString('<?xml', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
    }

    public function test_svg_respects_the_given_size(): void
    {
        $svg = (new QrCodeGenerator)->svg('https://example.com', size: 200);

        $this->assertStringContainsString('width="200"', $svg);
        $this->assertStringContainsString('height="200"', $svg);
    }
}
