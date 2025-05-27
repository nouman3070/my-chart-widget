
# Yii2 ChartWidget

A reusable Chart.js wrapper widget for Yii2, allowing easy rendering of multiple chart types (Bar, Line, Pie, Radar, etc.) with a simple PHP configuration.

## 📦 Installation

Install the package via Composer:

```bash
composer require nouman3070/yii2-chart-widget
```

## 🚀 Usage

Use the widget in your view file:

```php
use nouman3070\ChartWidget\ChartWidget;
```

## 📊 Supported Chart Types

- Line
- Bar
- Horizontal Bar
- Pie
- Doughnut
- Radar
- Polar Area
- Scatter

## 📑 Detailed Examples

### Line Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'lineChart',
    'type' => 'line',
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
    'datasets' => [
        ['label' => 'Sales', 'data' => [100, 200, 150, 300], 'fill' => false],
        ['label' => 'Purchase', 'data' => [80, 120, 90, 240], 'fill' => false],
    ],
]) ?>
```

### Bar Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'barChart',
    'type' => 'bar',
    'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
    'datasets' => [
        ['label' => 'Income', 'data' => [1000, 1500, 1300, 2000]],
    ],
]) ?>
```

### Horizontal Bar Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'hBarChart',
    'type' => 'horizontalBar',
    'labels' => ['Product A', 'Product B', 'Product C'],
    'datasets' => [
        ['label' => 'Units Sold', 'data' => [300, 500, 200]],
    ],
]) ?>
```

### Pie Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'pieChart',
    'type' => 'pie',
    'labels' => ['Chrome', 'Firefox', 'Safari'],
    'datasets' => [
        ['label' => 'Browser Share', 'data' => [60, 25, 15]],
    ],
]) ?>
```

### Doughnut Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'doughnutChart',
    'type' => 'doughnut',
    'labels' => ['USA', 'India', 'UK'],
    'datasets' => [
        ['label' => 'Users', 'data' => [300, 400, 100]],
    ],
]) ?>
```

### Radar Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'radarChart',
    'type' => 'radar',
    'labels' => ['Speed', 'Strength', 'Agility', 'Stamina', 'Skill'],
    'datasets' => [
        ['label' => 'Player A', 'data' => [65, 75, 70, 80, 90]],
        ['label' => 'Player B', 'data' => [50, 85, 60, 70, 80]],
    ],
]) ?>
```

### Polar Area Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'polarChart',
    'type' => 'polarArea',
    'labels' => ['A', 'B', 'C', 'D'],
    'datasets' => [
        ['label' => 'Scores', 'data' => [11, 16, 7, 3]],
    ],
]) ?>
```

### Scatter Chart

```php
<?= ChartWidget::widget([
    'elementId' => 'scatterChart',
    'type' => 'scatter',
    'labels' => [],
    'datasets' => [
        [
            'label' => 'Observations',
            'data' => [
                ['x' => -10, 'y' => 0],
                ['x' => 0, 'y' => 10],
                ['x' => 10, 'y' => 5],
            ],
            'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
        ],
    ],
]) ?>
```

## 🔧 Configuration Options

- `elementId` (string): The unique ID of the canvas element for rendering the chart.
- `type` (string): Type of chart (`line`, `bar`, `horizontalBar`, `pie`, `doughnut`, `radar`, `polarArea`, `scatter`).
- `labels` (array): Labels for the X-axis or data points.
- `datasets` (array): Data series to plot. Each dataset can contain keys like `label`, `data`, `fill`, `backgroundColor`, etc.

## 💡 Notes

- This widget is a wrapper for [Chart.js](https://www.chartjs.org/).
- You can customize charts further by extending or modifying widget options.

---

Made with ❤️ for Yii2 developers.