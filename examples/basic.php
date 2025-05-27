<?php

require_once __DIR__ . '/../vendor/autoload.php';

use IctplDd\ChartWidget\ChartWidget;

$chart = new ChartWidget();
$chart->labels = ['Jan', 'Feb', 'Mar'];
$chart->datasets = [
    ['label' => 'Sales', 'data' => [120, 190, 300]]
];
$chart->type = 'bar';

echo $chart->render();
