<?php

foreach(new \DirectoryIterator('.') as $file) {
    if($file->isDot()) {
        continue;
    }

    //Files in that dir
    if($file->isFile()
        && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getFilename());
        if (strpos($content, '###CCSTART') === false
            && strpos($content, '###CCEND') === false) {
            continue;
        }

        insertCCCode($file->getFilename());
    }

    //go down one dir
    elseif($file->isDir()) {
        foreach(new \DirectoryIterator($file->getFilename()) as $subdir) {
            if($subdir->isDir()
                || $subdir->isDot()
                || $subdir->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getFilename().'/'.$subdir->getFilename());
            if (strpos($content, '###CCSTART') === false
                && strpos($content, '###CCEND') === false) {
                continue;
            }


            insertCCCode($file->getFilename().'/'.$subdir->getFilename(), '../');
        }

    }
}


function insertCCCode(string $filename, string $pathPrefix = '') {
    $content = file_get_contents($filename);
    $content = str_replace('###CCSTART', '
$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage();
$coverage->setProcessUncoveredFilesFromWhitelist(true);
$coverage->filter()->addDirectoryToWhitelist(\''.$pathPrefix.'src/View\');
$coverage->start(uniqid(\'\', true));
', $content);
    $content = str_replace('###CCEND', '
$app->addHook(\'beforeExit\', function () use($coverage) {
    $coverage->stop(true);
    $writer = new \SebastianBergmann\CodeCoverage\Report\PHP();
    $writer->process($coverage, \''.$pathPrefix.'tests/coverage/\'.uniqid(\'\', true).\'.cov\');
});
', $content);

    file_put_contents($filename, $content);
}