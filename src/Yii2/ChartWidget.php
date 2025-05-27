<?php

namespace ictpl\ChartWidget\Yii2;


use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

class ChartWidget extends Widget
{
    public $elementId = 'chart';
    public $labels = [];
    public $datasets = [];
    public $type = 'bar';
    public $options = [];
    public $width = '800px';
    public $height = '400px';
    public $darkMode = false;

    public function run(): string
    {
        if ($this->elementId === 'chart') {
            $this->elementId .= '_' . uniqid();
        }

        $this->datasets = $this->assignColors($this->datasets);
        $widthPx = $this->parsePixelValue($this->width, 800);
        $heightPx = $this->parsePixelValue($this->height, 400);

        $canvas = "<canvas id=\"{$this->elementId}\" width=\"{$widthPx}\" height=\"{$heightPx}\" style=\"width: {$widthPx}px; height: {$heightPx}px; display:block;\"></canvas>";

        $chartData = [
            'labels' => $this->labels,
            'datasets' => $this->datasets,
        ];

        $defaultOptions = [
            'responsive' => false,
            'maintainAspectRatio' => false,
            'scales' => $this->resolveScales(),
            'plugins' => [
                'legend' => ['display' => true],
                'tooltip' => ['enabled' => true],
            ],
        ];

        if (in_array($this->type, ['pie', 'doughnut', 'polarArea'])) {
            unset($defaultOptions['scales']);
        }

        if ($this->darkMode) {
            $defaultOptions['plugins']['legend']['labels'] = ['color' => '#fff'];
            foreach (['x', 'y', 'r'] as $axis) {
                if (isset($defaultOptions['scales'][$axis])) {
                    $defaultOptions['scales'][$axis]['ticks']['color'] = '#fff';
                }
            }
        }

        if ($this->type === 'horizontalBar') {
            $defaultOptions['indexAxis'] = 'y';
        }

        $chartOptions = array_merge_recursive($defaultOptions, $this->options);

        $js = <<<JS
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    new Chart(document.getElementById('{$this->elementId}'), {
        type: '{$this->type}',
        data: {$this->jsonEncode($chartData)},
        options: {$this->jsonEncode($chartOptions)}
    });
});
</script>
JS;

        return $canvas . $js;
    }

    private function assignColors(array $datasets): array
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

    private function resolveScales(): array
    {
        return match ($this->type) {
            'horizontalBar' => ['x' => ['beginAtZero' => true]],
            'bar', 'line' => ['y' => ['beginAtZero' => true]],
            'radar' => ['r' => ['beginAtZero' => true]],
            'bubble', 'scatter' => [
                'x' => ['beginAtZero' => true],
                'y' => ['beginAtZero' => true],
            ],
            default => [],
        };
    }

    private function parsePixelValue($value, $fallback = 400): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value)) {
            if (preg_match('/^(\d+)px$/', $value, $matches)) {
                return (int) $matches[1];
            }
            if (preg_match('/^\d+$/', $value)) {
                return (int) $value;
            }
        }
        return $fallback;
    }

    private function jsonEncode(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}