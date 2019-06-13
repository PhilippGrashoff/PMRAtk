<?php

foreach(new \DirectoryIterator('.') as $file) {
    if($file->isDot()
    || $file->isDir()
    || $file->getExtension() !== 'php') {
        continue;
    }

    $content = file_get_contents($file->getFilename());
    if(strpos($content, '###CCSTART') === false
    && strpos($content, '###CCEND') === false) {
        continue;
    }

    $content = str_replace('###CCSTART', '
$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(new \SebastianBergmann\CodeCoverage\Driver\Xdebug());
$coverage->filter()->addDirectoryToWhitelist(\'src/View\');
$coverage->start(pathinfo(__FILE__, PATHINFO_FILENAME));
', $content);
    $content = str_replace('###CCEND', '
$app->addHook(\'beforeExit\', function () use($coverage) {
    $coverage->stop(true);
    $writer = new \SebastianBergmann\CodeCoverage\Report\PHP();
    $writer->process($coverage, \'tests/coverage/\'.basename($_SERVER[\'SCRIPT_NAME\'], \'.php\').\'-\'.uniqid().\'.cov\');
});
', $content);

    file_put_contents($file->getFilename(), $content);
}