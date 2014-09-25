<?php

class ErrorController extends Zend_Controller_Action
{

    public function errorAction() {
    	// $this->_helper->layout->disableLayout();
    	
    	// debugMessage($this->toArray());
        $errors = $this->_getParam('error_handler');
        if (!$errors) {
        	$this->view->message = 'You have reached the error page';
        	debugMessage('$this->view->message. No Error detected');
        	return;
        }
         
        $exception = $errors->exception; // debugMessage($exception);
        $vars = get_object_vars($exception );
        $error_list = createHTMLCommaListFromArray($vars); // debugMessage($error_list);
        // debugMessage(get_class($errors->exception));  debugMessage($errors->type);
       
        // exit();
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found '.$errors->exception;
                break;
            default:
                // application error
               	$this->getResponse()->setHttpResponseCode(500);
                // $this->view->message = 'Application error'.$errors->exception;
            	$this->view->message = $error_list;
            	/* $string = '<div class="divider30"></div>
			<div class="row-fluid">
				<div class="col-md-12">
					<div class="alert alert-danger">Application Runtime Error</div>
					<p class="bg-warning padding10">'.$error_list.'></p>
			    </div>
			</div> ';
            	debugMessage($string); */
                break;
        }
        
        // Log exception, if logger available
        $log = $this->getLog();
        if($log) {
            $log->crit($this->view->message, $error_list);
        }
        
        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            // $this->view->exception = $errors->exception;
            $this->view->exception = $error_list;
        }
        
        $this->view->request   = $errors->request; /**/
         
    }

    public function getLog()
    {
        return Zend_Registry::get("logger"); 
    }


}

