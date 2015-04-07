<?php
/**
 * Data Reader Processor
 *
 */

require_once '/home/action/workspace/www/DB_ETL.php';


$db = getConnection();
//reset_db($db);
run_data_collection($db);
closeDB($db);


function run_data_collection($db) {
  $start_at = 294569;
  try {
		$file_contents = get_file();
		$line = true;
		$line = fgets($file_contents);
		$cuisines = array();
    
    fill_cuisines($db, $cuisines);
		
		$count = 1;
		while (!empty($line) && $line !== false) {
			$line = fgets($file_contents);
			if ($line === false) {
				break;
			}
      if ($count > $start_at) {
  			process_line($db, $line, $cuisines);
      }
      $count++;
      if ($count > $start_at) echo $count,PHP_EOL;
      /*
      if ($count > $line_count_to_process) { //TODO: remove this limiter
        break;
      }
      */
		}
	} catch (Exception $e) {
		echo 'Whoops! An Error occurred while reading the file.',PHP_EOL,$e;
    return false;
	}
	
	return true;
}

/*
 * Opens and returns a filestream for a file
 * Currently configured for direct online access at: 
 *   https://nycopendata.socrata.com/api/views/xx67-kt59/rows.csv?accessType=DOWNLOAD
 *
 * @return stream The filestream for the requested file
*/
function get_file() {
  $filename = 'https://nycopendata.socrata.com/api/views/xx67-kt59/rows.csv?accessType=DOWNLOAD';
	return fopen($filename, 'r');
}

function process_line($db, $line, &$cuisines) {
	$split_line = str_getcsv($line);
  
  for ($i = 0; $i < count($split_line); $i++) {
    $split_line[$i] = trim($split_line[$i]);
  }
  
  $raw_grade = $split_line[14];
  if (empty($raw_grade)) {
    return;
  }
  $number_grade = 0;
  // A = 4, B = 3, C = 2, D = 1, F = 0
  switch ($raw_grade) {
    case 'A':
      $number_grade++;
    case 'B':
      $number_grade++;
    case 'C':
      $number_grade++;
    case 'D':
      $number_grade++;
  }
  
  // reformat to Year-Month-Date from Month/Date/Year
  $split_inspection_date = explode('/', $split_line[8]);
  $inspection_date = $split_inspection_date[2] . '-' . $split_inspection_date[0] . '-' . $split_inspection_date[1];
  $split_grade_date = explode('/', $split_line[15]);
  $grade_date = $split_grade_date[2] . '-' . $split_grade_date[0] . '-' . $split_grade_date[1];
	
  // (1) See if the Cuisine exists, add it if it doesn't
  $line_cuisine = $split_line[7];
	if (!in_array($line_cuisine, $cuisines)) {
    $query = 'INSERT INTO ETL.Cuisines (CuisineName, Count) VALUES ("' . $line_cuisine . '", 1);';
    query($db, $query);
    if (!empty(report_last_error($db))) {
      var_dump($split_line);
      throw new Exception('Problem inserting Cuisine' . PHP_EOL . report_last_error($db));
    }
    $query = 'SELECT LAST_INSERT_ID() AS CuisineID;';
    $result = query($db, $query);
    $row = $result->fetch_assoc();
		$cuisines[$row['CuisineID']] = $line_cuisine;
    $cuisines[$line_cuisine] = $row['CuisineID'];
	} else {
    $query = 'UPDATE ETL.Cuisines SET Count = (Count + 1) 
              WHERE CuisineID = "' . $cuisines[$line_cuisine] .'";';
    query($db, $query);
  }
  
  // (2) See if the Restuarant exists, if it doesn't, then add it
  $query = 'SELECT 1 FROM ETL.Restaurants WHERE RestaurantID = "' . $split_line[0] . '"; ';
  $result = query($db, $query);
  $row = $result->fetch_assoc();
  if (empty($row)) {
		$query = 'INSERT INTO ETL.Restaurants
		(RestaurantID, Name, Boro, Address, Phone, CuisineID)
		VALUES
		("' . $split_line[0] . '", ' // RestaurantID
			. '"' . $split_line[1] . '", ' // Name
			. '"' . $split_line[2] . '", ' // Boro
			. '"' . $split_line[3] . ' ' . $split_line[4] . ' ' . $split_line[5] . '", ' // Address
		  . '"' . $split_line[6] . '", ' // Phone
			. $cuisines[$line_cuisine] . ');'; // CuisineID
    query($db, $query);
    if (!empty(report_last_error($db))) {
      var_dump($split_line);
      throw new Exception('Problem inserting Restaurant' . PHP_EOL . report_last_error($db));
    }
	}
	
  // (3) Finally, add the information from the Inspection
	$query = 'INSERT INTO ETL.Inspections
		(RestaurantID, InspectionDate, Grade, GradeDate)
		VALUES
    ("' . $split_line[0] . '", ' // RestaurantID
    . '"' . $inspection_date . '", ' // InspectionDate
    . $number_grade . ', ' // Grade
    . '"' . $grade_date . '"); '; // GradeDate
	
  query($db, $query);
  if (!empty(report_last_error($db))) {
    var_dump($split_line);
    throw new Exception('Problem inserting Inspection' . PHP_EOL . report_last_error($db));
  }
}

function fill_cuisines($db, &$cuisines) {
  $query = 'SELECT CuisineID, CuisineName FROM ETL.Cuisines;';
  $result = query($db, $query);
  while ($row = $result->fetch_assoc()) {
    $cuisines[$row['CuisineID']] = $row['CuisineName'];
    $cuisines[$row['CuisineName']] = $row['CuisineID'];
  }
}
      