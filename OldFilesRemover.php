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
 * OldFilesRemover class
 * Removes all files older than a DateTime instance
 *
 * Example use : OldFilesRemover::NewInstance(new DirectoryIterator('/path/to/explore'), new DateTime('yesterday midnight'), array('csv', 'pdf'))->deleteOldFiles();
 *      ->  Will delete all csv and pdf files into /path/to/explore older than yesterday, midnight.
 *
 * Check that your date.timezone is correctly set up in your php.ini to avoid timezone issues
 *
 * For a recursive deletion, check out the OldFilesRemoverRecursive class (slower)
 * @author Beno!t POLASZEK - 2014
 * @link https://github.com/bpolaszek/OldFilesRemover
 */

class OldFilesRemover implements \IteratorAggregate, \Countable {

    protected $directoryIterator;
    protected $dateTime;
    protected $extensions           =   array();
    protected $files                =   array();

    /**
     * @param DirectoryIterator $directoryIterator
     * @param DateTime $dateTime
     * @param array $extensions - optionnal - an array of file extensions to filter on (without the dots)
     */
    public function __construct(\DirectoryIterator $directoryIterator, \DateTime $dateTime, Array $extensions = array()) {
        $this->directoryIterator    =   $directoryIterator;
        $this->dateTime             =   $dateTime;
        $this->extensions           =   array_map('strtolower', $extensions);
    }

    /**
     * Constructor alias - useful for chaining
     */
    public static function NewInstance() {
        $CurrentClass = new \ReflectionClass(get_called_class());
        return $CurrentClass->NewInstanceArgs(func_get_args());
    }

    /**
     * Deletes files older than the DateTime instance submitted
     *
     * @return int Number of deleted files
     */
    public function deleteOldFiles() {

        $nbDeletedFiles = 0;

        foreach ($this->directoryIterator AS $file)
            if ($this->isValid($file))
                unlink((string)$file->getPathName()) AND $nbDeletedFiles++;

        if ($nbDeletedFiles > 0)
            $this->files = array();

        return $nbDeletedFiles;
    }

    /**
     * Returns an array of the files that match
     * @return array
     */
    public function getFiles() {

        if (!$this->files)
            foreach ($this->directoryIterator AS $file)
                if ($this->isValid($file))
                    $this->files[] = $file;

        return $this->files;
    }

    /**
     * Checks that a file is a real file, that it has expired, and its extension
     * @param DirectoryIterator $file
     * @return bool
     */
    protected function isValid(\DirectoryIterator $file) {
        return (!$file->isDot() && DateTime::CreateFromFormat('U', $file->getMTime()) < $this->dateTime && (!$this->extensions || in_array(strtolower($file->getExtension()), $this->extensions)));
    }

    /**
     * Iterator interface implementation
     * @return ArrayIterator|Traversable
     */
    public function getIterator() {
        return new \ArrayIterator($this->getFiles());
    }

    /**
     * Countable interface implementation
     * @return int
     */
    public function count() {
        return count($this->getFiles());
    }

}