<?php

namespace nouman3070\MyChartWidget;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use Yii;

class ChartWidget extends Widget {

    public $elementId = 'chart';
    public $labels = [];
    public $datasets = [];
    public $type = 'bar';
    public $options = [];
    public $width = '800px';
    public $height = '400px';
    public $darkMode = false;
    public $mini = false;
    public $preset = 'default';
    public $showSummary = false;
    public $exportButton = false;

    public function init() {
        parent::init();

        if ($this->elementId === 'chart') {
            $this->elementId .= '_' . uniqid();
        }

        $this->datasets = $this->assignColors($this->datasets);
    }

    public function run() {
        $this->registerAssets();

        $widthPx = $this->parsePixelValue($this->width, 800);
        $heightPx = $this->parsePixelValue($this->height, 400);

        $canvas = Html::tag('canvas', '', [
            'id' => $this->elementId,
            'width' => $widthPx,
            'height' => $heightPx,
            'style' => "width: {$widthPx}px; height: {$heightPx}px; display:block;",
        ]);

        $summary = $this->showSummary ? Html::tag('div', $this->generateSummary(), ['style' => 'margin-top:10px;font-size:14px;']) : '';
        $button = $this->exportButton ? Html::button('Download Chart', ['onclick' => "downloadChart('{$this->elementId}')", 'style' => 'margin-top:10px;']) : '';

        $chartData = [
            'labels' => $this->labels,
            'datasets' => $this->datasets,
        ];

        $defaultOptions = $this->getDefaultOptions();

        $chartOptions = array_merge_recursive($defaultOptions, $this->options);

        $js = new JsExpression("new Chart(document.getElementById('{$this->elementId}'), {
            type: '{$this->type}',
            data: " . Json::encode($chartData) . ",
            options: " . Json::encode($chartOptions) . "
        });");

        $this->getView()->registerJs($js);

        if ($this->exportButton) {
            $this->getView()->registerJs("function downloadChart(id) {
                const chart = document.getElementById(id);
                const url = chart.toDataURL();
                const a = document.createElement('a');
                a.href = url;
                a.download = id + '.png';
                a.click();
            }");
        }

        return $canvas . $button . $summary;
    }

    protected function registerAssets() {
        $this->getView()->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', [
            'depends' => [\yii\web\JqueryAsset::class],
            'position' => \yii\web\View::POS_END,
        ]);
    }

    private function generateColor(int $index): string {
        $hue = ($index * 137) % 360;
        return "hsl($hue, 70%, 60%)";
    }

    private function assignColors($datasets) {
        foreach ($datasets as $i => &$set) {
            $color = $this->generateColor($i);
            $set['backgroundColor'] ??= $color;
            $set['borderColor'] ??= $color;
            $set['borderWidth'] ??= 1;
        }
        return $datasets;
    }

    private function getDefaultOptions() {
        $options = [
            'responsive' => false,
            'maintainAspectRatio' => false,
            'scales' => $this->resolveScales(),
            'plugins' => [
                'legend' => ['display' => true],
                'tooltip' => ['enabled' => true],
            ],
        ];

        if (in_array($this->type, ['pie', 'doughnut', 'polarArea'])) {
            unset($options['scales']);
        }

        if ($this->darkMode) {
            $options['plugins']['legend']['labels'] = ['color' => '#fff'];
            foreach (['x', 'y', 'r'] as $axis) {
                if (isset($options['scales'][$axis])) {
                    $options['scales'][$axis]['ticks']['color'] = '#fff';
                }
            }
        }

        if ($this->type === 'horizontalBar') {
            $options['indexAxis'] = 'y';
        }

        if ($this->mini) {
            $options['plugins']['legend']['display'] = false;
            $options['plugins']['tooltip']['enabled'] = false;
            $options['scales'] = [];
        }

        return $options;
    }

    private function resolveScales() {
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

    private function parsePixelValue($value, $fallback = 400) {
        if (is_int($value)) return $value;
        if (preg_match('/^(\d+)px$/', $value, $matches)) return (int) $matches[1];
        if (preg_match('/^\d+$/', $value)) return (int) $value;
        return $fallback;
    }

    private function generateSummary(): string {
        $all = [];
        foreach ($this->datasets as $d) {
            $all = array_merge($all, $d['data'] ?? []);
        }
        if (empty($all)) return '';
        $min = min($all);
        $max = max($all);
        $avg = round(array_sum($all) / count($all), 2);
        return "Min: $min | Max: $max | Avg: $avg";
    }

}