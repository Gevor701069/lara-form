<?php
namespace LaraForm;

use AdamWathan\BootForms\Facades\BootForm;
use Illuminate\Support\Facades\Config;
use LaraForm\Elements\Components\CheckBox;
use LaraForm\Elements\Components\Inputs\Hidden;
use LaraForm\Elements\Components\Inputs\Input;
use LaraForm\Elements\Components\Inputs\Password;
use LaraForm\Elements\Components\Inputs\Submit;
use LaraForm\Elements\Components\Label;
use LaraForm\Elements\Components\Select;
use LaraForm\Elements\Components\Textarea;

class FormBuilder
{
    /**
     * @var Password
     */
    protected $password;

    /**
     * @var CheckBox
     */
    protected $checkBox;

    /**
     * @var Textarea
     */
    protected $textarea;

    /**
     * @var Submit
     */
    protected $submit;

    /**
     * @var Hidden
     */
    protected $hidden;

    /**
     * @var Select
     */
    protected $select;

    /**
     * @var Label
     */
    protected $label;

    /**
     * @var Input
     */
    protected $input;

    /**
     * @var string
     */
    protected $laraFormToken;

    /**
     * @var FormProtection
     */
    protected $formProtection;

    /**
     * @var array
     */
    protected $hiddenFields;

    /**
     * FormBuilder constructor.
     * @param FormProtection $formProtection
     * @param Password $password
     * @param Submit $submit
     * @param Hidden $hidden
     * @param Input $input
     * @param CheckBox $checkBox
     * @param Textarea $textarea
     * @param Select $select
     * @param Label $label
     */
    public function __construct(
        FormProtection $formProtection,
        Password $password,
        Submit $submit,
        Hidden $hidden,
        Input $input,
        CheckBox $checkBox,
        Textarea $textarea,
        Select $select,
        Label $label
    )
    {
        $this->formProtection = $formProtection;
        $this->password = $password;
        $this->checkBox = $checkBox;
        $this->textarea = $textarea;
        $this->submit = $submit;
        $this->hidden = $hidden;
        $this->select = $select;
        $this->label = $label;
        $this->input = $input;
    }

    /**
     * @param $data
     * @return bool
     */
    public function validate($data)
    {
        return $this->formProtection->validate($data);
    }

    /**
     * @param null $model
     * @param array $options
     * @return string
     */
    public function create($model = null, $options = [])
    {
        $form = BootForm::open();
        $token = md5(str_random(80));

        $this->formProtection->setToken($token);

        $unlockFields = $this->getGeneralUnlockFieldsBy($options);
        $unlockFields[] = '_token';

        $this->formProtection->setUnlockFields($unlockFields);

        if (!empty($model)) {
             BootForm::bind($model);
        }

        $method = $this->getMethodBy($model, $options);
        if ($method) {
            $form->{$method}();
            $this->formProtection->addField('_method', $options, strtoupper($method));
        }

        if (isset($options['action'])) {
            $form->action($options['action']);
            unset($options['action']);
        }

        if (isset($options['file']) && $options['file']) {
            $options['enctype'] = 'multipart/form-data';
            unset($options['file']);
        }

        foreach ($options as $k => $val) {
            $form->attribute($k, $val);
        }

        $hidden = $this->hidden(Config::get('lara_form.label.form_protection', 'laraform_token'), $token);
        return $form . $hidden;
    }

    /**
     * @param $model
     * @param $options
     * @param bool $unSet
     * @return null|string
     */
    private function getMethodBy($model, &$options, $unSet = true)
    {
        $method = null;
        if (isset($options['method'])) {
            if (in_array($options['method'], ['get', 'post', 'put', 'patch', 'delete'])) {
                $method = $options['method'];
            }
            if ($unSet) {
                unset($options['method']);
            }
        } elseif (!empty($model)) {
            $method = 'put';
        }

        return $method;
    }

    /**
     * @param $options
     * @return array|string
     * @throws \Exception
     */
    private function getGeneralUnlockFieldsBy(&$options)
    {
        $unlockFields = [];
        if (!empty($options['_unlockFields'])) {
            $unlockFields = $this->formProtection->processUnlockFields($options['_unlockFields']); // TODO use 
            unset($options['_unlockFields']);
        }
        return $unlockFields;
    }

    /**
     * @return mixed
     */
    public function end()
    {
        $this->formProtection->confirm();
        return BootForm::close();
    }

    /**
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function input($name, array $options = [])
    {
        $this->formProtection->addField($name, $options);
        return $this->input->toHtml($name, $options);
    }

    /**
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function password($name, array $options = [])
    {
        $this->formProtection->addField($name, $options);
        $options['type'] = 'password';
        return $this->input->toHtml($name, $options);
    }

    /**
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function select($name, $options = [])
    {
        $hidden = '';
        if (isset($options['empty']) && $options['empty'] === false) {
            $hidden = $this->hidden->toHtml(substr($name, 0, -2));
        }
        $this->formProtection->addField($name); // TODO add options
//        TODO for select optional check values
//        $optionValues = $this->select->getOptionValues($options, false);
//        $this->formProtection->addField($name, $options,  array_keys($optionValues));

        return $hidden.$this->select->toHtml($name, $options);
    }

    /**
     * @param string $name
     * @param array $options
     * @return mixed
     */
    public function submit($name = '', $options = [])
    {
        return $this->submit->toHtml($name, $options);
    }

    /**
     * @param $name
     * @param array $options
     * @return string
     */
    public function checkbox($name, array $options = [])
    {
        $this->formProtection->addField($name, $options);
        $checkbox = $this->checkBox->toHtml($name, $options);

        if (isset($options['hidden']) && $options['hidden'] === false) {
            $hidden = '';
            unset($options['hidden']);
        } else {
            $hidden = ends_with($name, '[]') ? '' : $this->hidden->toHtml($name, 0);
        }

        foreach ($options as $k => $v) {
            if ($k == 'class') {
                $checkbox->class($v);
            } else {
                $checkbox->attribute($k, $v);
            }
        }

        return $hidden.$checkbox;
    }

    /**
     * @param $name
     * @param array $options
     * @return string
     */
    public function hidden($name, $options = [])
    {
        $this->formProtection->addField($name, $options, $this->hidden->getValue($options));
        return $this->hidden->toHtml($name, $options);
    }

    /**
     * @param $name
     * @param array $options
     * @return string
     */
    public function textarea($name, $options = [])
    {
        $this->formProtection->addField($name, $options);
        return $this->textarea->toHtml($name, $options);
    }
}
