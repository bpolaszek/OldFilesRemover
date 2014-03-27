<?php

/**
 * MIT License (MIT)
 *
 * Copyright (c) 2014 Beno!t POLASZEK
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * OldFilesRemoverRecursive class
 * Removes all files older than a DateTime instance, recursively
 *
 * Example use :
 * $MyDir   =   new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/path/to/explore'), RecursiveIteratorIterator::SELF_FIRST);
 *
 * OldFilesRemoverRecursive::NewInstance($MyDir, new DateTime('yesterday midnight'), array('csv', 'pdf'))->deleteOldFiles;
 *      ->  Will delete all csv and pdf files into /path/to/explore older than yesterday, midnight, recursively.
 *
 * If $removeEmptyDirs is set to true, we'll remove the empty folders at the end of the process.
 * Check that your date.timezone is correctly set up in your php.ini to avoid timezone issues
 *
 * For a non-recursive deletion, check out the OldFilesRemover class (faster)
 * @author Beno!t POLASZEK - 2014
 * @link https://github.com/bpolaszek/OldFilesRemover
 */

class OldFilesRemoverRecursive extends OldFilesRemover {

    protected $removeEmptyDirs;
    protected $dirsToCheckIfEmpty   =   array();

    /**
     * @param RecursiveIteratorIterator $directoryIterator
     * @param DateTime $dateTime
     * @param array $extensions - optionnal - array of extensions (without the dots)
     * @param bool $removeEmptyDirs - if we need to remove empty directories
     */
    public function __construct(\RecursiveIteratorIterator $directoryIterator, \DateTime $dateTime, Array $extensions = array(), $removeEmptyDirs = true) {
        $this->directoryIterator    =   $directoryIterator;
        $this->dateTime             =   $dateTime;
        $this->extensions           =   array_map('strtolower', $extensions);
        $this->removeEmptyDirs      =   $removeEmptyDirs;
    }

    /**
     * Deletes files older than the DateTime instance submitted
     * If $removeEmptyDirs property is set to true, we'll remove the empty directories affected by deletions
     *
     * @return int Number of deleted files
     */
    public function deleteOldFiles() {

        $nbDeletedFiles = 0;

        foreach ($this->directoryIterator AS $file)
            if ($this->isValid($file))
                unlink((string)$file->getPathName()) AND $this->addDirToCheck($file->getPath()) AND $nbDeletedFiles++;

        if ($nbDeletedFiles > 0)
            $this->files = array();

        if ($this->removeEmptyDirs)
            foreach ($this->dirsToCheckIfEmpty AS &$dir)
                if (count(glob($dir . DIRECTORY_SEPARATOR . '*')) === 0)
                    unlink($dir);

        $this->dirsToCheckIfEmpty = array();

        return $nbDeletedFiles;
    }

    /**
     * Checks that a file is a real file, that it has expired, and its extension
     * @param SplFileInfo $file
     * @return bool
     */
    protected function isValid(\SplFileInfo $file) {
        return ($file->isFile() && DateTime::CreateFromFormat('U', $file->getMTime()) < $this->dateTime && (!$this->extensions || in_array(strtolower($file->getExtension()), $this->extensions)));
    }

    /**
     * Adds a directory to check later if it's empty or not
     * @param $dir
     * @return bool
     */
    protected function addDirToCheck($dir) {
        if (!in_array($dir, $this->dirsToCheckIfEmpty))
            $this->dirsToCheckIfEmpty[] = $dir;
        return true;
    }

}