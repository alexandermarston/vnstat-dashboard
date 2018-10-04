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

 /**
  * A simple renderer
  */
 class Renderer
 {
 	protected $app;

 	function __construct($app)
 	{
 		$this->app = $app;
 	}


	function printOptions()
	{
	    $i = 0;
	    foreach ($this->app->getInterfaceList() as $interface) {
	        $i++;
	        if ($i == count($this->app->getInterfaceList())) {
	            echo "<a href=\"?i=" . rawurlencode($interface) . "\">" . rawurlencode($interface) . "</a>";
	        } else {
	            echo "<a href=\"?i=" . rawurlencode($interface) . "\">" . rawurlencode($interface) . ", </a>";
	        }
	    }
	}

	function printTableStats($type, $interface, $label)
	{
	    echo '<table class="table table-bordered">
	        <thead>
	        <tr>
	            <th>' . $label . '</th>
	            <th>Received</th>
	            <th>Sent</th>
	            <th>Total</th>
	        </tr>
	        </thead>
	        <tbody>';
	    $data = $this->app->getVnstatData($type, $interface);

	    for ($i = 0; $i < count($data); $i++) {
	        $label = $data[$i]['label'];
	        $totalReceived = $data[$i]['rx'];
	        $totalSent = $data[$i]['tx'];
	        $totalTraffic = $data[$i]['total'];
	        echo '<tr>';
	        echo '<td>' . $label . '</td>';
	        echo '<td>' . $totalReceived . '</td>';
	        echo '<td>' . $totalSent . '</td>';
	        echo '<td>' . $totalTraffic . '</td>';
	        echo '</tr>';

	    }
	    echo '</tbody></table>';
	}
 }