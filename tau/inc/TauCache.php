<?php
/**
 * 
 * @abstract Handles cache of common used pages
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 25-jun-2013
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

class TauCache {

    protected $last_edit; //last edit of file
    protected $fileHash; //Full path of cached file
    
    protected $messages; // array of messages of the class, for verbose purposes
    protected $updateCacheFile; // if true, will update cache and cache_time files
    protected $useCachedFile; // If true, will render cache file
    protected $fullCacheFilePath;
    
    public function __construct() {
        $this->messages = array();
        
        if(USE_TAU_CACHE && VERBOSE_MODE){
            $created = date("Y-m-d H:i:s", time());
            $this->messages[] = "--- Cache Class Verbose ---";
            $this->messages[]  = " -- cache executed: $created -- ";
            file_put_contents(CACHE_PATH . "/output.html", "");
        }
    }
    /**
     * Initialize the Cache object to deal with a file
     * @param string $fullPath Path ( full or relative ) to the file
     * @param string $lang Two letters code of the language, like es,en etc
     */
    public function init($fullPath, $lang) {
        
        $this->messages[] = "Cache Lifetime: " . CACHE_SECONDS_LIFETIME . "";
        $this->useCachedFile = false;
        $this->updateCacheFile = false;
        
        $filename = basename($fullPath);
        $this->fileHash = substr(sha1( $fullPath ), 0, 8) . "_" . $filename;
        $subfolder = $this->fileHash[0]; // accessing first char of string
        $this->fullCacheFilePath = CACHE_PATH . "/$lang/$subfolder/" . $this->fileHash;
        
        if(!file_exists(CACHE_PATH . "/$lang")){
            mkdir(CACHE_PATH . "/$lang", 0744);
        }
        if(!file_exists(CACHE_PATH . "/$lang/$subfolder")){
            mkdir(CACHE_PATH . "/$lang/$subfolder", 0744);
        }
        
        $this->messages[] = "Request URI: " . $_SERVER['REQUEST_URI'];
        
        if(file_exists($this->fullCacheFilePath)){
            $this->messages[] = "File $fullPath exists as " . $this->fileHash;
            $time_last_modified = filemtime($this->fullCacheFilePath);
            $file_timelapse = time() - $time_last_modified;
            
            if($file_timelapse > CACHE_SECONDS_LIFETIME){
                $this->messages[] = "File lifetime is longer than CACHE_SECONDS_LIFETIME, so set file to be updated";
                $this->updateCacheFile = true;
            }else{
                $this->messages[] = "File lifetime is shorter than CACHE_SECONDS_LIFETIME, so do not update file, and USE it";
                $this->useCachedFile = true;
            }
        }else{
            $this->updateCacheFile = true;
            $this->messages[] = "File $fullPath DOES NOT EXIST as " . $this->fileHash;
        }
    }
    
    /**
     * Get if we're going to use the cache file, to be tested outside class.
     * @return boolean True if we're going to use the cache file, false otherwise
     */
    public function useCacheFile(){
        return $this->useCachedFile;
    }
    /**
     * Get the file cached contents
     * @return string The html cached
     */
    public function getCacheFile(){
        return file_get_contents($this->fullCacheFilePath) . "<!-- FROM CACHE -->\n";
        
    }
    
    /**
     * Saves the cached content to a file
     * @param string $content The html render of the file to cache
     */
    public function saveCacheFile($content){
        if($this->updateCacheFile){
                $this->messages[] = "saveCacheFile() : Saving file to " . $this->fullCacheFilePath;
                file_put_contents($this->fullCacheFilePath, $content);
        }
    }
    /**
     * Force not save the cache file
     */
    public function setNoSaveCache(){
        $this->messages[] = "setNoSaveCache() called";
        $this->updateCacheFile = false;
    }
    /**
     * Force not use cache file, must be used just after object creation
     */
    public function setNoUseCacheFile(){
        $this->messages[] = "setNoUseCacheFile() called";
        $this->useCachedFile = false;
    }
    /**
     * Get all messages of the Class
     * @param boolean $asHtml If true, will return lines as paragraphs, or LF if false
     * @return string The messages of the class execution
     */
    public function getMessages($asHtml = true){
        
        $endString = "";
        
        foreach ($this->messages as $message){
            if($asHtml){
                $endString .= "<p>" . $message . "</p>";
            }else{
                $endString .= $message . "\n";
            }
        }
        return $endString;
    }
    /**
     * Save all the messages in an html file, for test purposes only
     */
    public function saveMessagesToFile(){
        $mess = $this->getMessages();
        file_put_contents(CACHE_PATH . "/output.html", $mess, FILE_APPEND);
    }
    
    
}