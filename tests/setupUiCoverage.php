<?php

require_once(__DIR__ . '/config.php');

foreach (new DirectoryIterator('.') as $file) {
    if ($file->isDot()) {
        continue;
    }

    //Files in that dir
    if ($file->isFile()
        && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getFilename());
        if (strpos($content, '###CCSTART') === false
            && strpos($content, '###CCEND') === false) {
            continue;
        }

        insertCCCode($file->getFilename());
    } //go down one dir
    elseif ($file->isDir()) {
        foreach (new DirectoryIterator($file->getFilename()) as $subdir) {
            if ($subdir->isDir()
                || $subdir->isDot()
                || $subdir->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getFilename() . '/' . $subdir->getFilename());
            if (strpos($content, '###CCSTART') === false
                && strpos($content, '###CCEND') === false) {
                continue;
            }


            insertCCCode($file->getFilename() . '/' . $subdir->getFilename());
        }
    }
}


function insertCCCode(string $filename)
{
    $content = file_get_contents($filename);
    $content = str_replace(
        '###CCSTART',
        '
$filter = new \SebastianBergmann\CodeCoverage\Filter;
$filter->includeDirectory(FILE_BASE_PATH . \'src/View\');
$filter->includeDirectory(FILE_BASE_PATH . \'src/App\');
$coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage(
    (new \SebastianBergmann\CodeCoverage\Driver\Selector)->forLineCoverage($filter),
    $filter
);
$coverage->start(uniqid(\'\', true));

',
        $content
    );
    $content = str_replace(
        '###CCEND',
        '
$app->onHook(
    \atk4\ui\App::HOOK_BEFORE_EXIT,
    function () use ($coverage) {
        $coverage->stop(true);
        (new \SebastianBergmann\CodeCoverage\Report\PHP())->process($coverage, FILE_BASE_PATH . \'tests/coverage/\' . uniqid(\'\', true) . \'.cov\');
    }
);
',
        $content
    );

    file_put_contents($filename, $content);
    echo PHP_EOL . 'CCCode inserted in ' . $filename;
}