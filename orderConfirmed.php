<?php
session_start(); // Detect the current session
include("header.php"); // Include the Page Layout header

if (isset($_SESSION["OrderID"])) {

    echo "<div class='container p-5'>";
    echo "<div class='row'>";
    echo "<div class='col-12'>";
    echo "<div class='card border-0 shadow'>
            <div class='card-body' style='background-color: #DAAFF4; border-radius: 5px;'>
            <div class='m-auto'>
                <p style='font-weight: bold;'> Your order is on the way!</p>
            </div>";

    echo "<p>Checkout successful. Your order number is $_SESSION[OrderID]</p>";
    echo "<p>Thank you for your purchase.&nbsp;&nbsp;";
    echo "<p>Delivering To: $_SESSION[ShopperName]</p>";
    echo "<p>Order Summary:</p>";
    foreach ($_SESSION['Items'] as $key => $item) {
        $a = $item["quantity"];
        $b = $item["name"];
        echo "<p>$b x $a</p>";
    }

    if ($_SESSION["ShipCharge"] == "5") {
        echo " As you have chosen Normal Delivery, Your items will be delivered within 2 working days after your order is placed! 
        Thank you for your purchase! </br>";
    } else {
        echo " As you have chosen Express Delivery, Your items will be delivered within 24 hours. Thank you for your purchase!</br>";
    }

    echo "<br/>";
    echo "<a href='index.php' class='btn btn-primary editprofile-button'>Continue Shopping</a>";

    //unset session variables

    echo "</div>";
    echo "</div>";
    echo "</div>";
    echo "</div>";
}

include("footer.php"); // Include the Page Layout footer
?>