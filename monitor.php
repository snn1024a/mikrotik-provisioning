<?php
	$user="admin";
	$pass="";
	$fileLeases="/var/www/prov/dnsmasq.leases";
	$ipaddresses="/var/www/prov/ipaddresses";
	require('/opt/api/rosapi.php');

	$iparray=array();
	$timearray=array();
	
	$API = new routeros_api();
	$API->debug = false;
	$leasesFile = file_get_contents($fileLeases);
	$leasesArray = explode("\n", $leasesFile);
	$leases = getArrayFromLines($leasesArray, " ");
        function getArrayFromLines($array, $delimiter) {
          $resultArray = array();
          
          for($i=0; $i<count($array); $i++){
	  
	    if(empty($array[$i])) {
	      continue;
	    }
	    
	    $word = explode($delimiter, $array[$i]);

	    array_push($resultArray, $word);
	  }
	  
	  return $resultArray;
	}

	function getArrayFromArrayElement($array, $el) {
            $resultArray = array();

            for($i=0; $i<count($array); $i++){
	  	if(empty($array[$i])) {
	  	    	continue;
	     	}
	       	
		array_push($resultArray, $array[$i][$el]);
	     }
	  
	     return $resultArray;
	}


	
$iparray = (getArrayFromArrayElement($leases, 2));
file_put_contents($ipaddresses, $iparray);
#die;
	echo count($iparray)." APs sins ".gmdate('H:i:s', min($timearray));
	echo "<br/>";
	echo "parallel-ssh -h /var/www/prov/ip -l $user -o /var/www/prov/tmp/ /system identity set name=MTK<br/>";
        echo "cssh -l $user ";
	
	$lastarray=array();
	
	for($j=0; $j<count($iparray); $j++){ 
	  echo $iparray[$j]." ";
	  if ($API->connect($iparray[$j], $user, $pass)) {
	    $sysarray=array();
	    $sysarray["#"]= $j+1;	
	    $sysarray[Hostname] = getIdentity($API);
	    $sysarray[Ethernet] = getInterfaces($API);
	    $sysarray[Resource] = getResource($API);	
	    $sysarray[IP] = getIpAddresses($API);
	    $sysarray[BrPorts] = getPortBridge($API);
	    $sysarray[WiPack] = getWirelessPackInCharge($API);
	    $sysarray[CapI] = getCapInterfaces($API);
	    //$API->comm("/system/script/run", array('number'=>'0'));
	    $API->disconnect();  
	  }	 

	  array_push($lastarray, $sysarray);

	}
	#print_r($lastarray);

	function getIdentity($API) {
	  $ARRAY = $API->comm('/system/identity/print');
	  $sysname = $ARRAY[0]['name'];
	  return $sysname;
	}
	function getResource($API) {
	  $ARRAY = $API->comm('/system/resource/print');
	  $version = $ARRAY[0]['version'];
	  $archname = $ARRAY[0]['architecture-name'];
	  $boardname = $ARRAY[0]['board-name'];
	  $vearbo = $version."; ".$archname."; ".$boardname."; ";
	  return $vearbo;
	}
	function getInterfaces($API) {
	  $API->write('/interface/ethernet/print',false);
 	  $API->write('=detail=');
 	  $ARRAY = $API->read();
 	  foreach( $ARRAY as $key => $eth){
	  return $eth ['mac-address'];
	  }
	}
	
	function getIpAddresses($API) {
	  $API->write('/ip/address/print',false);
 	  $API->write('=detail=');
 	  $ARRAY = $API->read();
 	  foreach( $ARRAY as $key => $ip){
	    $ipaddresses .= $ip ['address']."; ";
	  }
	  return $ipaddresses;
	}
	
	function getPortBridge($API) {
	  $ARRAY = $API->comm('/interface/bridge/port/print');
	  for($brpo=0; $brpo<count($ARRAY); $brpo++){
	    $couple = $ARRAY[$brpo]['interface'].$ARRAY[$brpo]['bridge'];
	    $all .= $couple."; ";
	  }
	  return $all;
	}
	
	function getWirelessPackInCharge($API) {
	  $ARRAY = $API->comm("/system/package/print", array("?disabled" => "false",));
	  foreach ( $ARRAY as $key => $value ) {
	    if ( strpos ( $value['name'], 'wireless' ) !== FALSE ) {
	      return $value['name']; 
	    }
	  }
	}
	function getCapInterfaces($API) {
	  $ARRAY = $API->comm('/interface/wireless/cap/print');
	  $capi = $ARRAY[0]['interfaces'];
	  return $capi;
	}



/**
 * Translate a result array into a HTML table
 *
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.3.2
 * @link        http://aidanlister.com/2004/04/converting-arrays-to-human-readable-tables/
 * @param       array  $array      The result (numericaly keyed, associative inner) array.
 * @param       bool   $recursive  Recursively generate tables for multi-dimensional arrays
 * @param       string $null       String to output for blank cells
 */
function array2table($array, $recursive = false, $null = '&nbsp;')
{
    // Sanity check
    if (empty($array) || !is_array($array)) {
        return false;
    }
 
    if (!isset($array[0]) || !is_array($array[0])) {
        $array = array($array);
    }
 
    // Start the table
    $table = "<table border=1 width=100%>\n";
 
    // The header
    $table .= "\t<tr>";
    // Take the keys from the first row as the headings
    foreach (array_keys($array[0]) as $heading) {
        $table .= '<th>' . $heading . '</th>';
    }
    $table .= "</tr>\n";
 
    // The body
    foreach ($array as $row) {
        $table .= "\t<tr>" ;
        foreach ($row as $cell) {
            $table .= '<td>';
 
            // Cast objects
            if (is_object($cell)) { $cell = (array) $cell; }
             
            if ($recursive === true && is_array($cell) && !empty($cell)) {
                // Recursive mode
                $table .= "\n" . array2table($cell, true, true) . "\n";
            } else {
                $table .= (strlen($cell) > 0) ?
                    htmlspecialchars((string) $cell) :
                    $null;
            }
 
            $table .= '</td>';
        }
 
        $table .= "</tr>\n";
    }
 
    $table .= '</table>';
    return $table;
}
?>
<html>
  <head>
  <meta http-equiv="refresh" content="10">
  </head>
  <body>
     <?php
	print array2table($lastarray);
	echo "dnsmasq lease flush using <b>service dnsmasq stop;echo '' ><i>/var/www/prov/dnsmasq.leases</i>;sudo service dnsmasq start</b></br>"; 
//	print array2table($leases);
     ?>
  </body>
</html>
