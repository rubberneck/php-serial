<?php
/**
 * LRS_T74
 * Specifically developed for the LRS T74C232 interface
 * Provides an abstraction for the interface
 * Use this as an implements guide for another RS232 interface
 * Only implementing commands for basic settings and AlphaNumeric pager for now
 * TODO : Fix support for switching PLL frequencies and settings to interact with jTech pagers.
 * TODO : Retry functionality - test multiple concurrent connections
 * Minimal logging at this level
 * Initially developed for uWink by Rizwan Kassim <rizwank@uwink.com>
 * Released into GPL by uWink Inc.
 */
class LRS_T74 {

	private $serial;
	//private $serialport = "/dev/cu.usbserial";
	private $serialport = "/dev/tty.serial";
	// /dev/cu.usbserial for Prolific RS232->USB interface
	// /dev/tty.serial for XServe's built in RS232 interface
	private $serialbaud = 9600;
	private $log;
	private $sleep_count = 2;


	public function __construct () {
		/** Method __construct
		 * Initializes the serial port, opens the serial port
		 * and sets the baud rate
		 */
		// TODO: Error checking/exception handling
		// TODO throw on error on fail
		// TODO : Retry on failed open?
			global $log;
		$this->log = $log;

		$this->serial = new SerialDriver;
		$this->serial->deviceSet($this->serialport);
		$this->serial->confBaudRate($this->serialbaud);
		$this->log->notify("Opening serial port ".$this->serialport);
		$this->serial->deviceOpen();
		$this->log->notify("Successfully opened serial port");
	}

	public function __destruct () {
		/** Method __destruct
		 * Closes the serial port cleanly
		 * Initializes the serial port, opens the serial port
		 * and sets the baud rate
		 */
		// TODO: Error checking/exception handling
		// TODO throw on error on fail
		$this->log->notify("Closing serial driver");
		$this->serial->deviceClose();
	}

	private function send($msg){
		/** Method send
		 * Abstracts out EOL and serial interface
		 * Clean insertion point for error handling
		 * @param string $msg The message to be sent without an EOL
		 */
		$msg .= "\n";
		$this->serial->sendMessage($msg);
	}

	public function reset() {
		/** Method reset
		 * Resets the LRS T74C232 device.
		 */
		$this->send("RESET");
	}
	// TODO : try catch all sendMessages? or leave to caller to try/catch

	public function setPLL($pllmode){
		/** Method setPLL
		 * Sets the PLL frequencies for the transmitter
		 * PLL,37420, 467.7500 - Standard for Alphanum Pagers
		 * PLL,37422, 467.7750 - Jtech pager frequency
		 * @param integer $pllmode The 5 digit PLL Code (37420 or 37422)
		 */
		$this->send("SF,".$pllmode);
	}

	public function setEcho($mode=1){
		/** Method setEcho
		 * Turns on the screen echo mode, great for debugging
		 * @param integer $mode 0 for off, 1 for on. 1 is default
		 */
		$this->send("ECHO".$mode);
	}

	public function setTime(){
		/** Method setTime
		 * Sets the correct time on the interface
		 */
		$this->send("SETT,".date("H,i"));
	}

	// TODO implement STAT, need reliable reading from device
	// TODO implement reading from serial device
	public function pageAlpha($pager_id,$message){
		/** Method pageAlpha
		 * Pages an LRS Alphanumeric Pager
		 * 36 characters maximum for large pager view
		 * 76 characters maximum for tiny pager view (not recommended!)
		 * @param integer $pager_id The pager number to page
		 * @param string $message The text to be paged
		 */
#		print "Page Alpha got $pager_id and $message";
		$this->send("FPG,".$pager_id.",0,3!A".$message);
		sleep($this->sleep_count);
		
	}
}

?>
