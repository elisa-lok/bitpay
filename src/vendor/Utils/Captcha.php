<?php

namespace BITPAY\Utils;

class Captcha {
	/**
	 * Storage for configuration settings
	 * @var array
	 */
	protected $config = [];
	protected $image;

	public function __construct(array $config = []) {
		// Default configuration options
		$this->config = [
			// width of the image in pixels
			'width'   => 120,
			// height of the image in pixels
			'height'  => 40,
			// number of chars
			'length'  => 4,
			// chars that will be used on code generation
			'charset' => 'abcdefhjkmnprstuvwxyz23456789',
			// absolute path to the font
			'font'    => __DIR__ . '/fonts/captcha.ttf',
			// you can pass your own code or set to false for random
			'code'    => FALSE,
			// quality
			'quality' => 15,
		];
		// Override default options
		foreach ($this->config as $name => $def) {
			if (isset($config[$name])) {
				$this->config[$name] = $config[$name];
			}
		}
		if (empty($this->config['code'])) {
			$this->config['code'] = $this->genCode();
		}
	}

	/**
	 * Generates a code.
	 * @return string
	 */
	protected function genCode() {
		$code    = '';
		$charset = $this->config['charset'];
		$cnt     = mb_strlen($charset, 'UTF-8');
		for ($i = 0;$i < $this->config['length'];$i++) {
			$code .= mb_substr($charset, mt_rand(0, $cnt - 1), 1, 'UTF-8');
		}
		return $code;
	}

	/**
	 * Returns security code.
	 * @return string
	 */
	public function getCode() {
		return $this->config['code'];
	}

	/**
	 * Sets custom security code
	 * @param string $code
	 */
	public function setCode($code) {
		$this->config['code'] = $code;
	}

	public function build() {
		$image = imagecreatetruecolor($this->config['width'], $this->config['height']);
		//背景颜色
		//$bg = imagecolorallocate($image, mt_rand(225, 255), mt_rand(245, 255), mt_rand(245, 255));
		$bg = imagecolorallocate($image, 255, 255, 255);
		imagefill($image, 0, 0, $bg);
		$this->writeCode($image);
		$this->image = $image;

		return $this;
	}

	/**
	 * Draws a captcha code on the image canvas.
	 * @param  resource $image
	 * @return void
	 */
	protected function writeCode($image) {
		$code   = $this->config['code'];
		$width  = $this->config['width'];
		$height = $this->config['height'];
		$font   = $this->config['font'];
		$len    = mb_strlen($code, 'UTF-8');

		$size       = $width / $len - mt_rand(1, 3);
		$box        = imagettfbbox($size, 0, $font, $code);
		$textWidth  = $box[2] - $box[0];
		$textHeight = $box[1] - $box[7];
		$x          = ($width - $textWidth) / 2;
		$y          = ($height - $textHeight) / 2 + $size;

		for ($i = 0;$i < $len;$i++) {
			$char   = mb_substr($code, $i, 1, 'UTF-8');
			$box    = imagettfbbox($size, 0, $font, $char);
			$w      = $box[2] - $box[0];
			$angle  = mt_rand(-10, 10);
			$offset = mt_rand(-2, 2);
			$color  = imagecolorallocate($image, mt_rand(0, 125), mt_rand(0, 125), mt_rand(0, 125));
			imagettftext($image, $size, $angle, $x, $y + $offset, $color, $font, $char);
			$x += $w;
		}

		//4.3 设置背景干扰元素
		for ($i = 0;$i < 200;$i++) {
			//$color = imagecolorallocate($image, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
			//imagestring($image, mt_rand(1, 3), mt_rand(0, $width), mt_rand(0, $height), '*', $color);
			$color = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
			imagesetpixel($image, mt_rand(1, $width), mt_rand(1, $height), $color);
		}

		//4.4 设置干扰线
		for ($i = 0;$i < 4;$i++) {
			$color = imagecolorallocate($image, mt_rand(50, 200), mt_rand(50, 200), mt_rand(50, 200));
			imagesetthickness($image, rand(1, 3));
			imageline($image, mt_rand(1, $width), mt_rand(1, $height), mt_rand(1, $width), mt_rand(1, $height), $color);
		}
	}

	public function output() {
		//防止干扰输出,把之前的缓存全部清除,包括报错信息
		ob_end_clean();
		header('Content-type: image/jpg');
		imagejpeg($this->image, NULL, $this->config['quality']);
		imagedestroy($this->image);
		die;
	}

	/**
	 * Returns base64 encoded CAPTCHA image
	 */
	public function base64() {
		return 'data:image/jpeg;base64,' . base64_encode($this->get());
	}

	public function get() {
		ob_start();
		imagejpeg($this->image, NULL, $this->config['quality']);
		return ob_get_clean();
	}
}