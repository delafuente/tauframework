<?php

/**
 * Rename this file to settings.php
 * @abstract Main settings file
 * @author Lucas de la Fuente
 * @project tau
 * @encoding UTF-8
 * @date 02-feb-2011
 * @copyright (c) Lucas de la Fuente <lucasdelafuente1978@gmail.com>
 * @license https://github.com/delafuente/tauframework/blob/master/LICENSE The MIT License (MIT)
 */

//Not editable
$protocol = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";

define('PROTOCOL', $protocol);
define('WITH_DATA_MANAGER',true);
define('WITH_INPUT_VALIDATOR',true);
define('WITHOUT_DATA_MANAGER',false);

//Start editing here
define('APPLICATION_NAME', 'myapp.com'); //main domain
define('CANONICAL_APP_NAME','www.myapp.com');
define('APPLICATION_INSTALLED',false);
define('SITE_NAME', 'MyApp');

if (PRODUCTION_ENVIRONMENT) {

    define('DB_NAME', 'db_name');
    define('DB_ADMIN', 'db_user');
    define('DB_ADMIN_PASSWD', 'db_password');
    define('DB_HOST', 'localhost');

    define('LOG_PATH', '/path/to/your/app/log/');
    define('APPLICATION_PATH', '/path/to/your/app'); //radix
    define('WEB_PATH', '/path/to/your/web'); //your web
    define('APPLICATION_BASE_URL', PROTOCOL . 'myapp.com');
    define('SAVE_GENERATED_SQL_FOLDER', '/path/to/your/app/sql');
    define('TAU_BASE_URL', PROTOCOL . 'localhost.tau');
    define('TAU_UPLOADS_PATH ', "/path/to/your/app/uploads/"); //where to store user uploads
    define('DEBUG_MODE', false); //Set false for no logging
    define('VERBOSE_MODE', false);
    define('BENCH_MODE', false); // Database benchmarck logging
    define('LU_COOKIE_PATH', '/');
    define('LU_COOKIE_DOMAIN', 'myapp.com');
    define('MIGRATES_FOLDER', WEB_PATH . "/migrates");
    define('USE_TAU_CACHE', true);
    define('DB_LOG_ALL_QUERIES', false); //DB write all queries to log, use with caution, as it writes a lot
    
} else if (DEVELOPMENT_ENVIRONMENT) {

    define('DB_NAME', 'db_name');
    define('DB_ADMIN', 'db_user');
    define('DB_ADMIN_PASSWD', 'db_password');
    define('DB_HOST', 'localhost');

    define('LOG_PATH', '/path/to/your/app/log/');
    define('APPLICATION_PATH', '/path/to/your/app'); //radix
    define('WEB_PATH', '/path/to/your/web'); //your web
    define('APPLICATION_BASE_URL', PROTOCOL . 'myapp.com');
    define('SAVE_GENERATED_SQL_FOLDER', '/path/to/your/app/sql');
    define('TAU_BASE_URL', PROTOCOL . 'localhost.tau');
    define('TAU_UPLOADS_PATH ', "/path/to/your/app/uploads/"); //where to store user uploads
    define('DEBUG_MODE', false); //Set false for no logging
    define('VERBOSE_MODE', false);
    define('BENCH_MODE', false); // Database benchmarck logging
    define('LU_COOKIE_PATH', '/');
    define('LU_COOKIE_DOMAIN', 'myapp.com');
    define('MIGRATES_FOLDER', WEB_PATH . "/migrates");
    define('USE_TAU_CACHE', true);
    define('DB_LOG_ALL_QUERIES', false); //DB write all queries to log, use with caution, as it writes a lot
    
} else if (LOCAL_WITH_LOCALHOST) {


    define('DB_NAME', 'db_name');
    define('DB_ADMIN', 'db_user');
    define('DB_ADMIN_PASSWD', 'db_password');
    define('DB_HOST', 'localhost');

    //define('LOG_PATH', LOCAL_DRIVE . ':/path/to/your/app/log/'); //For Windows users
    define('LOG_PATH', '/path/to/your/app/log/');
    //define('APPLICATION_PATH', LOCAL_DRIVE . ':/path/to/your/app'); //For Windows users
    define('APPLICATION_PATH', '/path/to/your/app'); //radix
    define('WEB_PATH', '/path/to/your/web'); //your web

    define('APPLICATION_BASE_URL', PROTOCOL . 'localhost_url');
    define('SAVE_GENERATED_SQL_FOLDER', '/path/to/your/app/sql');
    define('TAU_BASE_URL', PROTOCOL . 'localhost_url_of_tau');
    define('TAU_UPLOADS_PATH ', "/path/to/your/app/uploads/"); //where to store user uploads
    define('DEBUG_MODE', true); //Set false for no logging
    define('VERBOSE_MODE', true);
    define('BENCH_MODE', false); // Database benchmarck logging
    define('LU_COOKIE_PATH', "/");
    define('LU_COOKIE_DOMAIN', false);
    define('MIGRATES_FOLDER', WEB_PATH . "/migrates");
    define('USE_TAU_CACHE', true);
    define('DB_LOG_ALL_QUERIES', false); //DB write all queries to log, use with caution, as it writes a lot

} else {
    die("<p>settings error: CONSTANTS ERROR IN config.php, PRODUCTION" .
            "_ENVIRONMENT or ONE LOCAL_WITH_* constant must be set to true</p>");
}
if(DEBUG_MODE){
    ini_set('display_errors', true);
}else{
    ini_set('display_errors', false);
}
define('USER_IMAGES_URL', APPLICATION_BASE_URL.'/uploads');
define('ALL_QUERIES_LOGFILE', 'allqueries');
//Data that (normally) remains unchanged in every environment
$autoloadPaths = array(
    APPLICATION_PATH . "/tau/inc",
    APPLICATION_PATH . "/tau/inc/framework",
    APPLICATION_PATH . "/app/libs",
    APPLICATION_PATH . "/app/modules",
    APPLICATION_PATH . "/app/modules/scripts",
    APPLICATION_PATH . "/app/modules/user",
    APPLICATION_PATH . "/app/modules/login",
    APPLICATION_PATH . "/app/controllers/widgets",
    APPLICATION_PATH . "/app/controllers/widgets/web"
);
define('NO_CACHE', false);
/** Field span error class */
define('SPAN_ERROR_CLASS', 'spanError');
/** Field error class */
define('FIELD_ERROR_CLASS', 'fieldError' );
/** Migrates split characters, for each sql sentence */
define('SQL_SPLIT', '-- &|6.28@tau&');
define('CACHE_PATH', WEB_PATH . "/cache");

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');
/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', 'utf8_general_ci');

//Application data
define('DEFAULT_LANG_ABBR', 'es');
define('DEFAULT_LANG_CODE', 'es_ES');
define('DEFAULT_COUNTRY', 'es');
define('ALLOWED_COUNTRIES', 'es,en'); //csv codes, allowed application countries
define('ALLOWED_LANGS', 'es,en'); //csv codes, allowed web page translations
//Other constants
define('KILOBYTES', 1024);
define('MEGABYTES', 1048576);
define('SECONDS_ONE_HOUR', 3600);
define('SECONDS_ONE_DAY', 86400);
if (!LOCAL_WITH_LOCALHOST) {
    define('SECONDS_ONE_MONTH', 2592200);
}else{
    define('SECONDS_ONE_MONTH', false); //2592200
}
define('SECONDS_ONE_YEAR', 31104400);


/** File issues */
define('LU_ALLOW_IMAGE_UPLOADS', true); //If false, users can't upload photos
define('LU_MAX_IMAGE_SIZE', 2 * MEGABYTES); //in bytes
define('LU_MAX_IMAGE_WIDTH', 2500); //In pixels. 0 equals 'unlimited'. Max allowed, images can be resized later
define('LU_MAX_IMAGE_HEIGHT', 0); //In pixels. 0 equals 'unlimited'. Max allowed, images can be resized later
define('LU_MAKE_THUMBS', true); //Makes a thumb each time a picture is uploaded
define('LU_THUMBS_WIDTH', 182); //in pixels, if not used LU_THUMBS_RESIZE_TO ( recommended )
define('LU_THUMBS_HEIGHT', 270); //in pixels, if not used LU_THUMBS_RESIZE_TO ( recommended )
define('LU_THUMBS_QUALITY', 99); // 1 to 99, the higher the better
define('LU_THUMBS_RESIZE_TO',270); //in pixels, thumbs will resize its max measure to this

define('LU_IMAGE_ALLOWED_EXTENSIONS', "jpg,jpeg,jpe,png,gif"); //csv list of allowed file extensions
define('LU_IMAGE_ALLOWED_MIME_TYPE', "image/jpeg,image/gif,image/pjpeg,image/png"); //csv list of all allowed mime types

define('LU_DEL_FILES_IF_ACCOUNT_DELETE', true); //all files of that user will be deleted if account is closed
define('LU_DEL_FILES_IF_ROW_DELETE', false); //if a row with type img (or any of file) is deleted, also delete the file

/*Photos can be resized and watermarked depending on this settings, 
  if you want to maintain original files uploaded also, use the following two constants */
define('LU_DUPLICATE_FILES', true); //copy any uploaded file to LU_DUPLICATE_FILES_PATH when it reach the server, and rename it
define('LU_DUPLICATE_FILES_PATH',"/path/to/original/images/backup/");

/** Watermark */
define('WATERMARK_IMAGES',true); //defines if the app should watermark images
define('WATERMARK_IMAGE_PATH', APPLICATION_PATH . "/path/to/watermark"); // System expects watermark_300.png, watermark_400.png, until watermark_900.png
define('WATERMARK_CENTERED',true); //Centers both vertical and horizontal
define('WATERMARK_X_PERCENT',2); //If not centered, the x position of watermark in %
define('WATERMARK_Y_PERCENT',45); //If not centered, the x position of watermark in %
/** mail config */
define('MAIL_SMTP_HOST', 'smtp.' . APPLICATION_NAME);
define('MAIL_POP3_HOST', 'pop3.' . APPLICATION_NAME);
define('MAIL_IMAP_HOST', 'mail.' . APPLICATION_NAME);
define('CONTACT_TO_RECIPIENT', 'contact@' . APPLICATION_NAME);
define('WEBMASTER_MAIL', 'contact@' . APPLICATION_NAME);
define('WEBMASTER_MAIL_PASSWORD', '****');
define('ERROR_MAIL', 'error' . APPLICATION_NAME);
define('ERROR_RECIPIENT_MAIL', 'error@' . APPLICATION_NAME);
define('INFO_MAIL', 'contact@' . APPLICATION_NAME);
define('INFO_MAIL_PASSWORD', '****');
define('REGISTER_FORM_MAIL', 'register@' . APPLICATION_NAME);
define('REGISTER_FORM_MAIL_POP', 'register%' . APPLICATION_NAME);
define('REGISTER_FORM_MAIL_PASSWORD', '****');
define('IS_GMAIL_REGISTER_ACCOUNT', false);
/** others */
define('LU_MAX_FIELD_LENGTH_ON_TABLE', 255); // for LuTable, if value in td is bigger, will be cropped and added (...)
/* http://www.php.net/manual/es/timezones.php for more timezone codes if you need to change this */
define('CLIENT_SERVER_TIMEZONE', 'Europe/Madrid'); // for adjust time of server to local time;
define('LU_DEFAULT_LANG_CODE', 'es_ES');
/** Friendship module */
define('ALLOW_RECONCILIATIONS', true); //If true, a broken relationship could be requested again
define('MAX_FRIENDS_ON_SIDEBAR', 30); //If you show a list of friends, this is the showed limit

define('CACHE_SECONDS_LIFETIME',3600);
define('ALLOW_FORM_DATA_REFRESH',true);

// LOAD THIS IN CREDENTIALS FILES, one for environment
/* Recaptcha config, if you want to use into application, it's integrated with forms */
define('RECAPTCHA_PUBLIC_KEY', 'your public key');
define('RECAPTCHA_PRIVATE_KEY', 'your private key');
define('GOOGLE_ANALYTICS_UID','test');

//Lang based configuration for dates, doesn't rely on country
//we can have country = de ( Germany ), and lang = at ( Austriac ), with different
//date formats. Date validation will use this format, and datepicker-{replace_lang} must
//use the same values, or validation will be wrong.
$lang_local = array(
  'es' => array('date_format' => 'dd/mm/yy', 'date_first_day' => 1),
  'en' => array('date_format' => 'yy/mm/dd', 'date_first_day' => 0)
);