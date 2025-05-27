<?php

namespace ictpl\ChartWidget;

class ChartRenderer
{
    public static function render(
        string $elementId,
        string $type,
        array $labels,
        array $datasets,
        array $options = [],
        string $width = '800px',
        string $height = '400px',
        bool $darkMode = false
    ): string {
        $datasets = self::assignColors($datasets);
        $chartData = json_encode([
            'labels' => $labels,
            'datasets' => $datasets,
        ], JSON_UNESCAPED_UNICODE);

        $chartOptions = json_encode($options, JSON_UNESCAPED_UNICODE);

        return <<<HTML
<canvas id="{$elementId}" width="{$width}" height="{$height}" style="width: {$width}; height: {$height}; display:block;"></canvas>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    new Chart(document.getElementById('{$elementId}'), {
        type: '{$type}',
        data: {$chartData},
        options: {$chartOptions}
    });
});
</script>
HTML;
    }

    private static function assignColors(array $datasets): array
    {
        $defaultColors = [
            'rgba(255, 99, 132, 0.6)',
            'rgba(54, 162, 235, 0.6)',
            'rgba(255, 206, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
        ];

        $borderColors = array_map(fn($c) => str_replace('0.6', '1', $c), $defaultColors);

        foreach ($datasets as $i => &$set) {
            $set['backgroundColor'] ??= $defaultColors[$i % count($defaultColors)];
            $set['borderColor'] ??= $borderColors[$i % count($borderColors)];
            $set['borderWidth'] ??= 1;
        }

        return $datasets;
    }
}