<?php

/* 
 * See license information at the package root in LICENSE.md
 */

header(filter_input(INPUT_SERVER, 'PROTOCOL', FILTER_DEFAULT) . " 404 Not Found");
exit;