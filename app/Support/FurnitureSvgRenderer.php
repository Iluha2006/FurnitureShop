<?php

namespace App\Support;

final class FurnitureSvgRenderer
{
    private int $gradientCounter = 0;

    private string $defs = '';

    public function render(string $name, string $category, string $colorHex, int $viewIndex = 1): string
    {
        [$light, $base, $dark] = $this->palette($colorHex);
        $type = $this->detectType($name, $category);
        $item = $this->drawItem($type, $name, $light, $base, $dark);
        $view = $this->viewTransform($viewIndex);

        $scene = '<rect x="0" y="0" width="800" height="470" fill="'.$this->grad('#f7f8fa', '#eef0f4').'"/>'
            .'<rect x="0" y="470" width="800" height="130" fill="'.$this->grad('#e2e5ea', '#cfd3d8').'"/>'
            .'<line x1="0" y1="470" x2="800" y2="470" stroke="#ffffff" stroke-opacity="0.65" stroke-width="2"/>';

        return '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 800 600">'
            .'<defs>'.$this->defs.'</defs>'
            .$scene
            ."<g transform=\"$view\">$item</g>"
            .'</svg>';
    }

    /**
     * Lighten, base and darken the base colour.
     *
     * @return array{0: string, 1: string, 2: string}
     */
    private function palette(string $hex): array
    {
        $hex = ltrim(strtolower(trim($hex)), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            $hex = 'a08c7a';
        }

        $base = '#'.$hex;

        return [$this->tint($base, 0.42), $base, $this->tint($base, -0.34)];
    }

    private function tint(string $hex, float $amount): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        if ($amount >= 0) {
            $r = (int) round($r + (255 - $r) * $amount);
            $g = (int) round($g + (255 - $g) * $amount);
            $b = (int) round($b + (255 - $b) * $amount);
        } else {
            $f = 1 + $amount;
            $r = (int) round($r * $f);
            $g = (int) round($g * $f);
            $b = (int) round($b * $f);
        }

        return sprintf('#%02x%02x%02x', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }

    private function grad(string $from, string $to, bool $vertical = true): string
    {
        $id = 'gr'.(++$this->gradientCounter);
        $x2 = $vertical ? '0' : '1';
        $y2 = $vertical ? '1' : '0';
        $this->defs .= "<linearGradient id=\"$id\" x1=\"0\" y1=\"0\" x2=\"$x2\" y2=\"$y2\">"
            ."<stop offset=\"0\" stop-color=\"$from\"/>"
            ."<stop offset=\"1\" stop-color=\"$to\"/>"
            .'</linearGradient>';

        return "url(#$id)";
    }

    private function rect(float $x, float $y, float $w, float $h, string $fill, float $rx = 0, string $extra = ''): string
    {
        return "<rect x=\"$x\" y=\"$y\" width=\"$w\" height=\"$h\" rx=\"$rx\" fill=\"$fill\"$extra/>";
    }

    private function circle(float $cx, float $cy, float $r, string $fill, string $extra = ''): string
    {
        return "<circle cx=\"$cx\" cy=\"$cy\" r=\"$r\" fill=\"$fill\"$extra/>";
    }

    private function ellipse(float $cx, float $cy, float $rx, float $ry, string $fill, string $extra = ''): string
    {
        return "<ellipse cx=\"$cx\" cy=\"$cy\" rx=\"$rx\" ry=\"$ry\" fill=\"$fill\"$extra/>";
    }

    private function line(float $x1, float $y1, float $x2, float $y2, string $stroke, string $extra = ''): string
    {
        return "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke=\"$stroke\"$extra/>";
    }

    private function path(string $d, string $fill, string $extra = ''): string
    {
        return "<path d=\"$d\" fill=\"$fill\"$extra/>";
    }

    private function shadow(float $cx, float $rx): string
    {
        return $this->ellipse($cx, 478, $rx, 15, '#000', ' opacity="0.10"');
    }

    private function stitch(float $x1, float $y1, float $x2, float $y2, string $color): string
    {
        return $this->line($x1, $y1, $x2, $y2, $color, ' stroke-opacity="0.28" stroke-dasharray="7 7" stroke-width="1.6"');
    }

    private function viewTransform(int $viewIndex): string
    {
        return match ($viewIndex) {
            2 => 'translate(36,0) skewX(-9)',
            3 => 'matrix(-1 0 0 1 800 0) translate(-36,0) skewX(9)',
            4 => 'translate(120,0) scale(1.12)',
            5 => 'translate(-120,0) scale(0.9)',
            default => '',
        };
    }

    private function detectType(string $name, string $category): string
    {
        $n = mb_strtolower($name);
        $c = mb_strtolower($category);

        $map = [
            'кресло-кровать' => 'sofa',
            'кресло-качалка' => 'rocking-chair',
            'кресло офисн' => 'office-chair',
            'кресло' => 'armchair',
            'диван' => 'sofa',
            'кроват' => 'bed',
            'матрас' => 'mattress',
            'комод' => 'chest',
            'пенал' => 'tall-cabinet',
            'тумба под раковину' => 'vanity',
            'тумб' => 'chest',
            'гарнитур' => 'kitchen',
            'шкаф навесной' => 'wall-cabinet',
            'обеденная группа' => 'dining-set',
            'стеллаж' => 'shelf',
            'этажерк' => 'shelf',
            'прихожая' => 'hallway',
            'стенка' => 'wall-unit',
            'шкаф-купе' => 'sliding-wardrobe',
            'шкаф для документов' => 'document-cabinet',
            'шкаф' => 'wardrobe',
            'дверь-купе' => 'mirror-door',
            'дверь' => 'door',
            'вешалка' => 'coat-rack',
            'зеркало' => 'mirror',
            'пуф-банкетк' => 'pouf-bench',
            'пуф' => 'pouf',
            'стол' => 'table',
        ];

        foreach ($map as $keyword => $type) {
            if (str_contains($n, $keyword)) {
                return $type;
            }
        }

        return match ($c) {
            'диваны' => 'sofa',
            'кресла' => 'armchair',
            'пуфы' => 'pouf',
            'матрасы' => 'mattress',
            'стеллажи' => 'shelf',
            'столы' => 'table',
            'мебель для кухни' => 'kitchen',
            'мебель для спальни' => 'bed',
            default => 'chest',
        };
    }

    private function drawItem(string $type, string $name, string $light, string $base, string $dark): string
    {
        return match ($type) {
            'sofa' => $this->drawSofa($name, $light, $base, $dark),
            'armchair' => $this->drawArmchair($light, $base, $dark),
            'office-chair' => $this->drawOfficeChair($light, $base, $dark),
            'rocking-chair' => $this->drawRockingChair($light, $base, $dark),
            'bed' => $this->drawBed($name, $light, $base, $dark),
            'mattress' => $this->drawMattress($light, $base, $dark),
            'chest' => $this->drawChest($name, $light, $base, $dark),
            'vanity' => $this->drawVanity($light, $base, $dark),
            'tall-cabinet' => $this->drawTallCabinet($light, $base, $dark),
            'kitchen' => $this->drawKitchen($light, $base, $dark),
            'wall-cabinet' => $this->drawWallCabinet($light, $base, $dark),
            'shelf' => $this->drawShelf($light, $base, $dark),
            'hallway' => $this->drawHallway($light, $base, $dark),
            'wall-unit' => $this->drawWallUnit($light, $base, $dark),
            'table' => $this->drawTable($name, $light, $base, $dark),
            'dining-set' => $this->drawDiningSet($light, $base, $dark),
            'sliding-wardrobe' => $this->drawSlidingWardrobe($light, $base, $dark),
            'wardrobe' => $this->drawWardrobe($light, $base, $dark),
            'document-cabinet' => $this->drawDocumentCabinet($light, $base, $dark),
            'door' => $this->drawDoor($light, $base, $dark),
            'mirror-door' => $this->drawMirrorDoor($light, $base, $dark),
            'coat-rack' => $this->drawCoatRack($light, $base, $dark),
            'mirror' => $this->drawMirror($light, $base, $dark),
            'pouf' => $this->drawPouf($name, $light, $base, $dark),
            'pouf-bench' => $this->drawPoufBench($light, $base, $dark),
            default => $this->drawChest($name, $light, $base, $dark),
        };
    }

    private function drawSofa(string $name, string $light, string $base, string $dark): string
    {
        $wide = str_contains($name, 'углов');
        $left = $wide ? 145.0 : 185.0;
        $width = $wide ? 500.0 : 430.0;
        $rightArm = $left + $width + 17;
        $outer = $left - 17;

        $out = $this->shadow($wide ? 415 : $left + $width / 2, $wide ? 325 : 235);
        $out .= $this->rect($left + 45, 428, 10, 44, $dark);
        $out .= $this->rect($left + $width - 55, 428, 10, 44, $dark);
        $out .= $this->rect($left + $width + 8, 444, 11, 30, $dark);
        $out .= $this->rect($left - 24, 444, 11, 30, $dark);
        $out .= $this->rect($left, 252, $width, 158, $this->grad($base, $dark), 14);
        $out .= $this->rect($left, 396, $width, 52, $this->grad($light, $base), 8);

        if ($wide) {
            $out .= $this->rect($left + $width, 330, 92, 126, $this->grad($base, $dark), 14);
            $out .= $this->rect($left + $width + 8, 338, 76, 46, $this->grad($light, $base), 12);
            $out .= $this->rect($left + $width + 8, 374, 76, 62, $this->grad($base, $dark), 10);
            $out .= $this->stitch($left + $width + 16, 348, $left + $width + 76, 348, $light);
            $out .= $this->stitch($left + $width + 16, 358, $left + $width + 76, 358, $light);
        }

        $cushW = ($width - 46) / 3;
        $out .= $this->rect($left + 14, 372, $cushW, 40, $this->grad($light, $base), 9);
        $out .= $this->rect($left + 14 + $cushW + 10, 372, $cushW, 40, $this->grad($light, $base), 9);
        $out .= $this->rect($left + 14 + ($cushW + 10) * 2, 372, $cushW + 2, 40, $this->grad($light, $base), 9);
        $out .= $this->line($left + 14, 378, $left + $width - 32, 378, $dark, ' stroke-opacity="0.22" stroke-width="1.4"');
        $out .= $this->line($left + 14, 382, $left + $width - 32, 382, $dark, ' stroke-opacity="0.22" stroke-width="1.4"');

        $out .= $this->rect($outer, 286, 34, 166, $this->grad($dark, $base), 12);
        $out .= $this->rect($outer + 4, 278, 26, 14, $this->grad($light, $base), 7);
        $out .= $this->rect($rightArm, 286, 34, 166, $this->grad($dark, $base), 12);
        $out .= $this->rect($rightArm + 4, 278, 26, 14, $this->grad($light, $base), 7);

        $out .= $this->rect($left + 34, 262, 46, 46, $this->grad($light, '#f2ede4'), 10);
        $out .= $this->rect($left + 34, 262, 46, 46, 'none', 10, ' stroke="'.$dark.'" stroke-opacity="0.15" stroke-width="1.5"');
        $out .= $this->rect($left + $width - 80, 262, 46, 46, $this->grad('#f2ede4', $light), 10);

        return $out;
    }

    private function drawArmchair(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 125);
        $out .= $this->rect(318, 448, 12, 26, $dark);
        $out .= $this->rect(470, 448, 12, 26, $dark);
        $out .= $this->rect(318, 232, 164, 150, $this->grad($base, $dark), 16);
        $out .= $this->rect(326, 198, 148, 44, $this->grad($light, $base), 18);
        $out .= $this->rect(326, 240, 40, 86, $dark, 14);
        $out .= $this->rect(434, 240, 40, 86, $dark, 14);
        $out .= $this->rect(330, 262, 32, 14, $this->grad($light, $base), 7);
        $out .= $this->rect(438, 262, 32, 14, $this->grad($light, $base), 7);
        $out .= $this->rect(348, 368, 104, 64, $this->grad($light, $base), 12);
        $out .= $this->rect(326, 424, 148, 26, $this->grad($base, $dark), 9);
        $out .= $this->circle(362, 300, 24, $this->grad('#f2ede4', $light));
        $out .= $this->circle(362, 300, 24, 'none', ' stroke="'.$dark.'" stroke-opacity="0.15" stroke-width="1.5"');
        $out .= $this->stitch(336, 412, 456, 412, $dark);
        $out .= $this->stitch(336, 418, 456, 418, $dark);

        return $out;
    }

    private function drawOfficeChair(string $light, string $base, string $dark): string
    {
        $out = $this->ellipse(400, 512, 90, 9, '#000', ' opacity="0.09"');
        $out .= $this->line(370, 512, 430, 512, $dark, ' stroke-width="8" stroke-linecap="round"');
        $out .= $this->circle(350, 528, 9, $dark);
        $out .= $this->circle(405, 531, 9, $dark);
        $out .= $this->circle(456, 528, 9, $dark);
        $out .= $this->line(358, 519, 350, 526, $dark, ' stroke-width="7" stroke-linecap="round"');
        $out .= $this->line(414, 520, 405, 529, $dark, ' stroke-width="7" stroke-linecap="round"');
        $out .= $this->line(449, 519, 456, 526, $dark, ' stroke-width="7" stroke-linecap="round"');
        $out .= $this->rect(392, 470, 16, 40, $this->grad($dark, $base));
        $out .= $this->line(382, 432, 418, 432, $dark, ' stroke-width="4"');
        $out .= $this->rect(398, 400, 48, 52, $this->grad($base, $dark), 10);
        $out .= $this->rect(350, 382, 100, 22, $this->grad($light, $base), 10);
        $out .= $this->rect(366, 220, 68, 168, $this->grad($base, $dark), 32);
        $out .= $this->rect(376, 206, 48, 26, $this->grad($light, $base), 12);
        $out .= $this->rect(456, 260, 10, 96, $dark, 5);
        $out .= $this->circle(461, 256, 7, $dark);
        $out .= $this->rect(334, 260, 10, 96, $dark, 5);
        $out .= $this->circle(329, 256, 7, $dark);

        return $out;
    }

    private function drawRockingChair(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 150);
        $out .= $this->path('M 235 500 Q 400 532 570 497', 'none', ' stroke="'.$dark.'" stroke-width="16" stroke-linecap="round"');
        $out .= $this->path('M 241 492 Q 400 524 565 489', 'none', ' stroke="'.$this->tint($dark, 0.2).'" stroke-width="10" stroke-linecap="round"');
        $out .= $this->rect(348, 468, 12, 26, $dark);
        $out .= $this->rect(452, 468, 12, 26, $dark);
        $out .= $this->line(296, 468, 356, 468, $dark, ' stroke-width="9"');
        $out .= $this->line(456, 468, 516, 468, $dark, ' stroke-width="9"');
        $out .= $this->rect(300, 250, 200, 200, $this->grad($base, $dark), 18);
        for ($x = 318; $x <= 484; $x += 22) {
            $out .= $this->rect($x, 268, 8, 152, $this->grad($light, $base), 4);
        }
        $out .= $this->rect(282, 246, 236, 16, $this->grad($light, $base), 8);
        $out .= $this->rect(286, 378, 228, 66, $this->grad($light, $base), 14);
        $out .= $this->circle(346, 360, 20, $this->grad('#f2ede4', $light));
        $out .= $this->circle(470, 348, 18, $this->grad('#f2ede4', $light));

        return $out;
    }

    private function drawBed(string $name, string $light, string $base, string $dark): string
    {
        $kids = str_contains(mb_strtolower($name), 'детск');
        $cream = $this->grad('#fbf7ef', '#efe7d8');

        $out = $this->shadow(400, 255);
        $out .= $this->rect(280, 428, 12, 44, $dark);
        $out .= $this->rect(508, 428, 12, 44, $dark);
        $out .= $this->rect(196, 208, $kids ? 48 : 76, 246, $this->grad($dark, $base), 12);
        $out .= $this->rect(200, 208, 42, 10, $this->grad($light, $base), 5);
        $out .= $this->rect(612, 326, $kids ? 0 : 42, 76, $this->grad($dark, $base), 10);
        $out .= $this->rect($kids ? 268 : 258, $kids ? 352 : 348, 320, 84, $this->grad($base, $dark), 12);
        $out .= $this->rect(188, 338, 424, 104, $cream, 14);
        $out .= $this->rect(188, 338, 424, 104, 'none', 14, ' stroke="'.$this->tint($base, 0.7).'" stroke-opacity="0.5" stroke-width="2"');
        $out .= $this->rect(210, 262, 150, 84, $cream, 12);
        $out .= $this->rect(360, 262, 150, 84, $cream, 12);
        $out .= $this->rect(440, 338, 166, 104, $this->grad($light, $base), 12);
        $out .= $this->stitch(452, 348, 594, 348, $dark);
        $out .= $this->stitch(452, 356, 594, 356, $dark);
        $out .= $this->stitch(452, 430, 594, 430, $dark);
        $out .= $this->line(196, 330, 604, 330, $this->tint($base, 0.7), ' stroke-opacity="0.5" stroke-width="2"');
        $out .= $this->rect(268, 208, 304, 6, $this->grad($light, $base), 3);

        if ($kids) {
            $out .= $this->rect(268, 300, 34, 150, $this->grad($light, $base), 10);
            $out .= $this->rect(268, 300, 34, 150, 'none', 10, ' stroke="'.$dark.'" stroke-opacity="0.2" stroke-width="1.5"');
            $out .= $this->rect(272, 308, 26, 46, 'none', 8, ' stroke="'.$dark.'" stroke-opacity="0.35" stroke-width="2.5"');
            $out .= $this->rect(272, 362, 26, 46, 'none', 8, ' stroke="'.$dark.'" stroke-opacity="0.35" stroke-width="2.5"');
            $out .= $this->rect(476, 380, 130, 48, $this->grad('#9cc18f', '#7aa06f'), 8);
        }

        return $out;
    }

    private function drawMattress(string $light, string $base, string $dark): string
    {
        $top = $this->tint($base, 0.62);
        $out = $this->shadow(400, 288);
        $out .= $this->rect(180, 404, 440, 58, $this->grad($base, $dark), 10);
        $out .= $this->rect(176, 330, 448, 84, $this->grad($top, $this->tint($base, 0.4)), 14);
        $out .= $this->rect(176, 330, 448, 84, 'none', 14, ' stroke="'.$dark.'" stroke-opacity="0.25" stroke-width="2"');
        for ($x = 218; $x <= 582; $x += 42) {
            $out .= $this->line($x, 338, $x, 408, $base, ' stroke-opacity="0.35" stroke-width="2.5"');
            for ($y = 350; $y < 410; $y += 18) {
                $out .= $this->line($x - 16, $y, $x + 16, $y, $base, ' stroke-opacity="0.18" stroke-width="1.8"');
            }
        }
        $out .= $this->line(180, 404, 620, 404, $dark, ' stroke-opacity="0.4" stroke-width="2.5"');
        $out .= $this->ellipse(400, 404, 200, 6, '#000', ' opacity="0.08"');

        return $out;
    }

    private function drawChest(string $name, string $light, string $base, string $dark): string
    {
        $tv = str_contains($name, 'ТВ');
        $drawers = 4;
        if (str_contains($name, 'ящик') && preg_match('/\d+/', $name, $m)) {
            $drawers = max(2, min(6, (int) $m[0]));
        }
        if ($tv) {
            $drawers = 2;
        }

        $boxes = [];
        $out = $this->shadow(400, 175);
        $out .= $this->rect(258, 452, 14, 26, $dark);
        $out .= $this->rect(528, 452, 14, 26, $dark);
        $out .= $this->rect(250, 214, 300, 18, $this->grad($light, $base), 4);
        $out .= $this->rect(258, 232, 284, 232, $this->grad($base, $dark), 6);

        $top = 250;
        $dh = 234 / $drawers;
        for ($i = 0; $i < $drawers; $i++) {
            $y = $top + $i * $dh + 6;
            $h = $dh - 10;
            $box = $this->rect(272, $y, 256, $h, $this->grad($light, $base), 5);
            $box .= $this->circle(336, $y + $h / 2, 5, $dark);
            $box .= $this->circle(464, $y + $h / 2, 5, $dark);
            $boxes[] = $box;
        }
        $out .= implode('', $boxes);

        if ($tv) {
            $out .= $this->line(350, 214, 350, 180, $this->tint($dark, 0.2), ' stroke-width="8"');
            $out .= $this->line(285, 180, 420, 180, $dark, ' stroke-width="10" stroke-linecap="round"');
            $out .= $this->rect(285, 120, 150, 66, $this->grad('#2a2d33', '#101215'), 5);
            $out .= $this->line(295, 132, 425, 132, '#9fb4c8', ' stroke-width="6" opacity="0.5"');
        }

        return $out;
    }

    private function drawVanity(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 140);
        $out .= $this->rect(308, 470, 13, 10, $dark);
        $out .= $this->rect(479, 470, 13, 10, $dark);
        $out .= $this->rect(306, 300, 188, 174, $this->grad($base, $dark), 8);
        $out .= $this->rect(322, 316, 100, 62, $this->grad($light, $base), 6);
        $out .= $this->circle(387, 356, 7, $dark);
        $out .= $this->rect(322, 392, 156, 62, $this->grad($light, $base), 6);
        $out .= $this->circle(387, 426, 7, $dark);
        $out .= $this->rect(286, 282, 228, 22, $this->grad($base, $dark), 6);
        $out .= $this->ellipse(380, 282, 62, 26, $this->grad('#f3f4f0', '#d9dcd4'));
        $out .= $this->ellipse(380, 280, 46, 18, $this->grad('#bcd4cf', '#8fb8ae'));
        $out .= $this->ellipse(380, 280, 26, 10, '#e8efec');
        $out .= $this->ellipse(360, 276, 80, 34, 'none', ' stroke="'.$this->tint($base, 0.4).'" stroke-opacity="0.12"');
        $out .= $this->line(398, 262, 398, 282, $dark, ' stroke-width="5"');
        $out .= $this->circle(398, 258, 4, $dark);
        $mirror = $this->grad('#dce7f1', '#b7c9d8');
        $out .= $this->rect(330, 190, 96, 90, $dark, 8);
        $out .= $this->rect(336, 196, 84, 78, $mirror, 6);
        $out .= $this->line(348, 206, 410, 206, '#ffffff', ' stroke-width="7" opacity="0.7"');
        $out .= $this->rect(356, 158, 44, 36, $light, 7);
        $out .= $this->circle(364, 168, 4, $dark);
        $out .= $this->circle(392, 168, 4, $dark);

        return $out;
    }

    private function drawTallCabinet(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 105);
        $out .= $this->rect(345, 448, 16, 30, $dark);
        $out .= $this->rect(439, 448, 16, 30, $dark);
        $out .= $this->rect(338, 182, 124, 268, $this->grad($base, $dark), 6);
        $out .= $this->rect(352, 196, 60, 96, $this->grad($light, $base), 4);
        $out .= $this->rect(420, 196, 26, 96, $this->grad($light, $base), 4);
        $out .= $this->rect(352, 304, 96, 96, $this->grad($light, $base), 4);
        $out .= $this->circle(438, 352, 6, $dark);
        $out .= $this->rect(334, 166, 132, 16, $this->grad($light, $base), 4);

        return $out;
    }

    private function drawKitchen(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 300);
        $out .= $this->line(120, 402, 640, 402, $this->tint($base, 0.3), ' stroke-width="18" stroke-linecap="round"');
        $out .= $this->rect(132, 250, 116, 152, $this->grad($base, $dark), 3);
        $out .= $this->rect(148, 264, 84, 124, $this->grad($light, $base), 3);
        $out .= $this->circle(178, 330, 6, $dark);
        $out .= $this->rect(256, 250, 116, 152, $this->grad($base, $dark), 3);
        $out .= $this->rect(272, 264, 84, 124, $this->grad($light, $base), 3);
        $out .= $this->circle(302, 330, 6, $dark);
        $out .= $this->rect(380, 250, 116, 152, $this->grad($base, $dark), 3);
        $out .= $this->rect(396, 264, 84, 124, $this->grad($light, $base), 3);
        $out .= $this->circle(426, 330, 6, $dark);
        $out .= $this->rect(504, 250, 116, 152, $this->grad($base, $dark), 3);
        $out .= $this->rect(520, 264, 84, 124, $this->grad($light, $base), 3);
        $out .= $this->circle(550, 330, 6, $dark);
        $out .= $this->rect(160, 150, 110, 100, $this->grad($base, $dark), 3);
        $out .= $this->rect(176, 164, 78, 72, $this->grad($light, $base), 3);
        $out .= $this->circle(210, 200, 6, $dark);
        $out .= $this->rect(462, 150, 110, 100, $this->grad($base, $dark), 3);
        $out .= $this->rect(478, 164, 78, 72, $this->grad($light, $base), 3);
        $out .= $this->circle(512, 200, 6, $dark);
        $out .= $this->rect(300, 180, 60, 30, $this->grad($light, $base), 4);
        $out .= $this->circle(318, 195, 8, $dark);
        $out .= $this->rect(330, 408, 130, 52, $this->grad('#d8c7ae', '#b89f82'), 3);
        $out .= $this->rect(460, 408, 130, 52, $this->grad('#d8c7ae', '#b89f82'), 3);
        $out .= $this->circle(345, 434, 10, $dark);
        $out .= $this->circle(475, 434, 10, $dark);
        $out .= $this->rect(120, 402, 520, 12, $this->tint($dark, 0.2), 6);
        $out .= $this->line(140, 300, 600, 300, '#ffffff', ' stroke-width="2" opacity="0.35"');

        return $out;
    }

    private function drawWallCabinet(string $light, string $base, string $dark): string
    {
        $out = $this->rect(330, 130, 140, 180, 'none', 0, ' stroke="'.$dark.'" stroke-opacity="0.3" stroke-width="6"');
        $out .= $this->line(340, 182, 460, 182, $dark, ' stroke-width="4" stroke-opacity="0.35"');
        $out .= $this->rect(325, 152, 150, 168, $this->grad($base, $dark), 6);
        $out .= $this->rect(338, 168, 56, 136, $this->grad($light, $base), 4);
        $out .= $this->rect(404, 168, 56, 136, $this->grad($light, $base), 4);
        $out .= $this->circle(384, 240, 6, $dark);
        $out .= $this->rect(319, 300, 162, 18, $this->grad($dark, $base), 5);
        $out .= $this->ellipse(400, 320, 84, 12, '#000', ' opacity="0.12"');

        return $out;
    }

    private function drawShelf(string $light, string $base, string $dark): string
    {
        $bookColors = ['#c04848', '#3a7ca5', '#d0914a', '#5a8f6c', '#7a6fa5', '#c9806d', '#4a90a4', '#b06a8c'];
        $out = $this->shadow(400, 200);
        $out .= $this->rect(246, 452, 14, 26, $dark);
        $out .= $this->rect(540, 452, 14, 26, $dark);
        $out .= $this->rect(250, 180, 300, 296, $this->grad($base, $dark), 6);
        $out .= $this->rect(266, 196, 236, 14, $this->grad($light, $base), 0);
        $shelfTop = [302, 386, 470];
        foreach ($shelfTop as $sy) {
            $out .= $this->rect(254, $sy, 292, 14, $this->grad($light, $base), 0);
            for ($bx = 272, $i = 0; $bx < 520; $bx += 26, $i++) {
                $bh = 40 + (($i * 7) % 34);
                $out .= $this->rect($bx, $sy - 16 - $bh, 20, $bh, $bookColors[$i % count($bookColors)], 2, ' opacity="0.92"');
            }
            $out .= $this->line(268, $sy + 7, 526, $sy + 7, $dark, ' stroke-opacity="0.15" stroke-width="1.5"');
        }

        return $out;
    }

    private function drawHallway(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 230);
        $out .= $this->rect(318, 140, 164, 34, $light, 4);
        $out .= $this->rect(274, 264, 252, 30, $light, 6);
        $out .= $this->rect(274, 264, 252, 30, 'none', 6, ' stroke="'.$dark.'" stroke-opacity="0.15" stroke-width="1.5"');
        $out .= $this->rect(296, 324, 208, 134, $this->grad($base, $dark), 6);
        $out .= $this->rect(314, 340, 60, 40, $this->grad($light, $base), 4);
        $out .= $this->rect(314, 396, 60, 40, $this->grad($light, $base), 4);
        $out .= $this->circle(350, 362, 5, $dark);
        $out .= $this->circle(350, 418, 5, $dark);
        $out .= $this->rect(398, 340, 88, 96, $this->grad($dark, '#1c1e22'), 4);
        $out .= $this->line(308, 264, 492, 264, $dark, ' stroke-width="8" stroke-linecap="round"');
        $out .= $this->rect(248, 178, 22, 82, '#1c1e22', 6, ' stroke="'.$dark.'" stroke-width="7" fill="'.$dark.'"');
        $out .= $this->rect(248, 178, 22, 82, $dark, 6);
        $out .= $this->circle(259, 172, 11, $dark);
        $out .= $this->line(532, 178, 532, 260, $dark, ' stroke-width="7" stroke-linecap="round"');
        $out .= $this->rect(518, 168, 28, 22, $dark, 9);
        $out .= $this->line(532, 204, 532, 240, '#dfe2e7', ' stroke-width="6" stroke-linecap="round"');
        $out .= $this->rect(220, 120, 60, 60, 'none', 8, ' stroke="'.$dark.'" stroke-width="6"');
        $out .= $this->rect(226, 126, 48, 48, $this->grad('#dce7f1', '#b7c9d8'), 6);
        $out .= $this->line(234, 132, 266, 132, '#ffffff', ' stroke-width="5" opacity="0.7"');

        return $out;
    }

    private function drawWallUnit(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 320);
        $out .= $this->rect(120, 380, 560, 86, $this->grad($base, $dark), 6);
        $out .= $this->rect(138, 396, 96, 54, $this->grad($light, $base), 4);
        $out .= $this->circle(176, 428, 5, $dark);
        $out .= $this->rect(270, 396, 96, 54, $this->grad($light, $base), 4);
        $out .= $this->circle(308, 428, 5, $dark);
        $out .= $this->rect(402, 396, 96, 54, $this->grad($light, $base), 4);
        $out .= $this->circle(440, 428, 5, $dark);
        $out .= $this->rect(534, 396, 96, 54, $this->grad($light, $base), 4);
        $out .= $this->circle(572, 428, 5, $dark);
        $out .= $this->rect(130, 150, 130, 234, $this->grad($base, $dark), 6);
        $out .= $this->rect(146, 166, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(146, 196, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(146, 254, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(146, 312, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->line(148, 172, 242, 172, '#ffffff', ' stroke-width="3" opacity="0.6"');
        $out .= $this->rect(540, 150, 130, 234, $this->grad($base, $dark), 6);
        $out .= $this->rect(556, 166, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(556, 196, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(556, 254, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(556, 312, 98, 12, $this->grad($light, $base), 0);
        $out .= $this->rect(300, 200, 200, 122, $dark, 4);
        $out .= $this->rect(308, 208, 184, 106, $this->grad('#2a2d33', '#101215'), 4);
        $out .= $this->line(318, 220, 482, 220, '#9fb4c8', ' stroke-width="6" opacity="0.5"');
        $out .= $this->ellipse(400, 190, 26, 6, '#000', ' opacity="0.18"');
        $out .= $this->line(392, 200, 392, 184, $this->tint($dark, 0.2), ' stroke-width="7"');
        $out .= $this->line(392, 184, 430, 184, $dark, ' stroke-width="9" stroke-linecap="round"');
        $out .= $this->rect(276, 330, 248, 54, $this->grad($light, $base), 5);
        $out .= $this->circle(326, 358, 5, $dark);
        $out .= $this->circle(474, 358, 5, $dark);

        return $out;
    }

    private function drawTable(string $name, string $light, string $base, string $dark): string
    {
        if (str_contains($name, 'журнальн')) {
            $out = $this->shadow(400, 165);
            $out .= $this->rect(296, 418, 14, 34, $dark);
            $out .= $this->rect(490, 418, 14, 34, $dark);
            $out .= $this->rect(278, 360, 244, 30, $this->grad($base, $dark), 5);
            $out .= $this->rect(268, 330, 264, 20, $this->grad($light, $base), 6);
            $out .= $this->rect(300, 392, 200, 14, $this->grad($base, $dark), 4);
            $out .= $this->rect(320, 280, 44, 44, $this->grad('#c04848', '#8f2f2f'), 4);
            $out .= $this->rect(370, 280, 44, 44, $this->grad('#5a8f6c', '#3f6a4f'), 4);
            $out .= $this->ellipse(548, 414, 14, 22, 'none', ' stroke="'.$this->tint($base, 0.1).'"');

            return $out;
        }

        if (str_contains($name, 'компьютерн')) {
            $out = $this->shadow(400, 185);
            $out .= $this->rect(262, 428, 12, 34, $dark);
            $out .= $this->rect(526, 428, 12, 34, $dark);
            $out .= $this->rect(282, 322, 220, 40, $this->grad($base, $dark));
            $out .= $this->rect(258, 308, 284, 16, $this->grad($light, $base), 4);
            $out .= $this->rect(280, 362, 60, 38, $this->grad($light, $base), 4);
            $out .= $this->circle(306, 384, 5, $dark);
            $out .= $this->rect(472, 362, 60, 38, $this->grad($light, $base), 4);
            $out .= $this->circle(498, 384, 5, $dark);
            $out .= $this->line(356, 320, 356, 284, $this->tint($dark, 0.25), ' stroke-width="8"');
            $out .= $this->line(290, 284, 448, 284, $dark, ' stroke-width="12" stroke-linecap="round"');
            $out .= $this->rect(310, 196, 120, 88, $this->grad('#2a2d33', '#101215'), 4);
            $out .= $this->line(322, 210, 418, 210, '#9fb4c8', ' stroke-width="7" opacity="0.5"');
            $out .= $this->rect(364, 214, 54, 50, $this->grad('#23262c', '#14161a'), 3);
            $out .= $this->rect(362, 268, 58, 10, $dark, 3);

            return $out;
        }

        if (str_contains($name, 'школьн')) {
            $out = $this->shadow(400, 175);
            $out .= $this->rect(272, 424, 12, 34, $this->grad('#3a7ca5', '#285b7d'));
            $out .= $this->rect(516, 424, 12, 34, $this->grad('#3a7ca5', '#285b7d'));
            $out .= $this->rect(282, 330, 240, 36, $this->grad($base, $dark));
            $out .= $this->rect(262, 316, 280, 16, $this->grad($light, $base), 5);
            $out .= $this->rect(448, 366, 96, 58, $this->grad('#c04848', '#8f2f2f'), 5);
            $out .= $this->rect(468, 372, 28, 74, 'none', 5, ' stroke="'.$this->tint($dark, 0.3).'"');
            $out .= $this->line(456, 372, 536, 372, $dark, ' stroke-width="6"');
            $out .= $this->rect(290, 286, 54, 44, $this->grad('#5a8f6c', '#3f6a4f'), 5);
            $out .= $this->circle(310, 306, 5, $this->grad('#f2ede4', '#d8d0c0'));

            return $out;
        }

        $conference = str_contains($name, 'переговор') || str_contains($name, 'руководител');
        $left = $conference ? 150.0 : 210.0;
        $width = $conference ? 500.0 : 380.0;

        $out = $this->shadow(400, 240);
        $out .= $this->rect($left + 40, 430, 14, 40, $dark);
        $out .= $this->rect($left + $width - 54, 430, 14, 40, $dark);
        if (! $conference) {
            $out .= $this->rect($left + $width / 2 - 8, 420, 16, 52, $this->tint($dark, 0.15));
        } else {
            $out .= $this->rect($left + $width / 2 - 7, 424, 14, 48, $this->tint($dark, 0.15));
        }
        $out .= $this->rect($left, 360, $width, 30, $this->grad($base, $dark), 5);
        $out .= $this->rect($left - 14, 344, $width + 28, 18, $this->grad($light, $base), 7);
        $out .= $this->line($left - 12, 362, $left + $width + 12, 362, '#ffffff', ' stroke-width="2" opacity="0.4"');

        return $out;
    }

    private function drawDiningSet(string $light, string $base, string $dark): string
    {
        $wood = $this->grad($this->tint($base, 0.2), $dark);
        $out = $this->shadow(400, 255);
        $out .= $this->rect(168, 440, 12, 32, $dark);
        $out .= $this->rect(620, 440, 12, 32, $dark);
        $out .= $this->rect(170, 380, 460, 34, $wood, 5);
        $out .= $this->rect(150, 364, 500, 20, $this->grad($light, $base), 6);
        $out .= $this->rect(192, 330, 96, 40, $this->grad($this->tint($base, 0.55), $light), 8);
        $out .= $this->line(240, 326, 240, 370, $dark, ' stroke-width="8"');
        $out .= $this->rect(512, 330, 96, 40, $this->grad($this->tint($base, 0.55), $light), 8);
        $out .= $this->line(560, 326, 560, 370, $dark, ' stroke-width="8"');
        $out .= $this->rect(210, 396, 20, 24, $dark, 4);
        $out .= $this->rect(570, 396, 20, 24, $dark, 4);
        $out .= $this->circle(400, 400, 20, $this->grad('#f2ede4', '#dccfb8'));
        $out .= $this->circle(400, 400, 13, $this->grad($light, $base));
        $out .= $this->ellipse(400, 376, 210, 7, '#000', ' opacity="0.06"');

        return $out;
    }

    private function drawSlidingWardrobe(string $light, string $base, string $dark): string
    {
        $mirror = $this->grad('#dce7f1', '#b7c9d8');
        $out = $this->shadow(400, 265);
        $out .= $this->line(160, 150, 640, 150, $this->tint($dark, 0.1), ' stroke-width="8" stroke-linecap="round"');
        $out .= $this->rect(150, 152, 500, 318, $this->grad($base, $dark), 4);
        $out .= $this->rect(166, 168, 132, 284, $this->grad($light, $base), 4);
        $out .= $this->rect(156, 162, 10, 298, $this->tint($dark, 0.35));
        $out .= $this->rect(340, 168, 120, 284, $mirror, 4);
        $out .= $this->line(352, 182, 448, 182, '#ffffff', ' stroke-width="6" opacity="0.6"');
        $out .= $this->rect(480, 168, 54, 284, $this->grad($light, $base), 4);
        $out .= $this->rect(320, 168, 12, 284, $this->tint($dark, 0.3));
        $out .= $this->rect(478, 216, 8, 88, $dark, 4);
        $out .= $this->line(160, 462, 640, 462, $this->tint($dark, 0.1), ' stroke-width="8" stroke-linecap="round"');

        $out .= $this->rect(398, 154, 30, 4, $this->tint($base, 0.6), 2);

        return $out;
    }

    private function drawWardrobe(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 230);
        $out .= $this->rect(250, 454, 16, 24, $dark);
        $out .= $this->rect(534, 454, 16, 24, $dark);
        $out .= $this->rect(242, 168, 316, 292, $this->grad($base, $dark), 6);
        $out .= $this->rect(258, 184, 132, 258, $this->grad($light, $base, false), 4);
        $out .= $this->rect(410, 184, 132, 258, $this->grad($light, $base, false), 4);
        $out .= $this->circle(368, 320, 7, $dark);
        $out .= $this->circle(432, 320, 7, $dark);
        $out .= $this->rect(238, 150, 324, 20, $this->grad($light, $base), 4);
        $out .= $this->line(250, 148, 550, 148, '#ffffff', ' stroke-width="3" opacity="0.5"');
        $out .= $this->ellipse(400, 470, 90, 6, '#000', ' opacity="0.08"');

        return $out;
    }

    private function drawDocumentCabinet(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 120);
        $out .= $this->rect(336, 452, 14, 26, $dark);
        $out .= $this->rect(450, 452, 14, 26, $dark);
        $out .= $this->rect(328, 190, 144, 264, $this->grad($base, $dark), 5);
        $out .= $this->rect(344, 206, 52, 232, $this->grad($light, $base, false), 3);
        $out .= $this->rect(404, 206, 52, 232, $this->grad($light, $base, false), 3);
        $out .= $this->circle(410, 326, 6, $dark);
        $out .= $this->circle(390, 326, 6, $dark);
        $out .= $this->rect(324, 172, 148, 18, $this->grad($light, $base), 4);
        $out .= $this->line(336, 202, 464, 202, $this->tint($dark, 0.3), ' stroke-width="2" stroke-opacity="0.5"');

        return $out;
    }

    private function drawDoor(string $light, string $base, string $dark): string
    {
        $frosted = $this->grad('#e8ecf2', '#c3ccd8');
        $out = $this->shadow(400, 105);
        $out .= $this->line(330, 138, 470, 138, $this->tint($dark, 0.1), ' stroke-width="8" stroke-linecap="round"');
        $out .= $this->rect(320, 142, 160, 320, $this->grad($base, $dark), 6);
        $out .= $this->rect(336, 156, 128, 290, $frosted, 4);
        $out .= $this->rect(336, 156, 128, 290, 'none', 4, ' stroke="'.$this->tint($light, 0.3).'" stroke-width="5" stroke-opacity="0.7"');
        $out .= $this->line(352, 172, 448, 172, '#ffffff', ' stroke-width="5" opacity="0.6"');
        $out .= $this->line(352, 430, 448, 430, '#ffffff', ' stroke-width="5" opacity="0.6"');
        $out .= $this->rect(430, 360, 12, 40, $dark, 4);
        $out .= $this->line(320, 344, 480, 344, $dark, ' stroke-width="3" stroke-opacity="0.4"');
        $out .= $this->line(320, 398, 480, 398, $dark, ' stroke-width="3" stroke-opacity="0.4"');

        return $out;
    }

    private function drawMirrorDoor(string $light, string $base, string $dark): string
    {
        $mirror = $this->grad('#dce7f1', '#9fb6ca');
        $out = $this->shadow(400, 105);
        $out .= $this->line(330, 136, 470, 136, $this->tint($dark, 0.1), ' stroke-width="8" stroke-linecap="round"');
        $out .= $this->rect(320, 140, 160, 322, $this->grad($base, $dark), 6);
        $out .= $this->rect(336, 154, 128, 292, $mirror, 4);
        $out .= $this->rect(336, 154, 128, 292, 'none', 4, ' stroke="'.$this->tint($light, 0.4).'" stroke-width="5" stroke-opacity="0.8"');
        $out .= $this->line(352, 170, 448, 170, '#ffffff', ' stroke-width="5" opacity="0.7"');
        $out .= $this->line(352, 430, 448, 430, '#ffffff', ' stroke-width="5" opacity="0.7"');
        $out .= $this->path('M 340 400 Q 360 350 400 360', 'none', ' stroke="#ffffff" stroke-width="4" opacity="0.5" fill="none"');
        $out .= $this->rect(430, 358, 12, 40, $dark, 4);
        $out .= $this->line(320, 346, 480, 346, $dark, ' stroke-width="3" stroke-opacity="0.45"');

        return $out;
    }

    private function drawCoatRack(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 75);
        $out .= $this->line(370, 300, 430, 300, $dark, ' stroke-width="12" stroke-linecap="round"');
        $out .= $this->rect(352, 296, 96, 20, $dark, 8);
        $out .= $this->line(400, 164, 400, 296, $this->tint($dark, 0.05), ' stroke-width="12" stroke-linecap="round"');
        $out .= $this->line(384, 180, 416, 180, $this->tint($dark, 0.15), ' stroke-width="10" stroke-linecap="round"');
        $out .= $this->circle(376, 268, 7, $dark);
        $out .= $this->circle(424, 268, 7, $dark);
        $out .= $this->circle(376, 236, 7, $dark);
        $out .= $this->circle(424, 236, 7, $dark);
        $out .= $this->circle(400, 150, 30, $this->grad('#c0a06a', '#8f7644'));
        $out .= $this->rect(388, 130, 24, 14, $this->grad('#c0a06a', '#8f7644'), 4);
        $out .= $this->ellipse(400, 300, 40, 10, $dark, ' opacity="0.5"');

        return $out;
    }

    private function drawMirror(string $light, string $base, string $dark): string
    {
        $mirror = $this->grad('#dce7f1', '#a9bfd2');
        $out = $this->shadow(400, 95);
        $out .= $this->rect(352, 430, 12, 42, $dark);
        $out .= $this->rect(436, 430, 12, 42, $dark);
        $out .= $this->rect(356, 430, 88, 10, $dark, 5);
        $out .= $this->rect(348, 168, 104, 262, $this->grad($base, $dark), 8);
        $out .= $this->rect(360, 180, 80, 238, $mirror, 6);
        $out .= $this->line(372, 194, 428, 194, '#ffffff', ' stroke-width="5" opacity="0.7"');
        $out .= $this->line(372, 210, 428, 210, '#ffffff', ' stroke-width="5" opacity="0.55"');

        return $out;
    }

    private function drawPouf(string $name, string $light, string $base, string $dark): string
    {
        if (str_contains($name, 'кругл')) {
            $out = $this->shadow(400, 75);
            $out .= $this->ellipse(400, 270, 85, 72, $this->grad($light, $base));
            $out .= $this->ellipse(400, 380, 58, 34, $this->grad($base, $dark));
            $out .= $this->circle(400, 285, 26, $dark, ' opacity="0.18"');
            $out .= $this->circle(372, 272, 10, '#ffffff', ' opacity="0.25"');

            return $out;
        }

        $out = $this->shadow(400, 75);
        $out .= $this->rect(358, 286, 84, 84, $this->grad($light, $base), 14);
        $out .= $this->rect(370, 300, 60, 2, $dark, 1, ' opacity="0.3"');
        $out .= $this->rect(370, 356, 60, 2, $dark, 1, ' opacity="0.3"');
        $out .= $this->circle(400, 330, 12, $dark, ' opacity="0.22"');
        $out .= $this->circle(380, 314, 9, '#ffffff', ' opacity="0.25"');
        $out .= $this->rect(358, 366, 84, 82, $this->grad($base, $dark), 12);

        return $out;
    }

    private function drawPoufBench(string $light, string $base, string $dark): string
    {
        $out = $this->shadow(400, 145);
        $out .= $this->rect(332, 462, 13, 16, $dark);
        $out .= $this->rect(455, 462, 13, 16, $dark);
        $out .= $this->rect(316, 300, 168, 70, $this->grad($light, $base), 10);
        $out .= $this->rect(316, 300, 168, 70, 'none', 10, ' stroke="'.$dark.'" stroke-opacity="0.2" stroke-width="1.5"');
        $out .= $this->line(332, 314, 468, 314, $dark, ' stroke-width="1.6" stroke-opacity="0.3"');
        $out .= $this->rect(322, 368, 156, 98, $this->grad($base, $dark), 8);
        $out .= $this->line(344, 386, 456, 386, $dark, ' stroke-width="2" stroke-opacity="0.4"');
        $out .= $this->circle(404, 336, 11, $dark, ' opacity="0.25"');
        $out .= $this->circle(388, 322, 8, '#ffffff', ' opacity="0.25"');

        return $out;
    }
}
