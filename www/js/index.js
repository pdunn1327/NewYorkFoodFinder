$(document).ready(function(){ 
  var request;

  function makeAjaxCall(data, update_target) {
    $.ajax({
      url: "/ajax.php", 
      type: "post",
      data: data, 
      success: function (response) {
        var data = JSON.parse(response);
        $(update_target).html(data['html']);
      },

      error: function() {
        alert("failure with ajax request");
        $(update_target).html('There was an error with your request.');
      }
    });
  }

  $("#search_btn").click(function(){
    if (request) {
      request.abort();
    }
    $("#search_area").html("Request sent, give it a minute, will ya'?");

    var selectedCuisine = $("#ddl_cuisines").val();
    var selectedGrade = $("#ddl_grades").val();

    var serializedData = {"type":"search_start", "cuisine":selectedCuisine,"grade":selectedGrade};

    makeAjaxCall(serializedData, "#search_area");
  });

  $("#basic_search").click(function(){
    if (request) {
      request.abort();
    }
    $("#search_area").html("Request sent, give it a minute, will ya'?");

    var serializedData = {"type":"basic_search"};

    makeAjaxCall(serializedData, "#search_area");
  });

  $("#reset_btn").click(function(){
    if (request) {
      request.abort();
    }
    $("#admin_msg").html("Attempting to reset DB, will drop tables<br/>and db, will not reload data.");

    var serializedData = {"type":"reset_db"};

    makeAjaxCall(serializedData, "#admin_msg");
  });

  $("#load_btn").click(function(){
    if (request) {
      request.abort();
    }
    $("#admin_msg").html("Attempting to load data into the db.");

    var serializedData = {"type":"load_db"};

    makeAjaxCall(serializedData, "#admin_msg");
  });
});