<!DOCTYPE HTML>
<HTML>
<HEAD>

<!--  -->
<?php



$servername = "sql213.epizy.com"; /* database credentials*/ 
$username = "epiz_32503928";
$password = "dYNO0vwUw18wISg";
$dbname = "epiz_32503928_dk313";
$key = "pArTY0nArCHSTr33t93022+FEZZIGfri"; /* encryption key*/ 
/* double checked that one cannot pull this information by downloading the page or look at it in inspect, but maybe I will try to come up with a better solution for storing these credentials */

$db_con = new mysqli($servername, $username, $password, $dbname);

if ($db_con->connect_error) { /* outputs the error if something goes wrong*/ 
$con_state = "connection failed";
$er_code = $db_con->connect_error;
};

$er_report = "problems included; <br>"; /* as problems are found, descriptions are concatenated onto this string*/
$payment_successful = true; /* pretty self explanatory, if something goes wrong, this guy gets tripped */
$tst_mssg = ""; /* leaving in for now in case i still need to test something*/

function validate_info() { /* runs payment info through a series of checks to see if it is valid. O(c)*/

  global $er_report, $payment_successful;

  $current_date_Ym = date("Y") . "-" . date("m");

  if((int)$_POST["card_payment"] > 99 ) { /* determined there was no reason in this hypothetical scenario that anyone should be spending more than $99*/
        $er_report .= "payment too large <br>"; /* and frankly it's easier to catch repetitive purchases in progress */
        $payment_successful = false;
  };
  if(!check_if_valid_name($_POST["card_name"])) {/* checks if a given name is valid */
    $er_report .= "Cardname not valid <br>";
    $payment_successful = false;
  };
  if(!check_if_valid_provider($_POST["card_provider"])) { /* checks if a given provider is valid */
    $er_report .= "Provider not valid <br>";
    $payment_successful = false;
  };
  if((int)$_POST["card_number"] > 9999999999999999.00 ) { /* card numbers aren't longer than 16 digits, though thanks to how html and php handles numbers, they can be shorter than 16 digits since leading zeros are removed */
    $er_report .= "card number not valid <br>";
    $payment_successful = false;
  };
  if($_POST["card_expiration_date"]<=$current_date_Ym) { /* checks if the card is expired */
    $er_report .= "card has expired <br>";
    $payment_successful = false;
  };
  if((int)$_POST["card_security_code"]>999.00) { /* cvcs aren't longer than 3 digits, but can have leading zeroes, so I have to allow numbers smaller than 3 digits */

    if(!(strtolower($_POST["card_provider"]) === "american express" && ($_POST["card_security_code"] <= 9999.00))) { /* except amex, who are special little princesses with 4 digit cvcs */

    $er_report .= "security code too large <br>"; 
    $payment_successful = false;

          };
  };
  if(!check_if_valid_country($_POST["card_country"])) { /* checks if a given coutry is valid */
    $er_report .= "not a valid country <br>";
    $payment_successful = false;
  };
  if((int)$_POST["card_zip"]>99999) {  /* once again, any zip larger than 6 digits is not valid (sort of, technically zip codes are longer than 6 digits but i dont feel like dealing with that problem yet) */
    $er_report .= "not a valid zip <br>"; /* but i still have to account for leading zeroes */
    $payment_successful = false;
  };

  if(!$payment_successful) {return;}; /* if something has gone wrong, the program should not proceed onward*/
  /* otherwise everything is successful and the program tries to insert the data in the database, numbers that arent payment amount are coerced into integers since that's what they should be, and i don't want funny business */
  payment_valid($_POST["card_payment"],$_POST["card_name"],$_POST["card_provider"],(int)$_POST["card_number"], $_POST["card_expiration_date"],(int)$_POST["card_security_code"],$_POST["card_country"],(int)$_POST["card_zip"] );

};

function check_if_valid_country($country) { /* checks a given string against a valid list of countries. O(c)*/

  $c_rray = array("Afghanistan", "Albania", "Algeria", "America", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "United States Minor Outlying Islands", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
      /* countries array credit to 'DHS' @ https://gist.github.com/DHS/1340150
      reminder to return to this link because a commenter posted an array that contains country codes as well*/

  
    foreach ( $c_rray as $valid_country) {

      if(strtolower($country) == strtolower($valid_country)) {return true;};
    };

  return false;
};

function check_if_valid_name($name) {/* checks if a name is valid by looking for special characters, and rejecting names with special characters. O(c)*/
/* this function is explicitly for stopping sql injection since name is a text field. 
unsurprisingly, confirming a name is real is hard. did you know that 'catchyn' is a real name?
and i don't have time to filter out every permutation of 'mr poopybutt'
i cant even remove phrases like 'insert' or 'update' since some poor soul out there might have those as part of their name
but at least removing special characters tends to break sql statements
i pity the poor soul whose name is '&&&&&&&&&'
*/
  if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $name))
    {
        return false;
    }
    /* special character checker courtesy of 'chigley' @ https://stackoverflow.com/questions/3938021/how-to-check-for-special-characters-php */

  return true;
};

function check_if_valid_provider($provider) { /* checks if a given credit card company name is valid. O(c)*/
  $p_rray = array("visa","american express","mastercard","jcb","diners club","discover","australian bankcard","unionpay"); 
      /* i made my own list of most common credit card company names, other names will be added as needed*/
  foreach ( $p_rray as $valid_provider) {

    if(strtolower($provider) == strtolower($valid_provider)) {return true;};
  };

return false;

};

function payment_valid($amnt,$name,$prov,$num, $date,$sc,$count,$zip ) { /* if everything checks out, then the information is ready to be put in the database table. O(c)*/
/* any numerical value that isn't a payment amount should be an integer */
  global $db_con, $er_report, $payment_successful/*, $tst_mssg */;


  /* encrypts and authenticates card number and security code*/
  $v_s = " ".$amnt.", '".$name."', '".$prov."', '".encryption($num)."', '".$date."-01', '".encryption($sc)."', '".$count."', ".$zip." ";
  $sql_ul = "INSERT INTO payments_record (amount, cardholder, card_provider, cardnumber_enc, exp_date, security_code_enc, country, zip)
  VALUES ($v_s);";

  /*$tst_mssg = $sql_ul; keeping for now, in case I have to check if something went wrong*/


  $upload = $db_con->query($sql_ul);

  if(!$upload) {$er_report .= "DB connection error"; return;};

  $payment_successful = true;
  return;


};

function encryption($pt) {/* Encrypts a string given as a parameter with the AES-128-CBC cipher, then creates an hmac for authentication, and optionally concatenates the initialiazation vector depending on the version. O(n)*/
  /* code courtesy of Michael Sabal @ 5.1.2 Using the OpenSSL Library in PHP to Encrypt Sensitive Data (video 10 minutes, reflection 5 minutes) https://elearning.cairn.edu/mod/hotquestion/view.php?id=659936 */
  global $key;
  if(version_compare(phpversion(),'5.3.3','>=')) {

    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw=openssl_encrypt($pt,$cipher,$key,$options=OPENSSL_RAW_DATA,$iv);
    $hmac = hash_hmac("sha256",$ciphertext_raw,$key,$as_binary=true);
    $ct= base64_encode($iv.$hmac.$ciphertext_raw);

    return $ct
  ;} else {
    $cipher="AES-128-CBC";
    $ciphertext_raw=openssl_encrypt($pt,$cipher,$key,$options=OPENSSL_RAW_DATA);
    $hmac = hash_hmac("sha256",$ciphertext_raw,$key,$as_binary=true);
    $ct= base64_encode($hmac.$ciphertext_raw);
    return $ct
    ;};


  
};
?>


<TITLE>Checkout Page</TITLE>
<META charset="UTF-8">
  <META name="description" content="Payment Portal Checkout">
  <!-- you can rename the file, but never change the description once it's set -->
  <META name="keywords" content="HTML, CSS, JavaScript">
  <META name="author" content="Destry Krepps">
  <META name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- taken from w3 schools -->

<H1>  Thanks For Shopping With Us!  </H1>

</HEAD>
<BODY>

<DIV class = "main content">
<?php

validate_info();
if($payment_successful) {echo "Checkout Success $tst_mssg";} else {echo "Something went wrong <br> $er_report";};

?>


</DIV>
<br>
<A href="index.php">Go Back</A>
</BODY>
</HTML>