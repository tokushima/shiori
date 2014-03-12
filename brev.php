<?php
namespace brev{
	class Args{
		static private $opt = array();
		static private $value = array();

		static public function init(){
			$opt = $value = array();
			$argv = array_slice((isset($_SERVER['argv']) ? $_SERVER['argv'] : array()),1);
			
			for($i=0;$i<sizeof($argv);$i++){
				if(substr($argv[$i],0,2) == '--'){
					$opt[substr($argv[$i],2)][] = ((isset($argv[$i+1]) && $argv[$i+1][0] != '-') ? $argv[++$i] : true);
				}else if(substr($argv[$i],0,1) == '-'){
					$keys = str_split(substr($argv[$i],1),1);
					if(count($keys) == 1){
						$opt[$keys[0]][] = ((isset($argv[$i+1]) && $argv[$i+1][0] != '-') ? $argv[++$i] : true);
					}else{
						foreach($keys as $k){
							$opt[$k][] = true;
						}
					}
				}else{
					$value[] = $argv[$i];
				}
			}
			self::$opt = $opt;
			self::$value = $value;
		}
		static public function opt($name,$default=false){
			return array_key_exists($name,self::$opt) ? self::$opt[$name][0] : $default;
		}
		static public function value($default=null){
			return isset(self::$value[0]) ? self::$value[0] : $default;
		}
		static public function opts($name){
			return array_key_exists($name,self::$opt) ? self::$opt[$name] : array();
		}
		static public function values(){
			return self::$value;
		}
	}
	class Server{
		static private function log($state,$file,$uri){
			$error_log = ini_get('error_log');

			$data = array(
					date('Y-m-d H:i:s'),
					$state,
					(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''),
					$file,
					(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : ''),
					(is_file($error_log) ? filesize($error_log) : 0),
					$uri,
			);
			file_put_contents('php://stdout',implode("\t,",$data).PHP_EOL);
		}
		static private function include_php($filename,$subdir){
			if(isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_PORT'])){
				$_ENV['APP_URL'] = 'http://'.$_SERVER['SERVER_NAME'].(($_SERVER['SERVER_PORT'] != 80) ? (':'.$_SERVER['SERVER_PORT']) : '').$subdir;
			}
			include($filename);
		}
		static public function router(){
			$dir = getcwd();
			$subdir = '';
			$uri = $_SERVER['REQUEST_URI'];
			if(strpos($uri,'?') !== false) list($uri) = explode('?',$uri,2);
			if(substr($uri,0,1) == '/'){
				$uri = substr($uri,1);
			}
			$uri_exp = explode('/',$uri,2);
			if(is_dir($uri_exp[0])){
				$subdir = '/'.$uri_exp[0];
				$dir = $dir.$subdir;
					
				if(isset($uri_exp[1])){
					$uri_exp = explode('/',$uri_exp[1],2);
				}
			}
			chdir($dir);
			if(is_file($f=($dir.'/'.implode('/',$uri_exp)))){
				self::log('success',$f,$uri);
				$info = pathinfo($f);
				if(isset($info['extension']) && strtolower($info['extension']) == 'php'){
					self::include_php($f,$subdir);
				}else{
					$mime = function($filename){
						$ext = (false !== ($p = strrpos($filename,'.'))) ? strtolower(substr($filename,$p+1)) : null;
						switch($ext){
							case 'jpg':
							case 'jpeg': return 'jpeg';
							case 'png':
							case 'gif':
							case 'bmp':
							case 'tiff': return 'image/'.$ext;
							case 'css': return 'text/css';
							case 'txt': return 'text/plain';
							case 'html': return 'text/html';
							case 'xml': return 'application/xml';
							case 'js': return 'text/javascript';
							case 'flv':
							case 'swf': return 'application/x-shockwave-flash';
							case '3gp': return 'video/3gpp';
							case 'gz':
							case 'tgz':
							case 'tar':
							case 'gz': return 'application/x-compress';
							case 'csv': return 'text/csv';
							case null:
							default:
								return 'application/octet-stream';
						}
					};
					header('Content-Type: '.$mime($f));
					readfile($f);
				}
			}else if(is_file($f=($dir.'/'.$uri_exp[0])) || is_file($f=($dir.'/'.$uri_exp[0].'.php'))){
				$_SERVER['PATH_INFO'] = '/'.(isset($uri_exp[1]) ? $uri_exp[1] : '');
				self::log('success',$f,$uri);
					
				self::include_php($f,$subdir,$uri);
			}else if(is_file($f=($dir.'/index.php'))){
				$_SERVER['PATH_INFO'] = '/'.implode('/',$uri_exp);
					
				self::log('success',$f);
				self::include_php($f,$subdir,$uri);
			}else{
				header('HTTP/1.1 404 Not Found');
				self::log('failure','',$uri);
			}
		}
	}
	class Command{
		static private function get_include_path(){
			$include_path = array();

			if(is_file($f=getcwd().'/bootstrap.php') || is_file($f=getcwd().'/vendor/autoload.php')){
				try{
					ob_start();
						include_once(realpath($f));
					ob_end_clean();
				}catch(\Exception $e){
				}
			}
			foreach(explode(PATH_SEPARATOR,get_include_path()) as $p){
				if(($rp = realpath($p)) !== false){
					$include_path[$rp] = true;
				}
			}			
			if(is_dir($d=__DIR__.'/lib')){
				$include_path[realpath($d)] = true;
			}
			if(class_exists('Composer\Autoload\ClassLoader')){
				$r = new \ReflectionClass('Composer\Autoload\ClassLoader');
				$composer_dir = dirname($r->getFileName());
				
				
				if(is_file($bf=realpath(dirname($composer_dir).'/autoload.php'))){
					ob_start();
						include_once($bf);
						if(is_file($composer_dir.'/autoload_namespaces.php')){
							$class_loader = include($bf);

							foreach($class_loader->getPrefixes() as $v){
								foreach($v as $p){
									if(($rp = realpath($p)) !== false){
										$include_path[$rp] = true;
									}
								}
							}
						}
					ob_end_clean();
				}
			}
			krsort($include_path);
			return array_keys($include_path);
		}
		static private function get_file($command){
			if(strpos($command,'::') !== false){
				list($command,$func) = explode('::',$command,2);
					
				foreach(self::get_include_path() as $p){
					if(is_file($f=($p.'/'.str_replace('.','/',$command).'/cmd/'.$func.'.php'))){
						return $f;
					}
				}
			}else{
				foreach(self::get_include_path() as $p){
					if(is_file($f=($p.'/'.str_replace('.','/',$command).'/cmd.php'))){
						return $f;
					}
				}
			}
			throw new \InvalidArgumentException($command.' found.');
		}
		static public function exec($command,$error_funcs=null){
			try{
				$file = self::get_file($command);
				if(is_file($f=dirname($file).'/__setup__.php')){
					include($f);
				}
				$rtn = include(self::get_file($command));
					
				if(is_file($f=dirname($file).'/__teardown__.php')){
					include($f);
				}
			}catch(\Exception $e){
				print('Exception: ');
				print(implode(PHP_EOL.' ',explode(PHP_EOL,PHP_EOL.$e->getMessage())));
				print(PHP_EOL.PHP_EOL);

				if(!is_callable($error_funcs) && defined('BREV_ERROR_CALLBACK')){
					$error_funcs = constant('BREV_ERROR_CALLBACK');
				}				
				if(is_string($error_funcs)){
					if(strpos($error_funcs,'::') !== false){
						$error_funcs = explode('::',$error_funcs);
						if(strpos($error_funcs[0],'.') !== false){
							$error_funcs[0] = '\\'.str_replace('.','\\',$error_funcs[0]);
						}						
					}
				}
				if(is_callable($error_funcs)){
					call_user_func_array($error_funcs,array($e));
				}
			}
		}
		static public function doc($command){
			$file = self::get_file($command);
			$doc = (preg_match('/\/\*\*.+?\*\//s',file_get_contents($file),$m)) ?
			trim(preg_replace("/^[\s]*\*[\s]{0,1}/m","",str_replace(array('/'.'**','*'.'/'),'',$m[0]))) :
			'';
				
			$help_params = array();
			$pad = 4;
			if(preg_match_all('/@.+/',$doc,$as)){
				foreach($as[0] as $m){
					if(preg_match("/@(\w+)\s+([^\s]+)\s+\\$(\w+)(.*)/",$m,$p)){
						if($p[1] == 'param'){
							$help_params[$p[3]] = array($p[2],trim($p[4]));
						}
					}else if(preg_match("/@(\w+)\s+\\$(\w+)(.*)/",$m,$p)){
						$help_params[$p[2]] = array(null,trim($p[3]));
					}
				}
				foreach(array_keys($help_params) as $k){
					if($pad < strlen($k)){
						$pad = strlen($k);
					}
				}
			}
			print(PHP_EOL.'Usage:'.PHP_EOL);
			print('  '.$command.PHP_EOL);
			if(!empty($help_params)){
				print("\n  Options:\n");
				foreach($help_params as $k => $v){
					print('    '.sprintf('--%s%s %s',str_pad($k,$pad),(empty($v[0]) ? '' : ' ('.$v[0].')'),trim($v[1]))."\n");
				}
			}
			$doc = trim(preg_replace('/@.+/','',$doc));
			print("\n\n  description:\n");
			print('    '.str_replace("\n","\n    ",$doc)."\n\n");
		}
		static public function get_list(){
			$list = array();
			$get_summary = function($file){
				$src = file_get_contents($file);
				list($summary) = (preg_match('/\/\*\*.+?\*\//s',$src,$m)) ?
				array_slice(explode(PHP_EOL,trim(preg_replace("/^[\s]*\*[\s]{0,1}/m","",str_replace(array('/'.'**','*'.'/'),'',$m[0])))),0,1) :
				array('');
				return $summary;
			};
			
			foreach(self::get_include_path() as $p){
				if(($r = realpath($p)) !== false){
					foreach(new \RecursiveIteratorIterator(
							new \RecursiveDirectoryIterator($r,\FilesystemIterator::SKIP_DOTS|\FilesystemIterator::UNIX_PATHS)
							,\RecursiveIteratorIterator::SELF_FIRST
					) as $f){
						if(
								$f->isDir() &&
								ctype_upper(substr($f->getFilename(),0,1)) &&
								strpos($f->getPathname(),'/.') === false &&
								strpos($f->getFilename(),'_') !== 0
						){
							if(is_file($cf=$f->getPathname().'/cmd.php') && !isset($list[$cf])){
								$class = str_replace('/','.',substr(dirname($cf),strlen($r)+1));
								$list[$cf] = array($class,$get_summary($cf));
							}
							if(is_dir($cd=$f->getPathname().'/cmd/')){
								foreach(new \DirectoryIterator($cd) as $fi){
									if(
										$fi->isFile() &&
										strpos($fi->getFilename(),'_') !== 0 &&
										substr($fi->getFilename(),-4) == '.php' &&
										!isset($list[$fi->getPathname()] )
									){
										$class = str_replace('/','.',substr($f->getPathname(),strlen($r)+1));
										$list[$fi->getPathname()] = array($class.'::'.substr($fi->getFilename(),0,-4),$get_summary($fi->getPathname()));
									}
								}
							}
						}
					}
				}
			}
			return $list;
		}
	}
}
/**
 * brev PHP Command line tools
 * (brevicipitidae)
 */
namespace{
	set_error_handler(function($n,$s,$f,$l){
		throw new \ErrorException($s,0,$n,$f,$l);
	});
	if(ini_get('date.timezone') == '') date_default_timezone_set('Asia/Tokyo');
	if(extension_loaded('mbstring')){
		if('neutral' == mb_language()) mb_language('Japanese');
		mb_internal_encoding('UTF-8');
	}

	if(isset($_SERVER['SERVER_SOFTWARE']) && preg_match('/PHP.+Development Server/',$_SERVER['SERVER_SOFTWARE'])){
		\brev\Server::router();
		exit;
	}
	\brev\Args::init();
	
	if(\brev\Args::value() == null){
		$list = \brev\Command::get_list();
		$len = 8;
		foreach($list as $info){
			if($len < strlen($info[0])) $len = strlen($info[0]);
		}
		foreach($list as $info){
			print('  '.str_pad($info[0],$len).' : '.$info[1].PHP_EOL);
		}
		exit;
	}
	
	if(\brev\Args::opt('h') === true || \brev\Args::opt('help') === true){
		\brev\Command::doc(\brev\Args::value());
		exit;
	}
	\brev\Command::exec(\brev\Args::value(),\brev\Args::opt('error-callback'));
}

