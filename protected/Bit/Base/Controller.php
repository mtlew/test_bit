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

    /**
     * Controller constructor.
     * @param array $params
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @param string $actionName
     * @return mixed
     */
    public function runAction($actionName)
    {
        $actionName = 'action' . ucfirst($actionName);

        if (method_exists($this, $actionName)) {
            return $this->$actionName();
        }
        return $this->actionIndex(); // TODO 404
    }

    /**
     * @param $uri string
     */
    public function redirect($uri)
    {
        header('Location: ' . $uri);
        die;
    }

    /**
     * @param string $fileName
     * @param null|array $data
     */
    protected function renderPartial($fileName, $data = null)
    {
        $this->renderFile($fileName, $data);
    }

    /**
     * @param string $fileName
     * @param null|array $data
     */
    protected function render($fileName, $data = null)
    {
        $content = $this->renderFile($fileName, $data, true);
        $this->renderLayout($content);
    }

    /**
     * @param string $content
     */
    protected function renderLayout($content)
    {
        $this->renderFile($this->getLayout(), ['content' => $content]);
    }

    /**
     * @param string $fileName
     * @param null|array $data
     * @param bool $return
     * @return string
     */
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

    /**
     * @param string $fileName
     * @return string
     */
    protected function getFileNameFull($fileName)
    {
        return $this->getFilePath() . $fileName . '.phtml';
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return dirname(__FILE__) . '/../View/';
    }

    /**
     * @return string
     */
    protected function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     */
    protected function setLayout($layout)
    {
        $this->layout = $layout;
    }
}