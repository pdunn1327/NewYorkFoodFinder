<?php
/* 
 * File to handle the AJAX requests from the client
 */
require_once '/home/action/workspace/www/DB_ETL.php';
//require_once '/home/action/workspace/www/data_reader_functions.php';

$type = sanitize($_POST['type']);
//$message = $_POST['message'];

$return_array = array('success' => 'false');

switch ($type) {
  case 'search_start':
    //$cuisine = sanitize($_POST['cuisine']);
    //$grade = sanitize($_POST['grade']);
    //$return_array = perform_basic_search($cuisine, $grade)
    break;
  case 'reset_db':
    $return_array = perform_db_reset();
    break;
  case 'load_db':
    $return_array = load_db();
    break;
  case 'basic_search':
    $return_array = perform_basic_search(38, 3);
    break;
}

// end by echoing the return array
echo json_encode($return_array);

/*
 * Never blindly trust input. Strip out special characters before using it.
 * 
 * @param string $input A string we wish to sanitize for later use
 * 
 * @return string The sanitized input
 */
function sanitize($input) {
  return trim(htmlentities($input));
}

function perform_basic_search($cuisine, $min_grade, $count_limit=0) {
  $grade = array(0 => 'F', 1 => 'D', 2 => 'C', 3 => 'B', 4 => 'A');
  
  $db = getConnection();
  $query = 'SELECT r.Name, i.Grade FROM ETL.Restaurants r '
          .'INNER JOIN ETL.Inspections i ON r.RestaurantID = i.RestaurantID '
          .'WHERE r.CuisineID = ' . $cuisine . ' '
          .'AND i.Grade >= ' . $min_grade . ' '
          .'GROUP BY r.Name, i.Grade '
          .'ORDER BY i.GradeDate DESC, r.Name ASC ';
  
  if ($count_limit > 0) {
    $query .= 'LIMIT ' . $count_limit . ' ';
  }
  $query .= ';';
  

  $result = query($db, $query);
  $return_array = ["success" => "true"];
  $html = '<table class="text"><tr><th>Restaurant</th><th>Grade</th></tr>';
  while ($row = $result->fetch_assoc()) {
    $html .= '<tr><td>' . $row['Name'] . '</td><td>' . $grade[$row['Grade']] . '</td></tr>';
  }
  $html .= '</table>';
  $return_array['html'] = $html;
  return $return_array;
}

function perform_db_reset() {
  $db = getConnection();
  $it_worked = reset_db($db);
  closeDB($db);
  
  return ["success" => "true"];
}

function load_db() {
  $db = getConnection();
  $it_worked = run_data_collection($db);
  closeDB($db);
  
  return ($it_worked) ? ["success" => "true"] : ["success" => "false"];
}