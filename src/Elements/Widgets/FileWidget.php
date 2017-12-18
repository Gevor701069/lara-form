<?php

namespace LaraForm\Elements\Widgets;

class FileWidget extends BaseInputWidget
{
    /**
     * @var
     */
    private $fileTemplate;

    /**
     * @return string
     */
    public function render()
    {
        $this->checkAttributes($this->attr);
        $this->currentTemplate = $this->getTemplate('fileContainer');
        if ($this->name === false) {
            $name = '';
        } elseif (!empty($this->name)) {
            $name = $this->name;
        } else {
            $name = $this->config['text']['submit_name'] ? $this->config['text']['submit_name'] : '';
        }
        return $this->formatInputField($name, $this->attr, $this->fileTemplate);
    }

    /**
     * @param $attr
     */
    public function checkAttributes(&$attr)
    {
        $btn = $this->config['css']['class']['submitClass'];
        $btnColor = $this->config['css']['class']['submitColor'];
        $default = $btn.' '.$btnColor;

        if (isset($attr['btn'])) {

            if ($attr['btn'] === true) {
                $attr['btn'] = $btnColor;
            }

            $this->htmlClass[] = $btn . '-' . $attr['btn'];
            unset($attr['btn']);
        }

        if (isset($attr['type'])) {
            unset($attr['type']);
        }

        if (isset($attr['value'])) {
            unset($attr['value']);
        }

        if (isset($attr['multiple'])) {
            $this->fileTemplate = $this->config['templates']['fileMultiple'];
            unset($attr['multiple']);
        } else {
            $this->fileTemplate = $this->config['templates']['file'];
        }

        if (isset($attr['accept'])) {
            if (is_array($attr['accept'])) {
                $attr['accept'] = implode(', ', $attr['accept']);
            }
        }
        $this->generateClass($attr,$default,false);
        parent::checkAttributes($attr);
    }
}