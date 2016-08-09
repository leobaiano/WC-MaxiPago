<?php
// This is an autoloader for the maxiPago! SDK.
// If you are not using a global autoloader do the following
// before any other maxiPago files:
//
//     require_once "<path>/maxipago/Autoload.php"

function Maxi_Pago_Autoload( $class_name ) {
    $file_name = '';

    if ($class_name === "KLogger") {
    	$file_name = $class_name . ".php";
    } else {
    	if ( false !== strpos ( $class_name, 'maxiPago_' ) ) {
    		$file_name = substr( $class_name, 9 ) . ".php";
    	}
   	}

   	if ( !empty( $file_name ) ) {
   		require $file_name;
   	}
}
spl_autoload_register( 'Maxi_Pago_Autoload' );
