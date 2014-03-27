OldFilesRemover
===============

A simple class to remove all files older than a DateTime instance.
In fact, you can choose between 2 classes :
- OldFilesRemover for removing files on a single-level
- OldFilesRemoverRecursive for removing files recursively

Usage for OldFilesRemover :
```php
$MyDir              =   new DirectoryIterator('/path/to/explore');
$Expired            =   new DateTime('yesterday midnight');
$OldFilesRemover    =   new OldFilesRemover($MyDir, $Expired);

// You can have a look at which files are going to be deleted.
var_dump($OldFilesRemover->getFiles());

// You can also iterate on these files to do something else
foreach ($OldFilesRemover AS $file)
    var_dump($file) // DirectoryIterator object
    
// Will delete all files older than yesterday, midnight
$NbRemovedFiles =   $OldFilesRemover->deleteOldFiles();

// One-line Short-hand
OldFilesRemover::NewInstance($MyDir, $Expired)->deleteOldFiles();
``` 

Usage for OldFilesRemoverRecursive :
```php
// Basically the same, but the constructor prefers a RecursiveIteratorIterator
$MyDir              =   new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/path/to/explore'), 
                                                          RecursiveIteratorIterator::SELF_FIRST);
$Expired            =   new DateTime('-1 year');
$Extensions         =   array('csv', 'pdf'); // For deleting only csv and pdf files

// Will recursively delete all csv and pdf files older than 1 year from today
OldFilesRemoverRecursive::NewInstance($MyDir, $Expired, $Extensions)->deleteOldFiles();
``` 
