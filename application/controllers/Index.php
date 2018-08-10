<?php
/**
 * @name IndexController
 * @author explorer
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends \Explorer\ControllerAbstract{

	/**
     * 默认动作
     * Yaf支持直接把Yaf\Request\Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/weather/index/index/index/name/explorer 的时候, 你就会发现不同
     */
	public function indexAction($name = "Stranger") {
        exit;
        $client = new Predis\Client();
        //$client->set('foo', 'bar');
        $value = $client->get('foo');
        //$client->expire('foo', 3);
        echo $value; exit;
        return $this->outputSuccess();
		//1. fetch query
		$get = $this->getRequest()->getQuery("get", "default value");

		//2. fetch model
		$model = new SampleModel();

		//3. assign
		$this->getView()->assign("content", $model->selectSample());
		$this->getView()->assign("name", $name);
		echo $this->getView()->render('index/index.phtml');

		//4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
	}
}
