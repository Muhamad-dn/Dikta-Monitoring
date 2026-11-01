<?php

namespace App\Services;

class BarcodeService
{
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213', // 0-9
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132', // 10-19
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211', // 20-29
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313', // 30-39
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331', // 40-49
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111', // 50-59
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214', // 60-69
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111', // 70-79
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141', // 80-89
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141', // 90-99
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112' // 100-106
    ];

    /**
     * Generate standard Code 128 (Subset B) SVG string.
     */
    public static function code128Svg(string $code, int $height = 46, float $moduleWidth = 2.0, string $barColor = '#0f172a'): string
    {
        $code = trim($code);
        if ($code === '') {
            $code = '-';
        }

        // Subset B: Start code = 104
        $symbols = [104];
        $checksum = 104;

        $len = strlen($code);
        for ($i = 0; $i < $len; $i++) {
            $charVal = ord($code[$i]) - 32;
            if ($charVal < 0 || $charVal > 94) {
                $charVal = 0; // fallback to space
            }
            $symbols[] = $charVal;
            $checksum += ($i + 1) * $charVal;
        }

        $checkChar = $checksum % 103;
        $symbols[] = $checkChar;
        $symbols[] = 106; // Stop code

        $widths = [];
        foreach ($symbols as $sym) {
            $pat = self::PATTERNS[$sym];
            $patLen = strlen($pat);
            for ($j = 0; $j < $patLen; $j++) {
                $widths[] = (int) $pat[$j];
            }
        }

        $quietZone = 8;
        $totalModules = array_sum($widths) + ($quietZone * 2);
        $totalWidth = $totalModules * $moduleWidth;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $totalWidth . ' ' . $height . '" class="w-full h-auto max-h-[50px]" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Barcode ' . htmlspecialchars($code) . '">';
        
        $currentX = $quietZone * $moduleWidth;
        $isBar = true;

        foreach ($widths as $w) {
            $barW = $w * $moduleWidth;
            if ($isBar) {
                $svg .= '<rect x="' . $currentX . '" y="0" width="' . $barW . '" height="' . $height . '" fill="' . $barColor . '"/>';
            }
            $currentX += $barW;
            $isBar = !$isBar;
        }

        $svg .= '</svg>';

        return $svg;
    }
}
