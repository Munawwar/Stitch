<?php
	/**
	 * Minimal Template Inheritance.
	 * MIT license (http://opensource.org/licenses/MIT).
	 */

	/**
	 * Main class that compiles/joins the html blocks and outputs the final html.
	 */
	class TI {
		public static $stack = array('superblock');
		public static $page = array(); // Keeps the html
		public static $blocks = array(); //Hash map to keep block info

		//For profiling
		/**
		 * Set TI_PROFILE to true to get performance log (as apache headers).
		 * @property TI_PROFILE
		 * @public
		 */
		public static $startTime;

		public static function callback($str, $flags) {
			$block = array_pop(TI::$stack);
			if ($block == 'superblock' && ($flags & PHP_OUTPUT_HANDLER_END)) {
				/*Superblock has ended..time to spit the html*/
				//Sort blocks
				$blocks = array_slice(TI::$blocks, 0);
				usort($blocks, function($a, $b) {
					return $a['pos'] - $b['pos'];
				});

				//Insert in decsending order
				$html = '';
				$end = strlen($str);
				for ($i = count($blocks) - 1; $i >= 0; $i -= 1) {
					$html = $blocks[$i]['content'] . TI::substr($str, $blocks[$i]['pos'], $end) . $html;
					$end = $blocks[$i]['pos'];
				}
				$html = TI::substr($str, 0, $end) . $html;
				if (defined('TI_PROFILE') && TI_PROFILE) {
					TI::log('Time taken in ms = ' . (microtime(true) - TI::$startTime));
				}
				return $html;
			} else {
				TI::$blocks[$block]['content'] = $str;
				return ''; //Delete contents of buffer
			}
		}

		//Utilities
		//--------------------
		private static $dnum = 0;
		//Debug logs using apache headers
		public static function log($str) {
			header('X-mti-' . TI::$dnum . ': ' . preg_replace('#[\\r\\n]#m', '\\n' , $str));
			TI::$dnum += 1;
		}

		public static function substr($str, $start, $end) {
			return substr($str, $start, $end - $start);
		}
		//--------------------

	}
	if (defined('TI_PROFILE') && TI_PROFILE) {
		TI::$startTime = microtime(true);
	}

	/**
	 * Starts a block. Can be used within page and template.
	 * Note: Nested blocks is not supported.
	 * @param {String} name Give a name to your block
	 * @public
	 */
	function startblock($name) {
		if(!isset(TI::$blocks[$name])) { //store block info
			TI::$blocks[$name]=array(
				'name' => $name,
				'content' => null, //it's the callback's job to fill this
				'pos' => ob_get_length() //position/offset of block with respect to parent
			);
		}
		array_push(TI::$stack, $name);
		ob_start("TI::callback");
	}

	/**
	 * Closes the last open block.
	 * @public
	 */
	function endblock() {
		if (isset(TI::$stack[1])) {
			ob_end_clean();
		}
	}

	/**
	 * Defines a block. It is equivalent to a startblock/endblock call with no html between them.
	 * @param {String} name Give a name to your block
	 * @public
	 */
	function defineblock($name) {
		if(!isset(TI::$blocks[$name])) { //store block info
			TI::$blocks[$name]=array(
				'name' => $name,
				'content' => '',
				'pos' => ob_get_length() //position/offset of block with respect to parent
			);
		}
	}

	ob_implicit_flush(TRUE);
	ob_start("TI::callback");
?>
