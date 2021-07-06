<?php

/**
 * Script to build a .pot Gettext language file.
 *
 * Usage: cat MyFile.php | php buildpot.php > strings.pot
 *
 * @author Marcus Povey <marcus@marcus-povey.co.uk>
 * @package Known-Language-Tools
 */


$in = file_get_contents("php://stdin");
$filenames = explode("\n", $in);
$handled = [];

function getLineNumber($content, $charpos)
{
    list($before) = str_split($content, $charpos); // fetches all the text before the match

    return strlen($before) - strlen(str_replace("\n", "", $before)) + 1;
}

foreach ($filenames as $filename) {

    $file = @file_get_contents($filename);

    if (!empty($file)) {
        if (preg_match_all('/_\((\'|")(.*)(\'|")(\)|,)/imsU', $file, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[2] as $translation) {

                $string = $translation[0];
                $offset = $translation[1];
                $linenumber = getLineNumber($file, $offset);

                $string = preg_replace('/\s{2,}/', ' ', $string);
                $string = str_replace(["\'",'\"',"\n"], ["'",'"',''], $string);
                //$normalised_string = str_replace('"', '\"', $string);
                $normalised_string = addcslashes($string, '"');

                //  $handled[] = $normalised_string; // duplication prevention

                    $handled[$normalised_string][] = "#: $filename:$linenumber\n";

            }
        }
    }
}

foreach ($handled as $normalised_string => $v) {

    // print instance(s)
    foreach ($v as $w) {
        echo $w;
    }

    // print localisable string
    if (strlen($normalised_string) > 70) {
        echo "msgid \"\"\n";
        echo "\"";
        echo wordwrap($normalised_string, 76, "\"\n\"");
        echo "\"\n";
    } else {
        echo "msgid \"$normalised_string\"\n";
    }
    echo "msgstr \"\"\n\n";

}

