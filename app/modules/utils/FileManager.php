<?php

/**
 * 
 * @abstract Handle all files issues to wrap php functions
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 24-feb-2016
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */
class FileManager {

    const FILES = 1;
    const FOLDERS = 2;
    const FILES_AND_FOLDERS = 0;

    public static function writeLocal($filename, $data, $flags = 0, $context = null){
        return file_put_contents($filename, $data, $flags, $context);
    }
    
    public static function createFolderIfNotPresent($path, $mode = 0644){
        if(!file_exists($path)){
            return mkdir($path, $mode);
        }
        return true;
    }
    
    public static function createFileIfNotPresent($path, $mode = 0644){
        return self::createFolderIfNotPresent($path, $mode);
    }

    /**
     * Get list of files and folders under path recursively
     * @param string $path The path to start scanning
     * @param array $exclude List of elements excluded by strpos, can be empty
     * @param array $extensions empty will list all, otherwise will only 
     * include files with extensions in this array, like ['jpg','png']. Define
     * array with lowercase always
     * @param boolean $type 1 : files, 2 : folders, 0: both ( see class const )
     * @return array List with the full paths of files and folders
     */
    public static function recursiveListPath($path, array $exclude, array $extensions, $type = 0){
        
        $listPath = array();
        
        if(!file_exists($path)){
            return $listPath;
        }
        
        $di = new RecursiveDirectoryIterator($path);

        foreach (new RecursiveIteratorIterator($di) as $filename => $file) {

            if($file->isDir() && $type === self::FILES){
                continue;
            }
            if(!$file->isDir() && $type === self::FOLDERS){
                continue;
            }
            if($file->getBaseName() === '..'){
                continue;
            }
            if(!empty($exclude)){
                foreach($exclude as $filter){
                    if(strpos($filename, $filter) !== false){
                        continue 2;
                    }
                }
            }
            if($file->getBaseName() === '.'){
                  $filename = rtrim($filename, '.');
                  $filename = rtrim($filename, "\/");
            }
            $filename = str_replace("\\","/", $filename);
            
            if( !empty($extensions) ){
                $basename = $file->getBaseName();
                $parts = explode('.',$basename);
                $ext = mb_strtolower(end($parts));
                if(!in_array($ext,$extensions)){
                    continue;
                }
            }
            $listPath[] = $filename;
        }
        
        return $listPath;
    }

}