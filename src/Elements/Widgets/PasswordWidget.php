<?php

namespace LaraForm\Elements\Widgets;

/**
 * Processes and creates input tag for password type
 *
 * Class PasswordWidget
 * @package LaraForm\Elements\Widgets
 */
class PasswordWidget extends BaseInputWidget
{
    /**
     * @return string
     */
    public function render()
    {
        return $this->parentRender();
    }


    /**
     * @param $attr
     */
    public function checkAttributes(&$attr)
    {
        $attr['type'] = 'password';
        $this->setOtherHtmlAttributes('type', 'password');
        $this->parentCheckAttributes($attr);
    }
}