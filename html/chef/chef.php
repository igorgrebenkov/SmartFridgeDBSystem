<?php
session_start();

// Retrieve the users credentials for DB connection
$conn_string= "host=localhost port=5432 dbname=smartfridgedb user=postgres password=csi2132";

// GET DB CONNECTION STRING
$dbconn = pg_connect($conn_string) or die ("Connection failed");
$query= "set search_path = 'public'";

if(isset($_GET['runFunction']) && function_exists($_GET['runFunction']))
	call_user_func($_GET['runFunction']);
else
	echo "Function not found or wrong input";

function createMeal() {
	echo "<br>";
    echo "<form target = 'meal_create_sent_frame' method='post' action='html/chef/chef.php?runFunction=addMeal'>";
	echo "<br>Chef ID: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "<input type = 'text' name = 'chef_id_str'><br>"; 
	echo "Meal Name: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "<input type = 'text' name = 'meal_name_str'><br>";
	echo "Meal Description: &nbsp";
	echo "<input type = 'text' name = 'meal_desc_str'><br>";
	echo "Cuisine: &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
	echo "<input type = 'text' name = 'meal_cuis_str'><br>";
	
	$query1 = "SELECT * FROM INGREDIENTS";
	$result = pg_query($GLOBALS['dbconn'], $query1);
  
	// shows a table with all the ingredients for the chef to select from.
	if (!$result) {
		echo "An error occurred.\n";
		exit;
	} else {
		echo "<table style='width:100%'>";
		echo "<tr style='font-weight:bold'> <td>Ingredient Id</td>" . "<td>Name</td>" . "<td>Expiry Date</td>" . "<td>Price</td>" . "<td>Count</td>" . "<td>Category</td>" . "<td>Select</td>" . "</tr>";
		while ($row = pg_fetch_row($result)) {
		  echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[1]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td><td>" . "$row[5]" . "</td>";
		  echo "<td>" . "<input type='checkbox' name='ingredient_checkbox[]' value='$row[0]'>" . "</td>";
		  echo "</tr>";
		}
		echo "</table>";
		echo "<br><iframe name='meal_create_sent_frame' height='200' width='1000' scrolling='yes' src='html/chef/create-meal-iframe-default.html'></iframe>";
		echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
		echo "<input id='submit' name='submit' type='submit' value='Create Meal' class='btn btn-primary'><br><br>";
		echo "</div>";
		echo "</form>";
	}
}

// check whether an ingredient is checked.
function isChecked($chkname,$value) {
	if (!empty($_POST[$chkname])) {
		foreach($_POST[$chkname] as $chkval) {
			if ($chkval == $value) {
				return true;
			}
		}
	}
	return false;
}

function addMeal() {
	if (isset($_POST['chef_id_str']))
		$chef_id_str = $_POST['chef_id_str'];
	if (isset($_POST['meal_name_str']))
		$meal_name_str = $_POST['meal_name_str'];
	if (isset($_POST['meal_desc_str']))
		$meal_description_str = $_POST['meal_desc_str'];
	if (isset($_POST['meal_cuis_str']))
		$meal_cuisine_str = $_POST['meal_cuis_str'];

    // get array of all checkbox values 

    if (empty($selected_ingredients)) { 
        echo "You didn't select anything.";
    } else {
        $selected_ingredients = $_POST['ingredient_checkbox'];
        $meal_id_query = "SELECT Meal_id FROM MEALS ORDER BY Meal_id DESC LIMIT 1";
        $meal_id_query_res = pg_query($GLOBALS['dbconn'], $meal_id_query);

        if (!$meal_id_query_res) {
            echo "An error occurred.\n";
        } else {
            $meal_id_query_row = pg_fetch_row($meal_id_query_res);
            $max_meal_id = (int) $meal_id_query_row[0];
            $meal_id_str = $max_meal_id+1;
        }

        // Create MEAL
        $meal_create_query = "INSERT INTO MEALS(Meal_id, Chef_id, Name, Descr, Cuisine) VALUES (" . $meal_id_str ."," . $chef_id_str . ",'" . $meal_name_str . "','" . $meal_description_str . "','" . $meal_cuisine_str . "');";
        $meal_create_query_res = pg_query($GLOBALS['dbconn'], $meal_create_query);

        if (!$meal_create_query_res) {
            echo "An error occurred.\n";
        } else {
            // Add ingredients to MEAL_CONTAINS relation
            $N = count($selected_ingredients);

            for ($i = 0; $i < $N; $i++) {
                $add_ing_to_meal = "INSERT INTO MEAL_CONTAINS(Ing_id, Meal_id) VALUES($selected_ingredients[$i], $meal_id_str)";

                $add_ing_to_meal_result = pg_query($GLOBALS['dbconn'], $add_ing_to_meal);

                if (!$add_ing_to_meal_result) {
                    echo "An error occurred.\n";
                } 
            }     
            echo "Successfully created meal for: " . $meal_name_str . "!";
        }
        //getMeals();
    }     
}

// Displays the list of available meals in a table
function getMeals() {
  $query1 = "SELECT * FROM MEALS";

  $result = pg_query($GLOBALS['dbconn'], $query1);
  if (!$result) {
    echo "An error occurred.\n";
    exit;
  } else {
    echo "<br>";
    echo "<table style='width:100%'>";
    echo "<tr style='font-weight:bold'><td>MealID</td>" . "<td>Name</td>" . "<td>Description</td>" . "<td>Cuisine</td>" . "</tr>";
    while ($row = pg_fetch_row($result)) {
      echo "<tr><td>" . "$row[0]" . "</td><td>" . "$row[2]" . "</td><td>" . "$row[3]" . "</td><td>" . "$row[4]" . "</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "<br><iframe name='meal_order_sent_frame' height='35' width='1000' scrolling='no' src='html/user/order-meal-iframe-default.html'></iframe>";
    echo "<br><a class = 'btn btn-primary text' id='backbtn' onclick='back()''>Back</a> &nbsp;";
    echo "</div>";
    echo "</form>";
  }
}

function placeOrder() {
	
}

function reports() {
	
}

?>

<style>
  .div-center {
      text-align:center;
  }
</style>
