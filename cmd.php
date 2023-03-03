<?php

require realpath(__DIR__) . '/includes.php';

set_language_by_code('fa_IR');
echo json_encode(command_list());

echo "<br><br>";

set_language_by_code('en_US');
echo json_encode(command_list());