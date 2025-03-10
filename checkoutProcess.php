<?php
session_start();
include("header.php"); // Include the Page Layout header
include_once("myPayPal.php"); // Include the file that contains PayPal settings
include_once("db_connection.php");

if ($_POST) //Post Data received from Shopping cart page.
{
    // To Do 6 (DIY): Check to ensure each product item saved in the associative
    //                array is not out of stock
    $qry = "SELECT ProductID, Quantity, ProductTitle, IF((Quantity - ? > -1) , 'Available','OutOfStock') AS Result 
	FROM product WHERE ProductID IN (SELECT ProductID FROM shopcartitem WHERE ShopCartID = ?)";
    //Returns 'Available' if quantity is 0 and above after subtraction and 'OutOfStock' if quantity becomes negative after subtraction

    echo "<h3 style='color:red;'>Unable to fulfill these orders:</h3> <br/>"; //Can Only be seen if order fails, displays header to alert user that order has failed

    foreach ($_SESSION['Items'] as $key => $item) {

        $intvalue = intval(json_encode($item["quantity"])); #Converting to int for sql comparison 
        $stmt = $conn->prepare($qry);
        $stmt->bind_param("ii", $intvalue, $_SESSION["Cart"]);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_array()) {
            if ($row["Result"] == "OutOfStock" && $row["ProductID"] == $item["productId"]) { //If Product is out of stock
                $pID = $row['ProductID'];
                $pTitle = $row['ProductTitle'];
                $quantityInStock = $row['Quantity'];
                echo "Product $pID: <u><b>$pTitle</b></u> is out of stock! <br />";
                echo "In stock: <b><u> $quantityInStock</u></b> | You ordered: <b><u> $intvalue </u></b> <br /><br />"; //Show current stock and user's order quantity
                $exit = true;
            }
        }
    }

    if ($exit == true) { //Display message and exit checkout process of any item is out of stock
        echo "<b> Please return to <a href='cart.php'>shopping cart</a> to amend your purchase.<br /> </b>";
        $stmt->close();
        include("footer.php");
        exit;
    }

    $stmt->close();
    // End of To Do 6

    $paypal_data = '';
    // Get all items from the shopping cart, concatenate to the variable $paypal_data
    // $_SESSION['Items'] is an associative array
    foreach ($_SESSION['Items'] as $key => $item) {
        $paypal_data .= '&L_PAYMENTREQUEST_0_QTY' . $key . '=' . urlencode($item["quantity"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_AMT' . $key . '=' . urlencode($item["price"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NAME' . $key . '=' . urlencode($item["name"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NUMBER' . $key . '=' . urlencode($item["productId"]);
    }

    // To Do 1A: Compute GST amount 7% for Singapore, round the figure to 2 decimal places
    $_SESSION["Tax"] = round($_SESSION["SubTotal"] * 0.07, 2);

    // To Do 1B: Compute Shipping charge - S$2.00 per trip
    $_SESSION["ShipCharge"] = 2.00;

    //Data to be sent to PayPal
    $padata = '&CURRENCYCODE=' . urlencode($PayPalCurrencyCode) .
        '&PAYMENTACTION=Sale' .
        '&ALLOWNOTE=1' .
        '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($PayPalCurrencyCode) .
        '&PAYMENTREQUEST_0_AMT=' . urlencode($_SESSION["SubTotal"] +
        $_SESSION["Tax"] +
        $_SESSION["ShipCharge"]) .
        '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode($_SESSION["SubTotal"]) .
        '&PAYMENTREQUEST_0_SHIPPINGAMT=' . urlencode($_SESSION["ShipCharge"]) .
        '&PAYMENTREQUEST_0_TAXAMT=' . urlencode($_SESSION["Tax"]) .
        '&BRANDNAME=' . urlencode("BabyBoo") .
        $paypal_data .
        '&RETURNURL=' . urlencode($PayPalReturnURL) .
        '&CANCELURL=' . urlencode($PayPalCancelURL);

    //We need to execute the "SetExpressCheckOut" method to obtain paypal token
    $httpParsedResponseAr = PPHttpPost(
        'SetExpressCheckout',
        $padata,
        $PayPalApiUsername,
        $PayPalApiPassword,
        $PayPalApiSignature,
        $PayPalMode
    );

    //Respond according to message we receive from Paypal
    if (
        "SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) ||
        "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])
    ) {
        if ($PayPalMode == 'sandbox')
            $paypalmode = '.sandbox';
        else
            $paypalmode = '';

        //Redirect user to PayPal store with Token received.
        $paypalurl = 'https://www' . $paypalmode .
            '.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' .
            $httpParsedResponseAr["TOKEN"] . '';
        header('Location: ' . $paypalurl);
    } else {
        //Show error message
        echo "<div style='color:red'><b>SetExpressCheckOut failed : </b>" .
            urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . "</div>";
        echo "<pre>" . print_r($httpParsedResponseAr) . "</pre>";
    }
}

//Paypal redirects back to this page using ReturnURL, We should receive TOKEN and Payer ID
if (isset($_GET["token"]) && isset($_GET["PayerID"])) {
    //we will be using these two variables to execute the "DoExpressCheckoutPayment"
    //Note: we haven't received any payment yet.
    $token = $_GET["token"];
    $playerid = $_GET["PayerID"];
    $paypal_data = '';

    // Get all items from the shopping cart, concatenate to the variable $paypal_data
    // $_SESSION['Items'] is an associative array
    foreach ($_SESSION['Items'] as $key => $item) {
        $paypal_data .= '&L_PAYMENTREQUEST_0_QTY' . $key . '=' . urlencode($item["quantity"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_AMT' . $key . '=' . urlencode($item["price"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NAME' . $key . '=' . urlencode($item["name"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NUMBER' . $key . '=' . urlencode($item["productId"]);
    }

    //Data to be sent to PayPal
    $padata = '&TOKEN=' . urlencode($token) .
        '&PAYERID=' . urlencode($playerid) .
        '&PAYMENTREQUEST_0_PAYMENTACTION=' . urlencode("SALE") .
        $paypal_data .
        '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode($_SESSION["SubTotal"]) .
        '&PAYMENTREQUEST_0_TAXAMT=' . urlencode($_SESSION["Tax"]) .
        '&PAYMENTREQUEST_0_SHIPPINGAMT=' . urlencode($_SESSION["ShipCharge"]) .
        '&PAYMENTREQUEST_0_AMT=' . urlencode($_SESSION["SubTotal"] +
        $_SESSION["Tax"] +
        $_SESSION["ShipCharge"]) .
        '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode($PayPalCurrencyCode);

    //We need to execute the "DoExpressCheckoutPayment" at this point 
    //to receive payment from user.
    $httpParsedResponseAr = PPHttpPost(
        'DoExpressCheckoutPayment',
        $padata,
        $PayPalApiUsername,
        $PayPalApiPassword,
        $PayPalApiSignature,
        $PayPalMode
    );

    //Check if everything went ok..
    if (
        "SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) ||
        "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])
    ) {
        // To Do 5 (DIY): Update stock inventory in product table 
        //                after successful checkout
        $qry = "UPDATE product as p INNER JOIN shopcartitem as s ON p.ProductID = s.ProductID 
				SET p.Quantity = (p.Quantity - s.Quantity) 
				WHERE p.ProductID IN (SELECT s.ProductID WHERE ShopCartID = ?)";

        $stmt = $conn->prepare($qry);
        $stmt->bind_param("i", $_SESSION["Cart"]);
        $stmt->execute();
        $stmt->close();

        // End of To Do 5

        // To Do 2: Update shopcart table, close the shopping cart (OrderPlaced=1)
        $total = $_SESSION["SubTotal"] + $_SESSION["Tax"] + $_SESSION["ShipCharge"];
        $qry = "UPDATE shopcart SET OrderPlaced=1, Quantity=?,
        		SubTotal=?, ShipCharge=?, Tax=?, Total=?
        		WHERE ShopCartID=?";
        $stmt = $conn->prepare($qry);
        // "i" - integer, "d" double
        $stmt->bind_param(
            "iddddi",
            $_SESSION["NumCartItem"],
            $_SESSION["SubTotal"],
            $_SESSION["ShipCharge"],
            $_SESSION["Tax"],
            $total,
            $_SESSION["Cart"]
        );
        $stmt->execute();
        $stmt->close();
        // End of To Do 2

        //We need to execute the "GetTransactionDetails" API Call at this point 
        //to get customer details
        $transactionID = urlencode(
            $httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]
        );
        $nvpStr = "&TRANSACTIONID=" . $transactionID;
        $httpParsedResponseAr = PPHttpPost(
            'GetTransactionDetails',
            $nvpStr,
            $PayPalApiUsername,
            $PayPalApiPassword,
            $PayPalApiSignature,
            $PayPalMode
        );

        if (
            "SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) ||
            "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])
        ) {
            //gennerate order entry and feed back orderID information
            //You may have more information for the generated order entry 
            //if you set those information in the PayPal test accounts.

            $ShipName = addslashes(urldecode($httpParsedResponseAr["SHIPTONAME"]));

            $ShipAddress = urldecode($httpParsedResponseAr["SHIPTOSTREET"]);
            if (isset($httpParsedResponseAr["SHIPTOSTREET2"]))
                $ShipAddress .= ' ' . urldecode($httpParsedResponseAr["SHIPTOSTREET2"]);
            if (isset($httpParsedResponseAr["SHIPTOCITY"]))
                $ShipAddress .= ' ' . urldecode($httpParsedResponseAr["SHIPTOCITY"]);
            if (isset($httpParsedResponseAr["SHIPTOSTATE"]))
                $ShipAddress .= ' ' . urldecode($httpParsedResponseAr["SHIPTOSTATE"]);
            $ShipAddress .= ' ' . urldecode($httpParsedResponseAr["SHIPTOCOUNTRYNAME"]) .
                ' ' . urldecode($httpParsedResponseAr["SHIPTOZIP"]);

            $ShipCountry = urldecode(
                $httpParsedResponseAr["SHIPTOCOUNTRYNAME"]
            );

            $ShipEmail = urldecode($httpParsedResponseAr["EMAIL"]);

            // To Do 3: Insert an Order record with shipping information
            //          Get the Order ID and save it in session variable.
            $qry = "INSERT INTO orderdata (ShipName, ShipAddress, ShipCountry,
                                ShipEmail, ShopCartID)
       				VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($qry);
            // "i" - integer, "s" - string
            $stmt->bind_param(
                "ssssi",
                $ShipName,
                $ShipAddress,
                $ShipCountry,
                $ShipEmail,
                $_SESSION["Items"]
            );
            $stmt->execute();
            $stmt->close();
            $qry = "SELECT LAST_INSERT_ID() AS OrderID";
            $result = $conn->query($qry);
            $row = $result->fetch_array();
            $_SESSION["OrderID"] = $row["OrderID"];
            // End of To Do 3

            $conn->close();

            // To Do 4A: Reset the "Number of Items in Cart" session variable to zero.
            $_SESSION["NumCartItem"] = 0;

            // To Do 4B: Clear the session variable that contains Shopping Cart ID.
            unset($_SESSION["Cart"]);

            // To Do 4C: Redirect shopper to the order confirmed page.
            header("Location: orderConfirmed.php");
            exit;
        } else {
            echo "<div style='color:red'><b>GetTransactionDetails failed:</b>" .
                urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
            echo "<pre>" . print_r($httpParsedResponseAr) . "</pre>";
            $conn->close();
        }
    } else {
        echo "<div style='color:red'><b>DoExpressCheckoutPayment failed : </b>" .
            urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]) . '</div>';
        echo "<pre>" . print_r($httpParsedResponseAr) . "</pre>";
    }
}

include("footer.php"); // Include the Page Layout footer
?>