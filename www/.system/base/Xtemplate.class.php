<?php
if (class_exists('XTemplate')){
	trigger_error("Class 'XTemplate' should not be defined twice",E_USER_NOTICE);
} else {
	class XTemplate {
		public $filecontents = '';
		public $blocks = array();
		public $parsed_blocks = array();
		public $preparsed_blocks = array();
		public $block_parse_order = array();
		public $sub_blocks = array();
		public $vars = array();
		public $filevars = array();
		public $filevar_parent = array();
		public $filecache = array();
		public $tpldir = '';
		public $files = null;
		public $filename = '';
		public $file_delim = '';
		public $filevar_delim = '';
		public $filevar_delim_nl = '';
		public $block_start_delim = '<!-- ';
		public $block_end_delim = '-->';
		public $block_start_word = 'BEGIN:';
		public $block_end_word = 'END:';
		public $tag_start_delim = '{';
		public $tag_end_delim = '}';
		public $comment_preg = '( ?#.*?)?';
		public $mainblock = 'main';
		public $output_type = 'HTML';
		public $debug = false;
		protected $_null_string = array('' => '');
		protected $_null_block = array('' => '');
		protected $_error = '';
		protected $_autoreset = true;
		protected $_ignore_missing_blocks = true;
		public function __construct($file, $tpldir = '', $files = null, $mainblock = 'main', $autosetup = true) {
			$this->restart($file, $tpldir, $files, $mainblock, $autosetup, $this->tag_start_delim, $this->tag_end_delim);
		}
		public function XTemplate ($file, $tpldir = '', $files = null, $mainblock = 'main', $autosetup = true) {
			assert('Deprecated - use PHP 5 constructor');
		}
		public function restart ($file, $tpldir = '', $files = null, $mainblock = 'main', $autosetup = true, $tag_start = '{', $tag_end = '}') {
			$this->filename = $file;
			$this->tpldir = $tpldir;
			if (defined('XTPL_DIR') && empty($this->tpldir)) { $this->tpldir = XTPL_DIR; }
			if (is_array($files)) { $this->files = $files; }
			$this->mainblock = $mainblock;
			$this->tag_start_delim = $tag_start;
			$this->tag_end_delim = $tag_end;
			$this->filecontents = '';
			$this->blocks = array();
			$this->parsed_blocks = array();
			$this->preparsed_blocks = array();
			$this->block_parse_order = array();
			$this->sub_blocks = array();
			$this->vars = array();
			$this->filevars = array();
			$this->filevar_parent = array();
			$this->filecache = array();
			if ($autosetup) { $this->setup(); }
		}

		public function setup ($add_outer = false) {
			$this->tag_start_delim = preg_quote($this->tag_start_delim);
			$this->tag_end_delim = preg_quote($this->tag_end_delim);
			$this->file_delim = "/" . $this->tag_start_delim . "FILE\s*\"([^\"]+)\"" . $this->comment_preg . $this->tag_end_delim . "/m";
			$this->filevar_delim = "/" . $this->tag_start_delim . "FILE\s*" . $this->tag_start_delim . "([A-Za-z0-9\._]+?)" . $this->comment_preg . $this->tag_end_delim . $this->comment_preg . $this->tag_end_delim . "/m";
			$this->filevar_delim_nl = "/^\s*" . $this->tag_start_delim . "FILE\s*" . $this->tag_start_delim . "([A-Za-z0-9\._]+?)" . $this->comment_preg . $this->tag_end_delim . $this->comment_preg . $this->tag_end_delim . "\s*\n/m";
			if (empty($this->filecontents)) { $this->filecontents = $this->_r_getfile($this->filename); }
			if ($add_outer) { $this->_add_outer_block(); }
			$this->blocks = $this->_maketree($this->filecontents, '');
			$this->filevar_parent = $this->_store_filevar_parents($this->blocks);
			$this->scan_globals();
		}

		public function assign ($name, $val = '', $reset_array = true) {
			if (is_array($name)) {
				foreach ($name as $k => $v) {
					$this->vars[$k] = $v;
				}
			} elseif (is_array($val)) {
	    		if ($reset_array) {
	    			$this->vars[$name] = array();
	    		}
			  foreach ($val as $k => $v) {
				  $this->vars[$name][$k] = $v;
			  }
			} else {
				$this->vars[$name] = $val;
			}
		}

		public function assign_file ($name, $val = '') {
			if (is_array($name)) {
				foreach ($name as $k => $v) {
					$this->_assign_file_sub($k, $v);
				}
			} else {
				$this->_assign_file_sub($name, $val);
			}
		}

		public function parse ($bname) {
			if (isset($this->preparsed_blocks[$bname])) {
				$copy = $this->preparsed_blocks[$bname];
			} elseif (isset($this->blocks[$bname])) {
				$copy = $this->blocks[$bname];
			} elseif ($this->_ignore_missing_blocks) {
				$this->_set_error("parse: blockname [$bname] does not exist");
				return;
			} else {
				$this->_set_error("parse: blockname [$bname] does not exist");
			}
			if (!isset($copy)) {
				die('Block: ' . $bname);
			}

			$copy = preg_replace($this->filevar_delim_nl, '', $copy);
			$var_array = array();
			preg_match_all("|" . $this->tag_start_delim . "([A-Za-z0-9\._]+?" . $this->comment_preg . ")" . $this->tag_end_delim. "|", $copy, $var_array);
			$var_array = $var_array[1];
			foreach ($var_array as $k => $v) {
				$any_comments = explode('#', $v);
				$v = rtrim($any_comments[0]);
				if (sizeof($any_comments) > 1) {
					$comments = $any_comments[1];
				} else {
					$comments = '';
				}
				$sub = explode('.', $v);
				if ($sub[0] == '_BLOCK_') {
					unset($sub[0]);
					$bname2 = implode('.', $sub);
					// trinary operator eliminates assign error in E_ALL reporting
					$var = isset($this->parsed_blocks[$bname2]) ? $this->parsed_blocks[$bname2] : null;
					$nul = (!isset($this->_null_block[$bname2])) ? $this->_null_block[''] : $this->_null_block[$bname2];

					if ($var === '') {
						if ($nul == '') {
							$copy = preg_replace("|" . $this->tag_start_delim . $v . $this->tag_end_delim . "|m", '', $copy);
						} else {
							$copy = preg_replace("|" . $this->tag_start_delim . $v . $this->tag_end_delim . "|m", "$nul", $copy);
						}
					} else {
						//$var = trim($var);
						switch (true) {
							case preg_match('/^\n/', $var) && preg_match('/\n$/', $var):
								$var = substr($var, 1, -1);
								break;

							case preg_match('/^\n/', $var):
								$var = substr($var, 1);
								break;

							case preg_match('/\n$/', $var):
								$var = substr($var, 0, -1);
								break;
						}
						$var = str_replace('\\', '\\\\', $var);
						$var = str_replace('$', '\\$', $var);
						$var = str_replace('\\|', '|', $var);
						$copy = preg_replace("|" . $this->tag_start_delim . $v . $this->tag_end_delim . "|m", "$var", $copy);
						if (preg_match('/^\n/', $copy) && preg_match('/\n$/', $copy)) {
							$copy = substr($copy, 1, -1);
						}
					}
				} else {
					$var = $this->vars;
					foreach ($sub as $v1) {
						if (!isset($var[$v1]) || (!is_array($var[$v1]) && strlen($var[$v1]) == 0)) {
							if (defined($v1)) {
								$var[$v1] = constant($v1);
							} else {
								$var[$v1] = null;
							}
						}
						$var = $var[$v1];
					}

					$nul = (!isset($this->_null_string[$v])) ? ($this->_null_string[""]) : ($this->_null_string[$v]);
					$var = (!isset($var)) ? $nul : $var;

					if ($var === '') {
						$copy = preg_replace("|" . $this->tag_start_delim . $v . "( ?#" . $comments . ")?" . $this->tag_end_delim . "|m", '', $copy);
					}

					$var = trim($var);
					$var = str_replace('\\', '\\\\', $var);
					$var = str_replace('$', '\\$', $var);
					$var = str_replace('\\|', '|', $var);
					$copy = preg_replace("|" . $this->tag_start_delim . $v . "( ?#" . $comments . ")?" . $this->tag_end_delim . "|m", "$var", $copy);
					if (preg_match('/^\n/', $copy) && preg_match('/\n$/', $copy)) {
						$copy = substr($copy, 1);
					}
				}
			}

			if (isset($this->parsed_blocks[$bname])) {
				$this->parsed_blocks[$bname] .= $copy;
			} else {
				$this->parsed_blocks[$bname] = $copy;
			}

			if ($this->_autoreset && (!empty($this->sub_blocks[$bname]))) {
				reset($this->sub_blocks[$bname]);
				foreach ($this->sub_blocks[$bname] as $k => $v) {
					$this->reset($v);
				}
			}
		}

		public function rparse ($bname) {
			if (!empty($this->sub_blocks[$bname])) {
				reset($this->sub_blocks[$bname]);
				foreach ($this->sub_blocks[$bname] as $k => $v) {
					if (!empty($v)) {
						$this->rparse($v);
					}
				}
			}
			$this->parse($bname);
		}

		public function insert_loop ($bname, $var, $value = '') {
			$this->assign($var, $value);
			$this->parse($bname);
		}

		public function array_loop ($bname, $var, &$values) {
			if (is_array($values)) {
				foreach($values as $v) {
					$this->insert_loop($bname, $var, $v);
				}
			}
		}

		public function text ($bname = '') {
			$text = '';
			if ($this->debug && $this->output_type == 'HTML') {
				$text .= '<!-- XTemplate: ' . realpath($this->filename) . " -->\n";
			}
			$bname = !empty($bname) ? $bname : $this->mainblock;
			$text .= isset($this->parsed_blocks[$bname]) ? $this->parsed_blocks[$bname] : $this->get_error();
			return $text;
		}

		public function out ($bname) {
			$out = $this->text($bname);
			echo $out;
		}

		public function out_file ($bname, $fname) {
			if (!empty($bname) && !empty($fname) && is_writeable($fname)) {
				$fp = fopen($fname, 'w');
				fwrite($fp, $this->text($bname));
				fclose($fp);
			}
		}

		public function reset ($bname) {
			$this->parsed_blocks[$bname] = '';
		}

		public function parsed ($bname) {

			return (!empty($this->parsed_blocks[$bname]));
		}

		public function set_null_string($str, $varname = '') {
			$this->_null_string[$varname] = $str;
		}

		public function SetNullString ($str, $varname = '') {
			$this->set_null_string($str, $varname);
		}

		public function set_null_block ($str, $bname = '') {
			$this->_null_block[$bname] = $str;
		}

		public function SetNullBlock ($str, $bname = '') {
			$this->set_null_block($str, $bname);
		}

		public function set_autoreset () {
			$this->_autoreset = true;
		}

		public function clear_autoreset () {
			$this->_autoreset = false;
		}

		public function scan_globals () {
			reset($GLOBALS);
			foreach ($GLOBALS as $k => $v) {
				$GLOB[$k] = $v;
			}
			$this->assign('PHP', $GLOB);
		}

		public function get_error () {
			$retval = false;
			if ($this->_error != '') {
				switch ($this->output_type) {
					case 'HTML':
					case 'html':
						$retval = '<b>[XTemplate]</b><ul>' . nl2br(str_replace('* ', '<li>', str_replace(" *\n", "</li>\n", $this->_error))) . '</ul>';
						break;
					default:
						$retval = '[XTemplate] ' . str_replace(' *\n', "\n", $this->_error);
						break;
				}
			}
			return $retval;
		}

		public function _maketree ($con, $parentblock='') {
			$blocks = array();
			$con2 = explode($this->block_start_delim, $con);
			if (!empty($parentblock)) {
				$block_names = explode('.', $parentblock);
				$level = sizeof($block_names);
			} else {
				$block_names = array();
				$level = 0;
			}
			$patt = "(" . $this->block_start_word . "|" . $this->block_end_word . ")\s*(\w+)" . $this->comment_preg . "\s*" . $this->block_end_delim . "(.*)";
			foreach($con2 as $k => $v) {
				$res = array();
				if (preg_match_all("/$patt/ims", $v, $res, PREG_SET_ORDER)) {
					$block_word	= $res[0][1];
					$block_name	= $res[0][2];
					$comment	= $res[0][3];
					$content	= $res[0][4];
					if (strtoupper($block_word) == $this->block_start_word) {
						$parent_name = implode('.', $block_names);
						$block_names[++$level] = $block_name;
						$cur_block_name=implode('.', $block_names);
						$this->block_parse_order[] = $cur_block_name;
						$blocks[$cur_block_name] = isset($blocks[$cur_block_name]) ? $blocks[$cur_block_name] . $content : $content;
						$blocks[$parent_name] .= str_replace('\\', '', $this->tag_start_delim) . '_BLOCK_.' . $cur_block_name . str_replace('\\', '', $this->tag_end_delim);
						$this->sub_blocks[$parent_name][] = $cur_block_name;
						$this->sub_blocks[$cur_block_name][] = '';
					} else if (strtoupper($block_word) == $this->block_end_word) {
						unset($block_names[$level--]);
						$parent_name = implode('.', $block_names);
						$blocks[$parent_name] .= $content;
					}
				} else {
					$tmp = implode('.', $block_names);
					if ($k) {
						$blocks[$tmp] .= $this->block_start_delim;
					}
					$blocks[$tmp] = isset($blocks[$tmp]) ? $blocks[$tmp] . $v : $v;
				}
			}

			return $blocks;
		}

		private function _assign_file_sub ($name, $val) {
			if (isset($this->filevar_parent[$name])) {
				if ($val != '') {
					$val = $this->_r_getfile($val);
					foreach($this->filevar_parent[$name] as $parent) {
						if (isset($this->preparsed_blocks[$parent]) && !isset($this->filevars[$name])) {
							$copy = $this->preparsed_blocks[$parent];
						} elseif (isset($this->blocks[$parent])) {
							$copy = $this->blocks[$parent];
						}
						$res = array();
						preg_match_all($this->filevar_delim, $copy, $res, PREG_SET_ORDER);
						if (is_array($res) && isset($res[0])) {
							foreach ($res as $v) {
								if ($v[1] == $name) {
									$copy = preg_replace("/" . preg_quote($v[0]) . "/", "$val", $copy);
									$this->preparsed_blocks = array_merge($this->preparsed_blocks, $this->_maketree($copy, $parent));
									$this->filevar_parent = array_merge($this->filevar_parent, $this->_store_filevar_parents($this->preparsed_blocks));
								}
							}
						}
					}
				}
			}
			$this->filevars[$name] = $val;
		}

		public function _store_filevar_parents ($blocks){
			$parents = array();
			foreach ($blocks as $bname => $con) {
				$res = array();
				preg_match_all($this->filevar_delim, $con, $res);
				foreach ($res[1] as $k => $v) {
					$parents[$v][] = $bname;
				}
			}
			return $parents;
		}

		private function _set_error ($str)    {
			$this->_error .= '* ' . $str . " *\n";
		}

		protected function _getfile ($file) {
			if (!isset($file)) {
				$this->_set_error('!isset file name!' . $file);
				return '';
			}

			if (isset($this->files)) {
				if (isset($this->files[$file])) {
					$file = $this->files[$file];
				}
			}

			if (!empty($this->tpldir)) {
				if (is_array($this->tpldir)) {
					foreach ($this->tpldir as $dir) {
						if (is_readable($dir . DIRECTORY_SEPARATOR . $file)) {
							$file = $dir . DIRECTORY_SEPARATOR . $file;
							break;
						}
					}
				} else {
					$file = $this->tpldir. DIRECTORY_SEPARATOR . $file;
				}
			}

			$file_text = '';

			if (isset($this->filecache[$file])) {
				$file_text .= $this->filecache[$file];
				if ($this->debug) {
					$file_text = '<!-- XTemplate debug cached: ' . realpath($file) . ' -->' . "\n" . $file_text;
				}
			} else {
				if (is_file($file) && is_readable($file)) {
					if (filesize($file)) {
						if (!($fh = fopen($file, 'r'))) {
							$this->_set_error('Cannot open file: ' . realpath($file));
							return '';
						}
						$file_text .= fread($fh,filesize($file));
						fclose($fh);
					}
					if ($this->debug) {
						$file_text = '<!-- XTemplate debug: ' . realpath($file) . ' -->' . "\n" . $file_text;
					}
				} elseif (str_replace('.', '', phpversion()) >= '430' && $file_text = @file_get_contents($file, true)) {
					if ($file_text === false) {
						$this->_set_error("[" . realpath($file) . "] ($file) does not exist");
						$file_text = "<b>__XTemplate fatal error: file [$file] does not exist in the include path__</b>";
					} elseif ($this->debug) {
						$file_text = '<!-- XTemplate debug: ' . realpath($file) . ' (via include path) -->' . "\n" . $file_text;
					}
				} elseif (!is_file($file)) {
					$this->_set_error("[" . realpath($file) . "] ($file) does not exist");
					$file_text .= "<b>__XTemplate fatal error: file [$file] does not exist__</b>";
				} elseif (!is_readable($file)) {
					$this->_set_error("[" . realpath($file) . "] ($file) is not readable");
					$file_text .= "<b>__XTemplate fatal error: file [$file] is not readable__</b>";
				}
				$this->filecache[$file] = $file_text;
			}

			return $file_text;
		}

		public function _r_getfile ($file) {
			$text = $this->_getfile($file);
			$res = array();
			while (preg_match($this->file_delim,$text,$res)) {
				$text2 = $this->_getfile($res[1]);
				$text = preg_replace("'".preg_quote($res[0])."'",$text2,$text);
			}
			return $text;
		}

		private function _add_outer_block () {
			$before = $this->block_start_delim . $this->block_start_word . ' ' . $this->mainblock . ' ' . $this->block_end_delim;
			$after = $this->block_start_delim . $this->block_end_word . ' ' . $this->mainblock . ' ' . $this->block_end_delim;
			$this->filecontents = $before . "\n" . $this->filecontents . "\n" . $after;
		}

		private function _pre_var_dump ($args) {
			if ($this->debug) {
				echo '<pre>';
				var_dump(func_get_args());
				echo '</pre>';
			}
		}
	}
}
?>
