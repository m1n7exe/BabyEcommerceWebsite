<?php
session_start();
include("header.php"); // Include the Page Layout header

if (!isset($_SESSION["ShopperID"])) { // Check if user logged in 
    // redirect to login page if the session variable shopperid is not set
    header("Location: login.php");
    exit;
}

include_once("db_connection.php");

// Retrieve from database and display shopping cart in a table
$qry = "SELECT *, (Price*Quantity) AS Total
            FROM ShopCartItem WHERE ShopCartID=?";
$stmt = $conn->prepare($qry);
$stmt->bind_param("i", $_SESSION["Cart"]); //"i" - integer
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    // To Do 2 (Practical 4): Format and display 
    // the page header and header row of shopping cart 
    echo "<p class='page-title' style='text-align:center'>Choose your delivery mode!</p>";
    echo "<div class='table-responsive' >"; // Bootstrap responsive table
    echo "<table class='table table-hover'>"; // Start of table
    echo "<thead class='cart-header'>"; // Start of table's header section
    echo "<tr>"; // Start of header row
    echo "<th width='650px'>Item</th>";
    echo "<th width='90px'>Price (S$)</th>";
    echo "<th width='60px'>Quantity</th>";
    echo "<th width='120px'>Total (S$)</th>";
    echo "<th>&nbsp;</th>";
    echo "</tr>"; // End of header row
    echo "</thead>"; // End of table's header section

    // Declare an array to store the shopping cart items in session variable 
    $_SESSION["Items"] = array();

    // Display the shopping cart content
    $subTotal = 0; // Declare a variable to compute subtotal before tax
    echo "<tbody>"; // Start of table's body section
    while ($row = $result->fetch_array()) {
        echo "<tr>";
        echo "<td style='width: 50 %'> $row[Name]<br />";
        echo "Product ID: $row[ProductID]</td>";
        $formattedPrice = number_format($row["Price"], 2);
        echo "<td>$formattedPrice</td>";
        echo "<td> $row[Quantity]</td>";
        $formattedTotal = number_format($row["Total"], 2);
        echo "<td>$formattedTotal</td>";
        echo "</tr>";

        // Store the shopping cart items in session variable as an associate array
        $_SESSION["Items"][] = array(
            "productID" => $row["ProductID"],
            "name" => $row["Name"],
            "price" => $row["Price"],
            "quantity" => $row["Quantity"]
        );

        // Accumulate the running sub-total
        $subTotal += $row["Total"];
    }
    echo "</tbody>"; // End of table's body section
    echo "</table>"; // End of table
    echo "</div>"; // End of Bootstrap responsive table


    // Display the subtotal at the end of the shopping cart
    echo "<p style='text-align:right; font-size:20px; padding-right:20px'>
				Subtotal = S$" . number_format($subTotal, 2);
    $_SESSION["SubTotal"] = round($subTotal, 2);

    //Displaying and getting input for Mode of Delivery
    echo "<br/>";
    echo "<td>";
    echo "<form method = 'post' style='padding-left:20px'>";
    //after click submit button, the default dropdown value will be retained
    //display a label for the dropdown list
    echo "<label for='deliveryMode'>Mode of Delivery:</label>";
    echo "<select name='mod' style='width: 200px; height: 30px;'>";
    echo "<option value=''>Select Mode of Delivery</option>";
    echo "<option value='Normal'>Normal Delivery  ($5.00)</option>";
    echo "<option value='Express'>Express Delivery ($10.00)</option>";
    echo "</select>";
    echo "<input type='submit' name='submit' style='width: 100px; height: 30px;'>";
    echo "</form>";
    echo "</td>";
    echo "</p>";


    // Retrive gst from database
    $qry2 = "SELECT * FROM gst WHERE EffectiveDate < curdate()
        ORDER BY EffectiveDate DESC LIMIT 1";
    $stmt = $conn->prepare($qry2);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array()) {
            $tax = $row["TaxRate"];
        }
    }

    // Checking Mode of Delivery
    if (isset($_POST['mod'])) {
        //If Mode Of Delivery is Normal
        if ($_POST['mod'] == "Normal") {
            $_SESSION["ModeOfDelivery"] = "Normal";
            $_SESSION["TaxFromCurrentYear"] = $tax;

            $taxAmount = round($_SESSION["SubTotal"] * ($_SESSION["TaxFromCurrentYear"] / 100), 2);


            echo "<div class='container p-5'>";
            echo "<div class='row'>";
            echo "<div class='col-12'>";
            echo "<div class='card border-0 shadow'>
                            <div class='card-body' style='background-color: #DAAFF4; border-radius: 5px;'>
                                <div class='m-auto'>
                                    <h2 class='loginheader text-center' style='color: #4E004A;'>Payment Details</h2>
                                </div>";
            echo "<p style ='font-size:20px'> You have chosen the Normal Delivery for your Order!<p>";
            echo " <p style = 'font-size:20px'> Sub Total: S$" . number_format($_SESSION["SubTotal"], 2);
            echo "<br/>";
            echo "Delivery Fee: S$5";
            echo "<br/>";
            echo "GST (Tax %) : $tax %";
            echo "<br/>";

            echo "GST: S$ $taxAmount";
            echo "<br/>";
            // Adding Delivery Fee and Tax Amount to Total Amount
            $totalAmount = $_SESSION["SubTotal"] + 5 + $taxAmount;

            echo "<p style='font-size:30px; font-weight: bold;'> 
                Total = S$ " . $totalAmount . "</p>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";

            // Displaying Delivery Details Form
            echo "
                <div class='container p-5'>
                <div class='row'>
                <div class='col-12'>
                        <div class='card border-0 shadow'>
                            <div class='card-body' style='background-color: #DAAFF4; border-radius: 5px;'>
                                <div class='m-auto'>
                                    <h2 class='loginheader text-center' style='color: #4E004A;'>Delivery Details</h2>
                                </div>
                                <form action='checkoutProcess.php' method='post' >
                                    <p style='font-weight: bold;'>* Shipping Information</p>
                                    <input type='text' name='ShipPhone' id='ShipPhone' class='form-control my-4 py-2' placeholder='Shipping Phone (65) 1234 5678' required/>
				                    <input type='text' name='ShipEmail' id='ShipEmail' class='form-control my-4 py-2' placeholder='Shipping Email (ecader@gmail.com)' required/>
                                    <p style='font-weight: bold;'>* Delivery Date & Time</p>
				                    <input type='date' name='DeliveryDate' id='DeliveryDate' class='form-control my-4 py-2' placeholder='Delivery Date' required/>
				                    <input type='text' name='DeliveryTime' id='DeliveryTime' class='form-control my-4 py-2' placeholder='Delivery Time (1pm-3pm)' required/>
                                    <p style='font-weight: bold;'>* Message</p>
				                    <input type='text' name='Message' id='Message' class='form-control my-4 py-2' placeholder='Message (Happy New Year!)' required/>
                                    <p style='font-weight: bold;'>* Billing Information</p>
                                    <input type='text' name='BillName' id='BillName' class='form-control my-4 py-2' placeholder='Billing Name' required/>
				                    <input type='text' name='BillAddress' id='BillAddress' class='form-control my-4 py-2' placeholder='Billing Address (Yung An Road,Block 334)' required/>
				                    <input type='text' name='BillPhone' id='BillPhone' class='form-control my-4 py-2' placeholder='Billing Phone (65) 1234 5678' required/>
				                    <input type='text' name='BillEmail' id='BillEmail' class='form-control my-4 py-2' placeholder='Billing Email (ecader@gmail.com)' required/>

                                <div class='text-center mt-3'>
                                <input type='image' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif'>
                                </div>
                        </form>
                        </div>
                        </div>
                        </div>
                    </div>
                 </div>";

        }
        // If Mode of Delivery is not normal, Mode of Delivery = Express
        else {
            $_SESSION["ModeOfDelivery"] = "Express";
            $_SESSION["TaxFromCurrentYear"] = $tax;

            $taxAmount = round($_SESSION["SubTotal"] * ($_SESSION["TaxFromCurrentYear"] / 100), 2);

            echo "<div class='container p-5'>";
            echo "<div class='row'>";
            echo "<div class='col-12'>";
            echo "<div class='card border-0 shadow'>
                            <div class='card-body' style='background-color: #DAAFF4; border-radius: 5px;'>
                                <div class='m-auto'>
                                    <h2 class='loginheader text-center' style='color: #4E004A;'>Payment Details</h2>
                                </div>";
            echo "<p style ='font-size:20px'> You have chosen the Express Delivery for your Order!<p>";
            echo " <p style = 'font-size:20px'> Sub Total: S$" . number_format($_SESSION["SubTotal"], 2);
            echo "<br/>";

            //if subtotal is more than 200, delivery fee is 0
            $expressFee = 0;
            if ($_SESSION["SubTotal"] > 200) {
                echo "Delivery Fee: S$0.00";
                echo "<br/>";
                $_SESSION["Waived"] = 1;
                $expressFee = 0;
            } else {
                echo "Delivery Fee: S$10";
                echo "<br/>";
                $_SESSION["Waived"] = 0;
                $expressFee = 10;
            }
            echo "GST (Tax %) : $tax %";
            echo "<br/>";

            echo "GST: S$ $taxAmount";
            echo "<br/>";
            // Adding Delivery Fee and Tax Amount to Total Amount
            $totalAmount = $_SESSION["SubTotal"] + $expressFee + $taxAmount;

            echo "<p style='font-size:30px; font-weight: bold;'> 
                Total = S$ " . $totalAmount . "</p>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";

            // Displaying Delivery Details Form
            echo "
                <div class='container p-5'>
                <div class='row'>
                <div class='col-12'>
                        <div class='card border-0 shadow'>
                            <div class='card-body' style='background-color: #DAAFF4; border-radius: 5px;'>
                                <div class='m-auto'>
                                    <h2 class='loginheader text-center' style='color: #4E004A;'>Delivery Details</h2>
                                </div>
                                <form action='checkoutProcess.php' method='post' >
                                    <p style='font-weight: bold;'>* Shipping Information</p>
                                    <input type='text' name='ShipPhone' id='ShipPhone' class='form-control my-4 py-2' placeholder='Shipping Phone (65) 1234 5678' required/>
				                    <input type='text' name='ShipEmail' id='ShipEmail' class='form-control my-4 py-2' placeholder='Shipping Email (ecader@gmail.com)' required/>
                                    <p style='font-weight: bold;'>* Delivery Date & Time</p>
				                    <input type='date' name='DeliveryDate' id='DeliveryDate' class='form-control my-4 py-2' placeholder='Delivery Date' required/>
				                    <input type='text' name='DeliveryTime' id='DeliveryTime' class='form-control my-4 py-2' placeholder='Delivery Time (1pm-3pm)' required/>
                                    <p style='font-weight: bold;'>* Message</p>
				                    <input type='text' name='Message' id='Message' class='form-control my-4 py-2' placeholder='Message (Happy New Year!)' required/>
                                    <p style='font-weight: bold;'>* Billing Information</p>
                                    <input type='text' name='BillName' id='BillName' class='form-control my-4 py-2' placeholder='Billing Name' required/>
				                    <input type='text' name='BillAddress' id='BillAddress' class='form-control my-4 py-2' placeholder='Billing Address (Yung An Road,Block 334)' required/>
				                    <input type='text' name='BillPhone' id='BillPhone' class='form-control my-4 py-2' placeholder='Billing Phone (65) 1234 5678' required/>
				                    <input type='text' name='BillEmail' id='BillEmail' class='form-control my-4 py-2' placeholder='Billing Email (ecader@gmail.com)' required/>

                                <div class='text-center mt-3'>
                                <input type='image' src='https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif'>
                                </div>
                        </form>
                        </div>
                        </div>
                        </div>
                    </div>
                 </div>";

        }

        // Getting Name for displaying at orderConfirmed.php
        $qry3 = "SELECT * 
            FROM Shopper WHERE ShopperID=?";
        $stmt = $conn->prepare($qry3);
        $stmt->bind_param("i", $_SESSION["ShopperID"]); //"i" - integer
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array()) {
                $SESSION["ShopperName"] = $row["Name"];
            }

        }

    }


    echo "</div>"; // End of container
    echo "</br>";
    include("footer.php"); // Include the Page Layout footer
}
$conn->close(); // Close database connection
?>