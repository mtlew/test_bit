<?php
/**
 * Created by PhpStorm.
 * User: mtlew
 * Date: 01.08.17
 * Time: 10:53
 */
namespace Bit\Base;


abstract class Controller
{

    protected $user;

    protected $params;
    protected $layout;


    public function __construct($params)
    {
        $this->params = $params;
    }


    public function runAction($actionName)
    {
        $actionName = 'action' . ucfirst($actionName);

        if (method_exists($this, $actionName)) {
            return $this->$actionName();
        }
        return $this->actionIndex(); // TODO 404
    }


    public function redirect($uri)
    {
        header('Location: ' . $uri);
        die;
    }


    protected function renderPartial($fileName, $data = null)
    {
        $this->renderFile($fileName, $data);
    }


    protected function render($fileName, $data = null)
    {
        $content = $this->renderFile($fileName, $data, true);
        $this->renderLayout($content);
    }

    protected function renderLayout($content)
    {
        $this->renderFile($this->getLayout(), ['content' => $content]);
    }


    protected function renderFile($filName, $data = null, $return = false)
    {
        if (is_array($data)) {
            extract($data, EXTR_PREFIX_SAME, 'data');
        }
        $filName = $this->getFileNameFull($filName);

        if ($return) {
            ob_start();
            ob_implicit_flush(false);
            require $filName;
            return ob_get_clean();
        }
        require $filName;
    }


    protected function getFileNameFull($fileName)
    {
        return $this->getFilePath() . $fileName . '.phtml';
    }


    protected function getFilePath()
    {
        return dirname(__FILE__) . '/../View/';
    }

    /**
     * @return mixed
     */
    protected function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param mixed $layout
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
    }
}