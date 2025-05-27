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
    public $responsive = false;
    public $maintainAspectRatio = false;
    public $darkMode = false;
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

        $canvasAttributes = [
            'id' => $this->elementId,
            'style' => "display:block;" . ($this->darkMode ? "background-color:#1e1e1e;" : "")
        ];

        if (!$this->responsive) {
            $canvasAttributes['width'] = $widthPx;
            $canvasAttributes['height'] = $heightPx;
            $canvasAttributes['style'] .= "width: {$widthPx}px; height: {$heightPx}px;";
        }

        $canvas = Html::tag('canvas', '', $canvasAttributes);

        $chartData = [
            'labels' => $this->labels,
            'datasets' => $this->datasets,
        ];

        $defaultOptions = [
            'responsive' => $this->responsive,
            'maintainAspectRatio' => $this->maintainAspectRatio,
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
                if (!isset($defaultOptions['scales'][$axis])) {
                    $defaultOptions['scales'][$axis] = [];
                }
                if (!isset($defaultOptions['scales'][$axis]['ticks'])) {
                    $defaultOptions['scales'][$axis]['ticks'] = [];
                }
                $defaultOptions['scales'][$axis]['ticks']['color'] = '#fff';
                $defaultOptions['scales'][$axis]['grid']['color'] = 'rgba(255,255,255,0.1)';
            }
        }

        if ($this->type === 'horizontalBar') {
            $defaultOptions['indexAxis'] = 'y';
        }

        $chartOptions = array_merge_recursive($defaultOptions, $this->options);

        $js = new JsExpression("
            new Chart(document.getElementById('{$this->elementId}'), {
                type: '{$this->type}',
                data: " . Json::encode($chartData) . ",
                options: " . Json::encode($chartOptions) . "
            });
        ");

        $this->getView()->registerJs($js);

        $summaryHtml = '';
        if ($this->showSummary) {
            $summary = $this->generateSummaryValues();
            $summaryText = "Min: {$summary['min']} | Max: {$summary['max']} | Avg: {$summary['avg']}";
            $summaryHtml = Html::tag('div', $summaryText, ['style' => 'margin-top:10px;font-size:14px;color:#000']);
        }

        $exportBtnHtml = '';
        if ($this->exportButton) {
            $exportBtnHtml = Html::button('Download Chart', [
                'onclick' => "downloadChart('{$this->elementId}')",
                'style' => 'margin-top:10px; padding:5px 10px; cursor:pointer;'
            ]);
            $this->getView()->registerJs("
                function downloadChart(id) {
                    const canvas = document.getElementById(id);
                    if (!canvas) {
                        alert('Chart not found!');
                        return;
                    }

                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = canvas.width;
                    tempCanvas.height = canvas.height;
                    const ctx = tempCanvas.getContext('2d');

                    ctx.fillStyle = '#1e1e1e';
                    ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                    ctx.drawImage(canvas, 0, 0);

                    const url = tempCanvas.toDataURL('image/jpeg', 1.0);

                    const a = document.createElement('a');
                    a.href = url;
                    a.download = id + '.jpg';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }
            ", \yii\web\View::POS_HEAD);
        }

        $wrapperStyle = $this->responsive ? "max-width: {$this->width}; margin: 0 auto;" : '';
        $canvasWrapper = Html::tag('div', $canvas, ['style' => $wrapperStyle]);

        return $canvasWrapper . $exportBtnHtml . $summaryHtml;
    }

    protected function registerAssets() {
        $this->getView()->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', [
            'depends' => [\yii\web\JqueryAsset::class],
            'position' => \yii\web\View::POS_END,
        ]);
    }

    private function assignColors($datasets) {
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

    private function resolveScales() {
        return match ($this->type) {
            'horizontalBar' => ['x' => ['beginAtZero' => true]],
            'bar', 'line' => ['y' => ['beginAtZero' => true]],
            'radar' => ['r' => ['beginAtZero' => true]],
            'bubble', 'scatter' => [
                'x' => ['beginAtZero' => true],
                'y' => ['beginAtZero' => true],
            ],
            'pie', 'doughnut', 'polarArea' => [],
            default => [],
        };
    }

    protected function parsePixelValue($value, $fallback = 400) {
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

    private function generateSummaryValues(): array {
        $all = [];
        foreach ($this->datasets as $d) {
            if (!empty($d['data']) && is_array($d['data'])) {
                foreach ($d['data'] as $value) {
                    if (is_numeric($value)) {
                        $all[] = $value;
                    } elseif (is_array($value) && isset($value[1]) && is_numeric($value[1])) {
                        $all[] = $value[1];
                    }
                }
            }
        }

        if (empty($all)) {
            return ['min' => 0, 'max' => 0, 'avg' => 0];
        }

        return [
            'min' => min($all),
            'max' => max($all),
            'avg' => round(array_sum($all) / count($all), 2),
        ];
    }
}