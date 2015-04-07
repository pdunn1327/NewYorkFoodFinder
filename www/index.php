<?php

//require_once '/home/action/workspace/www/data_reader_functions.php';
require_once '/home/action/workspace/www/DB_ETL.php';

//require_once '/home/action/workspace/www/page_blocks/global_header.php';
$db = getConnection();

$cuisines = array();
$query = 'SELECT CuisineName, CuisineID FROM ETL.Cuisines;';
$result = query($db, $query);
while ($row = $result->fetch_assoc()) {
  $cuisines[$row['CuisineName']] = $row['CuisineID'];
}

$grades = array('A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'F' => 0);

function make_dropdown($array, $id) {
  $html = '<select id="' . $id . '">';
  foreach ($array AS $key => $value) {
    $html .= '<option value="' . $value . '">' . $key . '</option>';
  }
  $html .= '</select>';
  return $html;
}

?>
<html>
  <title>New York Food Finder</title>
  <head>
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <script src="js/jquery-2.1.3.min.js"></script>
  </head>
  <body>
    <div class="center_titles">
      <p>
        <div id="main_title">Whatcha' Wanna' Eat?</div>
        <div class="subtitle">&nbsp;&nbsp;&nbsp;&nbsp;a New York Food Finder</div>
      </p>
    </div>
    <div id="main_panel" width="500px">
      <br/>
      <form id="basic_search">
        <button class="button_base" type="button">
          <span class="button_text">
            Show me the most recently Thai restaurants with a B grade or better...
          </span>
        </button>
      </form>
      <form id="search_form">
        <span class="text">Show me the </span><?= make_dropdown($cuisines, '#ddl_cuisines')?>
        <br/><span class="text"> restaurants with a </span><?= make_dropdown($grades, '#ddl_grades')?>
        <br/><span class="text"> or better... </span>
        <br/>
        <br/>
        <button class="bigbutton button_base" id="search_btn" type="button">
          <span class="button_text">
            Search
          </span>
        </button>
      </form>
      <form id="reset_bigbtn">
        <button class="bigbutton button_base" type="submit">
          <span class="button_text">
            Reset
          </span>
        </button>
      </form>
      <div id="search_area">
        
      </div>
      <div id="admin_area">
        <table>
          <tr>
            <td>
              Administrative Area
            </td>
          </tr>
          <tr>
            <td>
              <form id="reset_btn">
                <button class="bigbutton button_base" type="button">
                  <span class="button_text">
                    ResetDB
                  </span>
                </button>
              </form>
            </td>
            <td>
              <form id="load_btn">
                <button class="bigbutton button_base" type="button">
                  <span class="button_text">
                    LoadDB
                  </span>
                </button>
              </form>
            </td>
        </table>
        <div id="admin_msg">
        </div>
      </div>
    </div>
    <br/>
    <p class="center_titles subtitle">(c) 2015 Patrick Dunn</p>
    <script src="js/index.js"></script>
  </body>
</html>
<?
