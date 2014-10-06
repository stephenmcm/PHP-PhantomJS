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
	 * @var String currently just allow forced execution
	 * Probably should be removed
	 **/
	private $debug = null;


	/**
	 *
	 * @param string Path to phantomjs binary
	 * @param bool Debug mode
	 * @return void
	 * @throws \RuntimeException If bin not found
	 */
	public function __construct($bin = null, $debug = null) {
		if($bin !== null) $this->bin = $bin;
		if($debug !== null) $this->debug = true;

		if(!file_exists($this->bin)){
		    throw new \RuntimeException('PhantomJS Executable not found. Looked in: '.$this->bin);
		}
		
		if(!is_executable($this->bin)){
		    throw new \RuntimeException('PhantomJS Executable not executable. The web server (usually www-data) needs'
										. ' execute premissions for the PhantomJS binary.');
		}

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
	 * @param string Script file
	 * @param string Arg, ...
	 * @return array From exec command
	 **/
	public function execute($script) {

		// Escape
		$args = func_get_args();
		$cmd = escapeshellcmd("{$this->bin} " . implode(' ', $args));
		if($this->debug) $cmd .= ' 2>&1';

		// Execute
		exec($cmd, $result);

		$result[] = 'Executed Command:';
		$result[] = $cmd;
		return $result;

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