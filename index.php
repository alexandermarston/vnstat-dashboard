<?php
/*
 * Copyright (C) 2016 Alexander Marston (alexander.marston@gmail.com)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

$config = require_once('config.php'); // Include all the configuration information
require('vnstat.php'); // The vnstat information parser
require('renderer.php'); // Include a simple renderer

// Create a new VNStat app instance
$app = new VNStat($config);

// Create a Renderer instance passing the VNStat app object
$render = new Renderer($app);

$thisInterface = "";

if (isset($_GET['i'])) {
    $interfaceChosen = rawurldecode($_GET['i']);
    if (in_array($interfaceChosen, $app->getInterfaceList(), true)) {
        $thisInterface = $interfaceChosen;
    } else {
        $thisInterface = $app->getFirstInterface();
    }
} else {
    // Assume they mean the first interface
    $thisInterface = $app->getFirstInterface();
}

require_once ('index2.php');