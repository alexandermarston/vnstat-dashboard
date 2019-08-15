<?php

/*
 * Copyright (C) 2019 Alexander Marston (alexander.marston@gmail.com)
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

class vnStat {
	protected $executablePath;
	protected $vnstatVersion;
	protected $vnstatJsonVersion;
	protected $vnstatData;

	public function __construct ($executablePath) {
		if (isset($executablePath)) {
			$this->executablePath = $executablePath;

			// Execute a command to output a json dump of the vnstat data
			$vnstatStream = popen("$this->executablePath --json", 'r');

			// Is the stream valid?
			if (is_resource($vnstatStream)) {
				$streamBuffer = '';

				while (!feof($vnstatStream)) {
					$streamBuffer .= fgets($vnstatStream);
				}

				// Close the handle
				pclose($vnstatStream);

				$this->processVnstatData($streamBuffer);
			} else {

			}


		} else {
			die();
		}
	}

	private function processVnstatData($vnstatJson) {
		$decodedJson = json_decode($vnstatJson, true);

		// Check the JSON is valid
		if (json_last_error() != JSON_ERROR_NONE) {
			throw new Exception('JSON is invalid');
		}

		$this->vnstatData = $decodedJson;
		$this->vnstatVersion = $decodedJson['vnstatversion'];
		$this->vnstatJsonVersion = $decodedJson['jsonversion'];
	}

	public function getVnstatVersion() {
		return $this->vnstatVersion;
	}

	public function getVnstatJsonVersion() {
		return $this->vnstatJsonVersion;
	}

	public function getInterfaces() {
		// Create a placeholder array
		$vnstatInterfaces = [];

		foreach($this->vnstatData['interfaces'] as $interface) {
			array_push($vnstatInterfaces, $interface['id']);
		}

		return $vnstatInterfaces;
	}

	public function getInterfaceData($timeperiod, $type, $interface) {
		// If json version equals 1, add an 's' onto the end of each type.
		// e.g. 'top' becomes 'tops'
		if ($this->vnstatJsonVersion == 1) {
			$typeAppend = 's';
		}

		// Blank placeholder
		$trafficData = [];

		// Get the array index for the chosen interface
		$arrayIndex = array_search($interface, array_column($this->vnstatData['interfaces'], 'id'));
 
		if ($timeperiod == 'top10') {
			if ($type == 'table') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['top'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

						$trafficData[$i]['label'] = date('d/m/Y', strtotime($traffic['date']['month'] . "/" . $traffic['date']['day'] . "/" . $traffic['date']['year']));;
						$trafficData[$i]['rx'] = formatSize($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = formatSize($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = formatSize(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
                                                $trafficData[$i]['totalraw'] = ($traffic['rx'] + $traffic['tx']);
					}
				}
			}
		}

		if ($timeperiod == 'hourly') {
			if ($type == 'table') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['hour'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

                                                if ($this->vnstatJsonVersion == 1) {
                                                    $hour = $traffic['id'];
                                                } else {
                                                    $hour = $traffic['time']['hour'];
                                                }

						$trafficData[$i]['label'] = date("d/m/Y H:i", mktime($hour, 0, 0, $traffic['date']['month'], $traffic['date']['day'], $traffic['date']['year']));
                                                $trafficData[$i]['time'] =  mktime($hour, 0, 0, $traffic['date']['month'], $traffic['date']['day'], $traffic['date']['year']);
						$trafficData[$i]['rx'] = formatSize($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = formatSize($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = formatSize(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
					}
				}

                                usort($trafficData, sortingFunction);

			} else if ($type == 'graph') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['hour'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

                                                if ($this->vnstatJsonVersion == 1) {
                                                    $hour = $traffic['id'];
                                                } else {
                                                    $hour = $traffic['time']['hour'];
                                                }

						$trafficData[$i]['label'] = sprintf("Date(%d, %d, %d, %d, %d, %d)", $traffic['date']['year'], $traffic['date']['month']-1, $traffic['date']['day'], $hour, 0, 0);
						$trafficData[$i]['rx'] = kibibytesToBytes($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = kibibytesToBytes($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = kibibytesToBytes(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
					}
				}
			}
		}

		if ($timeperiod == 'daily') {
			if ($type == 'table') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['day'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

						$trafficData[$i]['label'] = date('d/m/Y', mktime(0, 0, 0, $traffic['date']['month'], $traffic['date']['day'], $traffic['date']['year']));
						$trafficData[$i]['rx'] = formatSize($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = formatSize($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = formatSize(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
					}
				}
			} else if ($type == 'graph') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['day'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

						$trafficData[$i]['label'] = sprintf("Date(%d, %d, %d, %d, %d, %d)", $traffic['date']['year'], $traffic['date']['month']-1, $traffic['date']['day'], 0, 0, 0);
						$trafficData[$i]['rx'] = kibibytesToBytes($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = kibibytesToBytes($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = kibibytesToBytes(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
					}
				}
			}
		}

		if ($timeperiod == 'monthly') {
			if ($type == 'table') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['month'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

						$trafficData[$i]['label'] = date('F Y', mktime(0, 0, 0, $traffic['date']['month'], 10, $traffic['date']['year']));
						$trafficData[$i]['rx'] = formatSize($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = formatSize($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = formatSize(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
					}
				}
			} else if ($type == 'graph') {
				foreach ($this->vnstatData['interfaces'][$arrayIndex]['traffic']['month'.$typeAppend] as $traffic) {
					if (is_array($traffic)) {
						$i++;

                                                $trafficData[$i]['label'] = sprintf("Date(%d, %d, %d, %d, %d, %d)", $traffic['date']['year'], $traffic['date']['month'] - 1, 10, 0, 0, 0);
						$trafficData[$i]['rx'] = kibibytesToBytes($traffic['rx'], $this->vnstatJsonVersion);
						$trafficData[$i]['tx'] = kibibytesToBytes($traffic['tx'], $this->vnstatJsonVersion);
						$trafficData[$i]['total'] = kibibytesToBytes(($traffic['rx'] + $traffic['tx']), $this->vnstatJsonVersion);
					}
				}
			}
		}

                if ($type == 'graph') {
                    // Get the largest value and then prefix (B, KB, MB, GB, etc)
                    $trafficLargestValue = getLargestValue($trafficData);
                    $trafficLargestPrefix = getLargestPrefix($trafficLargestValue);

                    foreach($trafficData as $key => $value) {
                        $trafficData[$key]['rx'] = formatBytesTo($value['rx'], $trafficLargestPrefix);
                        $trafficData[$key]['tx'] = formatBytesTo($value['tx'], $trafficLargestPrefix);
                        $trafficData[$key]['total'] = formatBytesTo($value['total'], $trafficLargestPrefix);
                        $trafficData[$key]['delimiter'] = $trafficLargestPrefix;
                    }
                }

		return $trafficData;
	}
}

?>
