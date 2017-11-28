<?php

namespace LaraForm\Elements\Components;

use LaraForm\Elements\Widget;

class LabelWidget extends Widget
{
    /**
     * @param $option
     * @return string
     */
    public function render($option)
    {
        return $this->setLable($option);
    }
}