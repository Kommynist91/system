<?php

/**
 * A base class handler for URL-based actions. All ActionHandlers must 
 * extend this class for the Controller to call their actions.
 *  
 * @package Habari
 */  
class ActionHandler
{
	/**
	 * Name of action to trigger
	 * 
	 * @var string
	 * @see act()
	 */
	public $action = '';
	
	/**
	 * Internal array of handler variables (state info)
	 * 
	 * @var array
	 */
	public $handler_vars = array();

	/**
	 * All handlers must implement act() to conform to handler API.
	 * This is the default implementation of act(), which attempts
	 * to call a class member method of $this->act_$action().  Any
	 * subclass is welcome to override this default implementation.
	 *
	 * @param string $action the action that was in the URL rule
	 */	 	 	 	 
	public function act($action) {
		$this->action = $action;
		
		$action_method = 'act_' . $action;
		$before_action_method = 'before_' . $action_method;
		$after_action_method = 'after_' . $action_method;
		
		if (method_exists($this, $action_method)) {
			if (method_exists($this, $before_action_method)) {
				$this->$before_action_method();
			}
			/**
			 * Plugin action to allow plugins to execute before a certain
			 * action is triggered
			 * 
			 * @see ActionHandler::$action
			 * @action before_act_{$action}
			 */
			Plugins::act( $before_action_method, $this );
			
			$this->$action_method();
			
			/**
			 * Plugin action to allow plugins to execute after a certain
			 * action is triggered
			 * 
			 * @see ActionHandler::$action
			 * @action before_act_{$action}
			 */
			Plugins::act( $after_action_method );
			if (method_exists($this, $after_action_method)) {
				$this->$after_action_method();
			}
		}
	}

	/**
	 * Helper method to convert calls to $handler->my_action()
	 * to $handler->act('my_action');
	 * 
	 * @param string $function function name
	 * @param array $args function arguments
	 */
	public function __call($function, $args) {
		$this->handler_vars = array_merge($this->handler_vars, $args);
		return $this->act($function);
	}
	
	/**
	 * Helper method to allow RewriteRules to send a redirect. The method will 
	 * redirect to the build_str of the RewriteRule if matched.
	 */
	public function act_redirect()
	{
		$vars = isset($_SERVER['QUERY_STRING']) ? Utils::get_params($_SERVER['QUERY_STRING']) : array();
		Utils::redirect( URL::get(null, $vars) );
	}
}

?>
