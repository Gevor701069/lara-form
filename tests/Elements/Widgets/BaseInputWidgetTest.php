<?php

namespace Tests\LaraForm\Elements\Widgets;

use LaraForm\Elements\Widgets\BaseInputWidget;
use LaraForm\Stores\ErrorStore;
use LaraForm\Stores\OldInputStore;
use Tests\LaraForm\Elements\WidgetTest;

class BaseInputWidgetTest extends WidgetTest
{
    /**
     * @var
     */
    protected $baseInputWidget;

    /**
     * @throws \ReflectionException
     */
    public function setUp()
    {
        parent::setUp();
        if (empty($this->baseInputWidget)) {
            $this->baseInputWidget = $this->newBaseInputWidget();
        };

        $this->setProtectedAttributeOf($this->baseInputWidget, 'config', config('lara_form'));
    }

    /**
     *
     */
    public function testRender()
    {
        $baseInputWidget = $this->newBaseInputWidget(['checkAttributes', 'formatInputField']);
        $this->methodWillReturnTrue('formatInputField', $baseInputWidget);
        $returned = $baseInputWidget->render();
        $this->assertTrue($returned);

    }

    /**
     * @throws \ReflectionException
     */
    public function testFormatInputField()
    {
        $mockedMethods = [
            'getTemplate',
            'generalCheckAttributes',
            'setHtmlAttributes',
            'formatAttributes',
            'formatTemplate',
            'getHtmlAttributes',
            'completeTemplate'
        ];
        $baseInputWidget = $this->newBaseInputWidget($mockedMethods);
        $this->methodWillReturnTrue('completeTemplate', $baseInputWidget);
        $returned = $this->getProtectedMethod($baseInputWidget, 'formatInputField', ['name', [], true]);
        $this->assertTrue($returned);
    }

    /**
     * @throws \ReflectionException
     */
    public function testFormatInputFieldWhenFalseCTemplate()
    {
        $mockedMethods = [
            'getTemplate',
            'generalCheckAttributes',
            'setHtmlAttributes',
            'formatAttributes',
            'formatTemplate',
            'getHtmlAttributes',
            'completeTemplate'
        ];
        $baseInputWidget = $this->newBaseInputWidget($mockedMethods);
        $this->methodWillReturnTrue('completeTemplate', $baseInputWidget);
        $this->methodWillReturn('input','getTemplate', $baseInputWidget);
        $this->methodWillReturnArgument(0, 'formatTemplate', $baseInputWidget);
        $returned = $this->getProtectedMethod($baseInputWidget, 'formatInputField', ['name', []]);
        $html = $this->getProtectedAttributeOf($baseInputWidget, 'html');
        $this->assertEquals('input', $html);
        $this->assertTrue($returned);
    }

    /**
     * @throws \ReflectionException
     */
    public function testFormatNestingLabelWhenAnonymous()
    {
        $mockedMethods = [
            'formatInputField',
            'setOtherHtmlAttributes',
            'getTemplate',
            'formatAttributes',
            'formatTemplate',
            'completeTemplate'
        ];
        $data = [
            'template',
            [
                'label_text' => false,
                'type' => 'checkbox'
            ]
        ];
        $templateAttrPattern = [
            'hidden' => '',
            'content' => '',
            'text' => '',
            'icon' => '',
            'attrs' => ''
        ];
        $baseInputWidget = $this->newBaseInputWidget($mockedMethods);
        $this->methodWillReturnTrue('completeTemplate', $baseInputWidget);
        $this->methodWillReturn('', 'formatAttributes', $baseInputWidget);
        $this->methodWillReturnArgument(1, 'formatTemplate', $baseInputWidget);
        $returned = $this->getProtectedMethod($baseInputWidget, 'formatNestingLabel', $data);
        $templateAttr = $this->getProtectedAttributeOf($baseInputWidget, 'html');
        $this->assertEquals($templateAttrPattern, $templateAttr);
        $this->assertTrue($returned);
    }

    /**
     * @throws \PHPUnit_Framework_Constraint
     * @throws \ReflectionException
     */
    public function testGeneralCheckAttributesWhenCTemplateTrue()
    {
        $mockedMethods = [
            'setHtmlAttributes',
            'getHtmlAttributes',
            'getValue',
            'generateId',
            'generateLabel',
            'generatePlaceholder',
            'generateClass',
            'assignOtherhtmlAtrributes',
            'parentCheckAttributes'
        ];
        $attr = ['value' => 'defaultValue'];
        $baseInputWidget = $this->newBaseInputWidget($mockedMethods);
        $this->setProtectedAttributeOf($baseInputWidget, 'config', config('lara_form'));
        $this->methodWillReturnTrue('parentCheckAttributes', $baseInputWidget);
        $baseInputWidget->expects($this->any())->method('getHtmlAttributes')->willReturn('text');
        $baseInputWidget->expects($this->any())->method('getValue')->willReturn(['value' => 'default_value']);
        $this->getProtectedMethod($baseInputWidget, 'generalCheckAttributes', [&$attr,true]);
    }


    /**
     * @param null $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function newBaseInputWidget($methods = null)
    {
        return $this->newInstance(
            BaseInputWidget::class,
            [app(ErrorStore::class), app(OldInputStore::class)],
            $methods);
    }
}
