<?php
class WPEditorBrowser {
  
  public static function getFilesAndFolders($dir, $contents, $type) {
    $slash = '/';
    if(WPWINDOWS) {
      $slash = '\\';
    }
    $output = array();
    if(is_dir($dir)) {
    	if($handle = opendir($dir)) {
    		$size_document_root = strlen($_SERVER['DOCUMENT_ROOT']);
    		$pos = strrpos($dir, $slash);
    		$topdir = substr($dir, 0, $pos + 1);
    		$i = 0;
    	  while(false !== ($file = readdir($handle))) {
          if($file != '.' && $file != '..' && substr($file, 0, 1) != '.' && WPEditorBrowser::allowedFiles($dir, $file)) {
    				$rows[$i]['data'] = $file;
    				$rows[$i]['dir'] = is_dir($dir . $slash . $file);
    				$i++;
    			}
    		}
      	closedir($handle);
    	}

    	if(isset($rows)) {  
    	  $size = count($rows);
      	$rows = self::sortRows($rows);
      	for($i = 0; $i < $size; ++$i) {
      		$topdir = $dir . $slash . $rows[$i]['data'];
      		$output[$i]['name'] = $rows[$i]['data'];
      		$output[$i]['path'] = $topdir;
      		if($rows[$i]['dir']) {
      			$output[$i]['filetype'] = 'folder';
      			$output[$i]['extension'] = 'folder';
      			$output[$i]['filesize'] = '';
      		}
      		else {
      			$output[$i]['filetype'] = 'file';
      			$path = pathinfo($output[$i]['name']);
      			if(isset($path['extension'])) {
      			  $output[$i]['extension'] = $path['extension'];
      			}
      			$output[$i]['filesize'] = '(' . round(filesize($topdir) * .0009765625, 2) . ' KB)';
      			if($type == 'theme') {
        		  $output[$i]['file'] = str_replace(realpath(get_theme_root()) . $slash, '', $output[$i]['path']);
        		  $output[$i]['url'] = get_theme_root_uri() . '/' . $output[$i]['file'];
        		}
        		else {
        		  $output[$i]['file'] = str_replace(realpath(WP_PLUGIN_DIR) . $slash, '', $output[$i]['path']);
            	$output[$i]['url'] = plugins_url() . '/' . $output[$i]['file'];
        		}
      		}
      	}
    	}
    	else {
    	  $output[-1] = 'this folder has no contents';
    	}
    }
    elseif(is_file($dir)) {
      if(isset($contents) && $contents == 1) {
        $output['name'] = basename($dir);
    		$output['path'] = $dir;
    		$output['filetype'] = 'file';
    		$path = pathinfo($output['name']);
  			if(isset($path['extension'])) {
  			  $output['extension'] = $path['extension'];
  			}
    		$output['content'] = file_get_contents($dir);
    		if($type == 'theme') {
    		  $output['file'] = str_replace(realpath(get_theme_root()) . $slash, '', $output['path']);
    		}
    		else {
    		  $output['file'] = str_replace(realpath(WP_PLUGIN_DIR) . $slash, '', $output['path']);
    		}
      	
      	$output['url'] = plugins_url() . $slash . $output['file'];
      }
      else {
    	  $pos = strrpos($dir, $slash);
      	$newdir = substr($dir, 0, $pos);
      	if($handle = opendir($newdir)) {
      		$size_document_root = strlen($_SERVER['DOCUMENT_ROOT']);
      		$pos = strrpos($newdir, $slash);
      		$topdir = substr($newdir, 0, $pos + 1);
      		$i = 0;
      	  while(false !== ($file = readdir($handle))) {
            if($file != '.' && $file != '..' && substr($file, 0, 1) != '.' && WPEditorBrowser::allowedFiles($newdir, $file)) {
      				$rows[$i]['data'] = $file;
      				$rows[$i]['dir'] = is_dir($newdir . $slash . $file);
      				$i++;
      			}
      		}
        	closedir($handle);
      	}
      
        if(isset($rows)) {
      	  $size = count($rows);
        	$rows = self::sortRows($rows);
        	for($i = 0; $i < $size; ++$i) {
        		$topdir = $newdir . $slash . $rows[$i]['data'];
        		$output[$i]['name'] = $rows[$i]['data'];
        		$output[$i]['path'] = $topdir;
        		if($rows[$i]['dir']) {
        			$output[$i]['filetype'] = 'folder';
        			$output[$i]['extension'] = 'folder';
        			$output[$i]['filesize'] = '';
        		}
        		else {
        			$output[$i]['filetype'] = 'file';
        			$path = pathinfo($rows[$i]['data']);
        			if(isset($path['extension'])) {
        			  $output[$i]['extension'] = $path['extension'];
        			}
        			$output[$i]['filesize'] = '(' . round(filesize($topdir) * .0009765625, 2) . ' KB)';
        		}
        		if($output[$i]['path'] == $dir) {
        		  $output[$i]['content'] = file_get_contents($dir);
        		}
        		if($type == 'theme') {
        		  $output[$i]['file'] = str_replace(realpath(get_theme_root()) . $slash, '', $output[$i]['path']);
        		}
        		else {
        		  $output[$i]['file'] = str_replace(realpath(WP_PLUGIN_DIR) . $slash, '', $output[$i]['path']);
        		}
          	
          	$output[$i]['url'] = plugins_url() . $slash . $output[$i]['file'];
        	}
        }
        else {
          $output[-1] = 'bad file or unable to open';
        }
      }
    }
    else {
    	$output[-1] = 'bad file or unable to open';
    }
    return $output;
  }
  
  public static function sortRows($data) {
  	$size = count($data);

  	for($i = 0; $i < $size; ++$i) {
  		$row_num = self::findSmallest($i, $size, $data);
  		$tmp = $data[$row_num];
  		$data[$row_num] = $data[$i];
  		$data[$i] = $tmp;
  	}

  	return $data;
  }

  public static function findSmallest($i, $end, $data) {
  	$min['pos'] = $i;
  	$min['value'] = $data[$i]['data'];
  	$min['dir'] = $data[$i]['dir'];
  	for(; $i < $end; ++$i) {
  		if($data[$i]['dir']) {
  			if($min['dir']) {
  				if($data[$i]['data'] < $min['value']) {
  					$min['value'] = $data[$i]['data'];
  					$min['dir'] = $data[$i]['dir'];
  					$min['pos'] = $i;
  				}
  			}
  			else {
  				$min['value'] = $data[$i]['data'];
  				$min['dir'] = $data[$i]['dir'];
  				$min['pos'] = $i;
  			}
  		}
  		else {
  			if(!$min['dir'] && $data[$i]['data'] < $min['value']) {
  				$min['value'] = $data[$i]['data'];
  				$min['dir'] = $data[$i]['dir'];
  				$min['pos'] = $i;
  			}
  		}
  	}
  	return $min['pos'];
  }
  
  public static function allowedFiles($dir, $file) {
    $slash = '/';
    if(WPWINDOWS) {
      $slash = '\\';
    }
    $output = true;
    $allowed_extensions = explode('~', WPEditorSetting::getValue('plugin_editor_allowed_extensions'));
    
    if(is_dir($dir . $slash . $file)) {
      $output = true;
    }
    else {
      $file = pathinfo($file);
      if(isset($file['extension']) && in_array($file['extension'], $allowed_extensions)) {
        $output = true;
      }
      else {
        $output = false;
      }
    }
    return $output;
  }
  
}