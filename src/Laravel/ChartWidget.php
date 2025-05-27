<?php

namespace ictpl\ChartWidget\Laravel;

use Illuminate\View\Component;
use ictpl\ChartWidget\ChartRenderer;

class ChartWidget extends Component
{
    public string $elementId;
    public string $type;
    public array $labels;
    public array $datasets;
    public array $options;
    public string $width;
    public string $height;
    public bool $darkMode;

    public function __construct(
        string $elementId = 'chart',
        string $type = 'bar',
        array $labels = [],
        array $datasets = [],
        array $options = [],
        string $width = '800px',
        string $height = '400px',
        bool $darkMode = false
    ) {
        $this->elementId = $elementId === 'chart' ? $elementId . '_' . uniqid() : $elementId;
        $this->type = $type;
        $this->labels = $labels;
        $this->datasets = $datasets;
        $this->options = $options;
        $this->width = $width;
        $this->height = $height;
        $this->darkMode = $darkMode;
    }

    public function render(): string
    {
        return ChartRenderer::render(
            $this->elementId,
            $this->type,
            $this->labels,
            $this->datasets,
            $this->options,
            $this->width,
            $this->height,
            $this->darkMode
        );
    }
}