<?php

    class ErrorController extends \Simpletools\Mvc\Controller
    {
        public function errorAction()
				{
        	$this->_view->msg = @$_GET['msg']?:$this->getParam('msg');
					$this->enableView();
					\Simpletools\Page\Layout::getInstance()->setLayout(APPLICATION_DIR.'/layouts/default.phtml')->enable()->render();

        }
    }

?>
