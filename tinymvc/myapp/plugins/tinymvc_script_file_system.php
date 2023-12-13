<?php

if (!function_exists('filesystem')) {
    /**
     * Returns the filesystem wrapper library.
     *
     * @return TinyMVC_Library_Filesystem
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#filesystem
     *
     * @deprecated `[2022-04-30]` `v2.34` in favor of the `\ExportPortal\Contracts\Filesystem\FilesystemProviderInterface\FilesystemProviderInterface`
     *
     * Reason: replace with new filesystem integration
     *
     * ```php
     * use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
     * //...
     * $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
     * ```
     */
    function filesystem()
    {
        return library(TinyMVC_Library_Filesystem::class);
    }
}

/**
 * Removes a directory and all its contents.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $dir    - path of the directory to be deleted
 * @param string $action - 'delete' by default if you want to remove the dir itself and not only the contents
 *
 * @return false|void
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#remove_dirdir-action-delete
 */
function remove_dir($dir, $action = 'delete')
{
    if (empty($dir)) {
        return false;
    }

    foreach (glob($dir . '/*') as $file) {
        if (is_dir($file)) {
            remove_dir($file);
        } else {
            unlink($file);
        }
    }

    if ('delete' == $action) {
        @rmdir($dir);
    }
}

/**
 * Removes a hidden directory and all its contents.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $dir    - path of the directory to be deleted
 * @param string $action - 'delete' by default if you want to remove the dir itself and not only the contents
 *
 * @return false|void
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#remove_dir_hiddendir-action-delete
 */
function remove_dir_hidden($dir, $action = 'delete')
{
    if (empty($dir)) {
        return false;
    }

    if ($handle = opendir($dir . '/')) {
        while (false !== ($entry = readdir($handle))) {
            if (in_array($entry, ['.', '..'])) {
                continue;
            }

            if (is_dir($dir . '/' . $entry)) {
                remove_dir_hidden($dir . '/' . $entry);
            } else {
                @unlink($dir . '/' . $entry);
            }
        }
        closedir($handle);
    }

    if ('delete' == $action) {
        @rmdir($dir);
    }
}

/**
 * Removes the list of files.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param array $files     - list of file names or paths that should be removed
 * @param bool  $directory
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#remove_filesfiles-directory-false
 */
function remove_files($files, $directory = false)
{
    foreach ($files as $file) {
        if (false != $directory) {
            $file = $directory . '/' . $file;
        }

        @unlink($file);
    }
}

/**
 * Creates a directory with rights.
 *
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [26.10.2021]
 * Reason: refactor method name
 *
 * @param string $dir  - full path of the new directory
 * @param int    $mode - the rights
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#create_dirdir-mode-0755
 */
function create_dir($dir, $mode = 0775)
{
    if (!is_dir($dir)) {
        mkdir($dir, $mode, true);
    }
}

/**
 * Decodes the string that was first encrypted with the encriptedFolderName function.
 *
 * @param string $folderName - the string containing the hashed folder name to decrypt
 *
 * @return string|void
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#checkencriptedfolderfolder_name
 */
function checkEncriptedFolder($folderName)
{
    $folderSha1 = substr($folderName, 0, 40);
    $folder = substr($folderName, -13);
    $key = config('fileupload_secret_key');

    if (sha1($key . $folder) == $folderSha1) {
        return $folder;
    }

    return false;
}

/**
 * Encodes the string if sent, if not returns an unique id encrypted.
 *
 * @param string $folderName - the string containing the name to be encrypted
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#encriptedfoldernamefolder_name-false
 */
function encriptedFolderName($folderName = false)
{
    if (empty($folderName)) {
        $folderName = uniqid();
    }

    $key = config('fileupload_secret_key');

    return sha1($key . $folderName) . $folderName;
}

/**
 * Returns the path to the file including the version.
 *
 * @param string $relpath - the path to the file
 * @param string $base    - base url (by default __FILES_URL)
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#filemodificationtimerelpath-base-__files_url
 */
function fileModificationTime($relpath, $base = __FILES_URL)
{
    if (file_exists($relpath)) {
        clearstatcache(true, $relpath);
        $version = md5(filemtime($relpath));

        return "{$base}{$relpath}?{$version}";
    }

    return "{$base}{$relpath}";
}

/**
 * Returns the mime type of the file.
 *
 * @param string $filename - path to file
 *
 * @return string
 *
 * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#mimecontenttypefilename
 */
function mimeContentType($filename)
{
    return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filename);
}

if (!function_exists('copyFileIfExists')) {
    /**
     * Copies a file from one location to another, but checks first if it exists.
     *
     * @param string     $path        - path to the file to be copied
     * @param string     $destination - the path to the new location
     * @param null|mixed $context     - a valid context resource created with stream_context_create() if needed
     *
     * @return bool
     *
     * @see http://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/c.-Helpers/a.-FileSystem#copyfileifexistspath-destination-context-null
     */
    function copyFileIfExists($path, $destination, $context = null)
    {
        if (null !== $path && file_exists($path) && is_file($path)) {
            if (null !== $context && is_resource($context)) {
                return copy($path, $destination, $context);
            }

            return copy($path, $destination);
        }

        return true;
    }
}

/**
 * Gets a path to a file if it exists or return the defualt if set.
 *
 * @param string      $path         - path to file
 * @param bool|string $templatePath - path to the default
 *
 * @see
 */
function getFileExits($path, $templatePath = false)
{
    $path_parts = pathinfo($path);
    if (!empty($path_parts['extension']) && file_exists($path)) {
        return $path;
    }

    return $templatePath;
}
