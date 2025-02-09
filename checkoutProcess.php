<?php
session_start();
include("header.php"); // Include the Page Layout header
include_once("myPayPal.php"); // Include the file that contains PayPal settings
include_once("db_connection.php"); // Include the file that contains the database connection settings

if ($_POST) //Post Data received from Shopping cart page.
{
    // To Do 6 (DIY): Check to ensure each product item saved in the associative
    //                array is not out of stock
    //                If out of stock, display error message and exit
    //                Else, save the shopping cart items in session variable
    foreach ($_SESSION["Items"] as $key => $item) {
        $productId = $item["productID"];

        $qry = "SELECT Quantity FROM Product WHERE ProductID = ?";

        $stmt = $conn->prepare($qry);
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        $result = $stmt->get_result();
        $stmt->close();
        $row = $result->fetch_array();


        //Check if the quantity of product that user is buying is more than the inventory list
        if ($row["Quantity"] < $item["quantity"]) {
            echo "<p style='font-weight: bold; color:red;'>We are really sorry! $item[name] is out of stock! </p> <br />";
            echo "<p style='font-weight: bold; color:red;'> Please return to shopping cart to amend your purchase. </p> <br />";
            echo "<p style='font-weight: bold;'>Thank you for your understanding! </p> <br />";
            echo "<a href='index.php'>Continue shopping</a></p>";
            include("footer.php");
            exit;
        }
    }


    $paypal_data = '';
    // Get all items from the shopping cart, concatenate to the variable $paypal_data
    // $_SESSION['Items'] is an associative array
    foreach ($_SESSION['Items'] as $key => $item) {
        $paypal_data .= '&L_PAYMENTREQUEST_0_QTY' . $key . '=' . urlencode($item["quantity"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_AMT' . $key . '=' . urlencode($item["price"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NAME' . $key . '=' . urlencode($item["name"]);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NUMBER' . $key . '=' . urlencode($item["productID"]);
    }

    //Get Form Data
    $_SESSION["BillName"] = $_POST["BillName"];
    $_SESSION["BillAddress"] = $_POST["BillAddress"];
    $_SESSION["ShipPhone"] = $_POST["ShipPhone"];
    $_SESSION["ShipEmail"] = $_POST["ShipEmail"];
    $_SESSION["BillCountry"] = "Singapore";
    $_SESSION["Message"] = $_POST["Message"];
    $_SESSION["BillPhone"] = $_POST["BillPhone"];
    $_SESSION["BillEmail"] = $_POST["BillEmail"];
    $_SESSION["DeliveryDate"] = $_POST["DeliveryDate"];
    $_SESSION["DeliveryTime"] = $_POST["DeliveryTime"];

    // To Do 1A: Compute GST amount 7% for Singapore, round the figure to 2 decimal places
    // $_SESSION["Tax"] = round($_SESSION["SubTotal"] * 0.07, 2);
    $_SESSION["Tax"] = round($_SESSION["SubTotal"] * ($_SESSION["TaxFromCurrentYear"] / 100), 2);

    // To Do 1B: Compute Shipping charge - S$2.00 per trip
    if ($_SESSION["ModeOfDelivery"] == "Normal") {
        $_SESSION["ShipCharge"] = 5.00;
    } else {
        if ($_SESSION["Waived"] == 1) {
            $_SESSION["ShipCharge"] = 0.00;
        } else {
            $_SESSION["ShipCharge"] = 10.00;
        }
    }


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
        '&BRANDNAME=' . urlencode("BaobeiComfort") .
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

        ?>
        <script>

            //Redirect to the home page
            window.location = "<?php echo $paypalurl; ?>";
        </script>
        <?php
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
        //Update stock inventory in product table after successful checkout
        //Retrieve all the products from the shopping cart, and reduce the stock quantity by 1 in the product table by product purchased
        foreach ($_SESSION['Items'] as $key => $item) {
            $quantity = $item["quantity"];
            $productId = $item["productID"];
            $sql = "UPDATE product SET Quantity = Quantity -?
					 WHERE productId =?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $quantity, $productId);
            $stmt->execute();
            $stmt->close();
        }
        // End of To Do 5

        // To Do 2: Update shopcart table, close the shopping cart (OrderPlaced=1)
        $total = $_SESSION["SubTotal"] + $_SESSION["Tax"] + $_SESSION["ShipCharge"];
        $qry = "UPDATE shopcart SET OrderPlaced=1, Quantity=?,
					SubTotal=?, ShipCharge=?, Tax=?, Total=?
				WHERE ShopCartId=?";
        $stmt = $conn->prepare($qry);
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
						 ShipEmail, ShipPhone, ShopCartId, BillName, BillAddress, BillCountry, BillPhone, BillEmail, DeliveryDate, DeliveryTime, DeliveryMode, Message) 
					VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($qry);
            $stmt->bind_param(
                "sssssisssssssss",
                $ShipName,
                $ShipAddress,
                $ShipCountry,
                $ShipEmail,
                $_SESSION["ShipPhone"],
                $_SESSION["Cart"],
                $_SESSION["BillName"],
                $_SESSION["BillAddress"],
                $_SESSION["BillCountry"],
                $_SESSION["BillPhone"],
                $_SESSION["BillEmail"],
                $_SESSION["DeliveryDate"],
                $_SESSION["DeliveryTime"],
                $_SESSION["ModeOfDelivery"],
                $_SESSION["Message"]
            );

            $stmt->execute();
            $stmt->close();
            $qry = "SELECT LAST_INSERT_ID() AS OrderId";
            $result = $conn->query($qry);
            $row = $result->fetch_array();
            $_SESSION["OrderID"] = $row["OrderId"];
            // End of To Do 3

            $conn->close();

            // To Do 4A: Reset the "Number of Items in Cart" session variable to zero.
            $_SESSION["NumCartItem"] = 0;

            // To Do 4B: Clear the session variable that contains Shopping Cart ID.
            unset($_SESSION["Cart"]);

            // To Do 4C: Redirect shopper to the order confirmed page.
            ?>
            <script>

                //Redirect to the home page
                window.location = "orderconfirmed.php";
            </script>
            <?php

            // exit;
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