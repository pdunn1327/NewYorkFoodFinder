<?php
/**
 * A collection of database-related functions.
 * This could have been a singleton class but that could cause problems
 * with unit testing. At least with a centralized set of functions they could be
 * mocked or swapped out as necessary and they can be re-used between procedural files.
 * This also allows for different database types to be used without the more
 * logic-oriented files from having to know what the database underlying the system is.
 *
 * @author Patrick Dunn <pdunn1327@gmail.com>
 */
function getConnection() {
  return new mysqli('wicked-wagon-wheel-99-160008', 'root', ''); //nitrous.io box
}

function query($db, $query) {
  $result = $db->query($query);
  if(!$result) {
    die('There was an error running the query [' . $db->error . ']');
  }
  return $result;
}

function closeDB($db) {
  $db->close();
}

function get_affected_rows($db) {
  return $db->affected_rows;
}

function reset_db($db) {
  $queries = array(
    'DROP TABLE IF EXISTS ETL.Restaurants;',
    'DROP TABLE IF EXISTS ETL.Cuisines;',
    'DROP TABLE IF EXISTS ETL.Inspections;',
    'DROP TABLE IF EXISTS ETL.Grades;',
    'DROP DATABASE IF EXISTS ETL;',
    'CREATE DATABASE ETL;',
    'CREATE TABLE ETL.Restaurants (
        RestaurantID VARCHAR(255) PRIMARY KEY,
        Name VARCHAR(100),
        Boro VARCHAR(25),
        Address VARCHAR(255),
        Phone VARCHAR(15),
        CuisineID INT
      );',
    'CREATE TABLE ETL.Cuisines (
        CuisineID INT PRIMARY KEY AUTO_INCREMENT,
        CuisineName VARCHAR(255),
        Count INT
      );',
    'CREATE TABLE ETL.Inspections (
        InspectionID INT PRIMARY KEY AUTO_INCREMENT,
        RestaurantID VARCHAR(255),
        InspectionDate VARCHAR(15),
        Grade INT,
        GradeDate VARCHAR(15)
      );',
    'CREATE TABLE ETL.Grades (
        Grade INT PRIMARY KEY,
        GradeLetter VARCHAR(1)
      );',
    'INSERT INTO ETL.Grades (Grade, GradeLetter) 
      VALUES (0, "F"),(1, "D"),(2, "C"),(3, "B"),(4, "A");',
  );
  
  foreach ($queries AS $query) {
    //query($db, $query); // Disabled for safety purposes
  }
}

function report_last_error($db) {
  return $db->error;
}