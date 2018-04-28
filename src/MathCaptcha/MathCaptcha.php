<?php
/**********************************************\
* Copyright (c) 2013 Manolis Agkopian          *
* See the file LICENCE for copying permission. *
\**********************************************/

namespace MathCaptcha;

class MathCaptcha {
	private $addNum1;
	private $addNum2;
	private $answer = null;
	private $captchaImg = null;
	private $captchaID = 0;
	
	public function __construct( $captchaID = 0 ) {
		
		$this->captchaID = 'math_captcha_' . $captchaID;
		
		// Set the captcha result from last generated captcha and unset it from the session
		if ( isset($_SESSION[$this->captchaID]) ) {
			$this->answer = $_SESSION[$this->captchaID];
			unset($_SESSION[$this->captchaID]);
		}
		
	}
	
	public function generate () {
		
		$this->addNum1 = rand(0, 10) * rand(1, 3);
		$this->addNum2 = rand(0, 10) * rand(1, 3);
		
		// Set the captcha result for current captcha and set it to the session for later check
		$_SESSION[$this->captchaID] = $this->answer = $this->addNum1 + $this->addNum2;

		// Create a canvas
		if ( ($this->captchaImg = @imagecreatetruecolor(99, 19)) === false ) {
			throw new MathCaptchaException('Creation of true color image failed');
		}
		
		// Allocate black and white colors
		$color_black = imagecolorallocate($this->captchaImg, 0, 0, 0);
		$color_white = imagecolorallocate($this->captchaImg, 255, 255, 255);
		
		// Make the background of the image white
		imagefilledrectangle($this->captchaImg, 0, 0, 99, 19, $color_white);
		
		// Draw the math question on the image using black color
		imagestring($this->captchaImg, 10, 2, 2,  $this->addNum1 . ' + ' . $this->addNum2 . ' = ', $color_black);
		
	}
	
	public function output () {
		
		if ( $this->captchaImg === null ) {
			throw new MathCaptchaException('Captcha image has not been generated');
		}
		
		header('Content-Disposition: Attachment;filename=captcha.png');
		header('Content-Type: image/png');
		
		imagepng($this->captchaImg);
		imagedestroy($this->captchaImg);
	}
	

	/**
	 * Returns image as plain or base64 encoded string
	 *
	 * @param boolean $base64_encode
	 * @return string
	 */
	public function outputString($base64_encode = false) {
		
		ob_start();
		imagepng($this->captchaImg);
		$captcha = ob_get_contents();
		ob_end_clean();

		return ($base64_encode ? base64_encode($captcha) : $captcha);

	}
	
	public function check ( $answer ) {
		
		// Check if math captcha has been generated
		if ( $this->answer === null ) {
			return false;
		}

		// Validate captcha
		if ( $this->answer === (int) trim($answer) ) {
			return true;
		}
		else {
			return false;
		}
		
	}
	
}