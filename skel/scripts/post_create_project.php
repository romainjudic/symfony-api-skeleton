#!/usr/bin/php
<?php

/**
 * Read the target file, replace placeholders with actual values
 * from a replace array and write it back to the file.
 *
 * @param string $target
 * @param array $replaces
 */
function applyValues($target, $replaces)
{
    file_put_contents(
        $target,
        strtr(
            file_get_contents($target),
            $replaces
        )
        );
}

/**
 * Recursively delete a directory.
 *
 * @param string $dir
 * @return bool
 */
function delTree($dir)
{
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }

    return rmdir($dir);
}




// We get the project name from the name of the path that Composer created for us.
$projectName = basename(realpath("."));
echo "Project name $projectName taken from directory name\n";

// Values to replace in the skeleton templates (README, composer.json, etc)
$replaces = [
    "{{ projectName }}" => $projectName,
];

// Copy and fill template files in project root
foreach (glob('skel/templates/*') as $template) {
    $target = basename($template);

    // Copy the template file to a file with the same name at project root
    echo "Creating clean file ($target) from template ($template)...\n";
    copy($template, $target);

    // Apply values to template variables
    echo "Applying variables to $target...\n";
    applyValues($target, $replaces);
}

// Remove all files that are only related to the skeleton
echo "Removing templates and skeleton related files...\n";
delTree('skel');
// Start the new project without composer.lock to install latest package versions
unlink("composer.lock");

echo "\033[0;32mPost create project script done...\n";

exit(0);