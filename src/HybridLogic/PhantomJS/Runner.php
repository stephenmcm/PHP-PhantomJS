<?php

namespace HybridLogic\PhantomJS;

/**
 * PhantomJS Command Runner
 *
 * $command = new \HybridLogic\PhantomJS\Runner;
 * $result = $command->execute('\www\test.js', 'http://mysite.com', 'another-arg');
 *
 * @package PhantomJS
 **/
class Runner {


	/**
	 * @var string Path to phantomjs binary
	 **/
	private $bin = '/usr/local/bin/phantomjs';


	/**
	 * @var bool If true, all Command output is returned verbatim
	 * Defaulting this to true as it's more useful on than off
	 **/
	private $debug = true;


	/**
	 * Constructor
	 *
	 * @param string Path to phantomjs binary
	 * @param bool Debug mode
	 * @return void
	 **/
	public function __construct($bin = null, $debug = null) {
		if($bin !== null) $this->bin = $bin;
		if($debug !== null) $this->debug = true;

		//Exec enabled test
		$disabled = explode(',', ini_get('disable_functions'));
		if(in_array('exec', $disabled) && $debug !== 'test'){
			trigger_error("'exec' appears to be disabled on this server. Pass 'test' in \$debug to run anyway", E_USER_ERROR);
		}

	} // end func: __construct



	/**
	 * Execute a given JS file
	 *
	 * This method should be called with the first argument
	 * being the JS file you wish to execute. Additional
	 * PhantomJS command line arguments can be passed
	 * through as function arguments e.g.:
	 *
	 *     $command->execute('/path/to/my/script.js', 'arg1', 'arg2'[, ...])
	 *
	 * The script tries to automatically decode JSON
	 * objects if the first character returned is a left
	 * curly brace ({).
	 *
	 * If debug mode is enabled, this method will return
	 * the output of the command verbatim along with any
	 * errors printed out to the shell.
	 *
	 * @param string Script file
	 * @param string Arg, ...
	 * @return bool/array False of failure, JSON array on success
	 **/
	public function execute($script) {

		// Escape
		$args = func_get_args();
		$cmd = escapeshellcmd("{$this->bin} " . implode(' ', $args));
		if($this->debug) $cmd .= ' 2>&1';

		// Execute
		exec($cmd, $result);
		if($this->debug) {
			$result[] = 'Executed Command:';
			$result[] = $cmd;
			return $result;
		}
		if($result === null) return false;

		// Return
		if(substr($result, 0, 1) !== '{') return $result; // not JSON
		$json = json_decode($result, $as_array = true);
		if($json === null) return false;
		return $json;

	} // end func: execute

	/**
	 * Tests if phantomjs is currently running
	 * @return boolean true if phantomjs is running
	 */
	public function isRunning(){
	    try {
	        $process = 'phantomjs';
			$escaped_command = escapeshellcmd("ps -C " . escapeshellarg($process)) . " | awk '{ print $1}' ";
			echo $escaped_command;
			exec($escaped_command, $result);
	        if( count($result) > 2){
	            return true;
	        }
	    }catch(Exception $e){} //Why?

	    return false;
	}

} // end class: Runner