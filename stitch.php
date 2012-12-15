<?php
	/**
	 * Stitch - Fast template inheritance.
	 * MIT license (http://opensource.org/licenses/MIT).
	 */

	/**
	 * Main class that compiles/joins the html blocks and outputs the final html.
	 */
	class TI {
		const APPEND = 1;
		const REPLACE = 0;
		const PREPEND = -1;

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

		//Superblock callback
		public static function callback($str, $flags) {
			if ($flags & PHP_OUTPUT_HANDLER_END) {
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
			}
		}

		//Utilities
		//--------------------
		private static $dnum = 0;
		//Debug logs using apache headers
		public static function log($str) {
			header('X-ti-' . TI::$dnum . ': ' . preg_replace('#[\\r\\n]#m', '\\n' , $str));
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
	 * @param {String} name Use any unique name for the block.
	 * If a block with the same name exist, then the block content is modified (modification depends on the 'insertTo' parameter).
	 * @param {Integer} [insertTo] If 1, the content is appended to the previous block of the same name.
	 * If -1 content is prepended. Default is 0 where content is replaced.
	 * If a previous block of the same name doesn't exist, then it is created.
	 *
	 * You can use TI::APPEND,TI::PREPEND and TI::REPLACE for readability.
	 * @public
	 */
	function startblock($name, $insertTo = 0) {
		if(!isset(TI::$blocks[$name])) { //store block info
			TI::$blocks[$name]=array(
				'name' => $name,
				'content' => null, //it is endblock's job to fill this
				'pos' => ob_get_length() //position/offset of block with respect to parent
			);
		}
		array_push(TI::$stack, array(
			'name' => $name,
			'insertTo' => $insertTo
		));
		ob_start();
	}

	/**
	 * Closes the last open block.
	 * @public
	 */
	function endblock() {
		if (isset(TI::$stack[1])) {
			$temp = array_pop(TI::$stack);
			$block = $temp['name'];
			$insertTo = $temp['insertTo'];
			$content = ob_get_contents();
			if (TI::$blocks[$block]['content'] === null || $insertTo === 0) {
				TI::$blocks[$block]['content'] = $content;
			} else if ($insertTo > 0) {
				//Append content
				TI::$blocks[$block]['content'] = TI::$blocks[$block]['content'] . $content;
			} else {
				//Prepend content
				TI::$blocks[$block]['content'] = $content . TI::$blocks[$block]['content'];
			}
			ob_end_clean();
		}
	}

	/**
	 * Defines a block. This method is equivalent to a startblock-endblock call with no html between them.
	 * @param {String} name Use any unique name for the block
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
