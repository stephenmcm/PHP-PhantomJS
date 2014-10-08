<?php

namespace HybridLogic\PhantomJS;

/**
 * PhantomJS Command Runner
 *
 * @package PhantomJS
 **/
class Runner {

	/**
	 * @var string Path to phantomjs binary
	 **/
	private $bin = '/usr/local/bin/phantomjs';

	/**
	 * Truthy/Flasy value to enable exec debug output
	 * @var Boolean
	 **/
	private $debug = null;
	
	/**
	 * Return array from exec
	 * @var array
	 */
	private $result = array();

	/**
	 * The decoded return from PhantomJS
	 * @var array
	 */
	private $phantomMsg = array();

	/**
	 *
	 * @param string Path to phantomjs binary
	 * @param bool Debug mode
	 * @return void
	 * @throws \RuntimeException If bin not found, executable or exec is not enabled
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
		if(in_array('exec', $disabled) && !$debug){
			throw new \RuntimeException("'exec' appears to be disabled on this server. Pass 'test' in \$debug to run "
										. "anyway");
		}

	}

	/**
	 * Execute PhantomJS with the passed script and arguments
	 *
	 * This method should be called with the first argument being the JS file you wish to execute. Additional PhantomJS
	 * command line arguments can be passed through as function arguments e.g.:
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
		if($this->debug) {
			$cmd .= ' 2>&1';
		}

		// Execute
		exec($cmd, $this->result);

		$this->result['executedCommand'] = $cmd;
		
		//First entry in the return array is a JSON string of PhantomJS output
		$this->phantomMsg = json_decode($this->result[0], true);

		if ($this->phantomMsg['error'] == TRUE) {
			throw new \RuntimeException('PhantomJS failed with message: ' . $this->phantomMsg['errorMessage']);
		}
		
		return $this->result;
	}

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
	    }catch(Exception $e){
			//exception suppression 
		}

	    return false;
	}


	/**
	 * Returns the JSON return message from PhantomJS converted to an Array
	 * @return Array
	 */
	public function getPhantomMsg() {
		return $this->phantomMsg;
	}

	/**
	 * The raw Array result from exec of PhantomJS
	 * @return Array
	 */
	public function getResult() {
		return $this->result;
	}
	
	public function getBin() {
		return $this->bin;
	}

	public function getDebug() {
		return $this->debug;
	}

	public function setBin($bin) {
		$this->bin = $bin;
	}

	public function setDebug($debug) {
		$this->debug = $debug;
	}


}