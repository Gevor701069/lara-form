<?php

namespace LaraForm\Elements;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

class Widget implements WidgetInterface
{
    /**
     * @var string
     */
    public $html = '';

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var
     */
    public $name;

    /**
     * @var array
     */
    public $routes = [];

    /**
     * @var array
     */
    public $_requestMethods = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * @var mixed
     */
    public $config;

    /**
     * @var
     */
    public $attributes;

    /**
     * @var bool
     */
    public $containerTemplate = false;

    /**
     * @var string
     */
    public $hidden = '';
    /**
     * Widget constructor.
     */
    public function __construct()
    {
        $this->config = config('lara_form');
    }

    /**
     * @param $option
     */
    public function render($option)
    {

    }

    /**
     * @param $attr
     */
    public function inspectionAttributes(&$attr)
    {

    }

    /**
     * @param $template
     * @param $attributes
     * @return string
     */
    public function formatTemplate($template, $attributes)
    {
        if (empty($attributes)) {
            return $template;
        }

        $from = [];
        $to = [];
        foreach ($attributes as $index => $attribute) {
            $from[] = '{{' . $index . '}}';
            $to[] = $attribute;
        }
        return str_ireplace($from, $to, $template);
    }

    /**
     * @param $attributes
     * @return string
     */
    public function formatAttributes($attributes)
    {
        $attributes = array_filter($attributes, function ($value) {
            if (!empty($value) && $value !== '' && $value !== false) {
                return $value;
            }
        });

        $attr = '';
        foreach ($attributes as $index => $attribute) {
            if (is_string((string)$index)) {
                $attr .= $index . '="' . $attribute . '"';
            } else {
                $attr .= $attribute . ' ';
            }

        }

        return $attr;
    }

    /**
     * @return string
     */
    public function completeTemplate()
    {
        $containerAttributes = [
            'required' => '',
            'type' => '',
            'text' => $this->label,
            'label' => $this->label,
            'attrs' => '',
            'hidden' => $this->hidden,
            'containerAttrs' => '',
            'content' => $this->html
        ];
        $container = $this->containerTemplate ? $this->containerTemplate : $this->config['templates']['inputContainer'];
        return $this->formatTemplate($container,$containerAttributes);
    }
    /**
     * @param array $options
     * @return array|mixed|string
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function action(&$options = [])
    {
        if (!empty($options['route'])) {
            $route = $options['route'];
            unset($options['route']);
            if (!is_array($route)) {
                $route = [$route];
            }
            return route(...$route);
        }

        if (!empty($options['url'])) {
            $url = $options['url'];
            unset($options['url']);
            return $url;
        }

        if (isset($options['action'])) {
            $action = $options['action'];
            if (!is_array($action)) {
                $action = [$action];
            }
        } else {
            return request()->url();
        }

        //if action is url
        if (filter_var($action, FILTER_VALIDATE_URL)) {
            return $action;
        }

        $allRoutes = $this->getRoutes();
        $methodName = array_shift($action);

        if (!strpos('@', $methodName)) {
            $curr = $this->getCurrentRoute();
            $controller = $this->getClassName(get_class($curr->getController()));
            $methodName = $controller . '@' . $methodName;
        }

        $routeName = array_search($methodName, $allRoutes);

        if (empty($routeName)) {
            abort(405, '[' . $methodName . '] method not allowed!');
        }

        return route($routeName, $action);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getId($name)
    {
        return str_ireplace(' ', '', ucwords(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name)));
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        if (empty($this->routes)) {
            collect(Route::getRoutes())->map(function ($route) {
                $this->routes[$route->getName()] = $this->getClassName($route->getActionName());
            });
        }

        return $this->routes;
    }

    /**
     * @return mixed
     */
    public function getCurrentRoute()
    {
        return Route::getCurrentRoute();
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getClassName($name)
    {
        return array_last(explode('\\', $name));
    }

    /**
     * @param $method
     * @param $arrgs
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \LogicException
     */
    public function createObject($method, $arrgs)
    {
        //TODO optimization create objects system
        $modelName = ucfirst($method);
        $classNamspace = 'LaraForm\Elements\Components\\' . $modelName . 'Widget';
        app()->singleton($modelName . 'Widget',function ()use($classNamspace){
            return new $classNamspace();
        });
        return app($modelName . 'Widget')->render(...$arrgs);
    }

    /*
     *
     */
    public function setLabel($option)
    {
        $template = $this->config['templates']['label'];
        $name = array_shift($option);
        $attr = !empty($option[0]) ? $option[0] : [];

        if (!isset($attr['for'])) {
            $attr['for'] = $name;
        }

        $rep = [
            'attrs' => $this->formatAttributes($attr),
            'text' => $name
        ];

        return $this->formatTemplate($template, $rep);
    }

    /**
     * @param $inputName
     * @param $option
     * @return string
     */
    public function renderLabel($inputName, $option)
    {
        $label = '';
        $for = '';
        if (isset($option['label'])) {
            if (is_string((string)$option['label'])) {
                $for = $option['label'];
            }
            if ($option['label'] == false) {
                return $label;
            }
        } else {
            $for = isset($option['id']) ? $option['id'] : $inputName;
        }

        $labelName = $this->getLabelName($inputName);
        $this->label = $this->setLabel([$labelName, ['for' => $for]]);
        return $this->label;
    }

    /**
     * @param $name
     * @return string
     */
    public function getLabelName($name)
    {
        return ucwords(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $name));
    }

}
