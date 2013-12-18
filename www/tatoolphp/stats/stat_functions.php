<?php

/* =================================================== */
/* FUNCTIONS			       		       */
/* =================================================== */


/*------------------------------------------------------------------------------
 Get the mode of an array
------------------------------------------------------------------------------*/
function getMode(array $valores){
	$longitud = count($valores);
	$repeticiones = array();
	if($longitud==0)
		return FALSE;
	foreach($valores as $valor){
		$repeticiones[$valor]=0;
		for($i=0;$i<$longitud;$i++){
			if($valores[$i]==$valor)
				$repeticiones[$valor]++;
		}
	}
	unset($valores,$longitud,$i,$valor);
	asort($repeticiones);
	$llaves = array_keys($repeticiones);
	return array_pop($llaves);
}

/*------------------------------------------------------------------------------
 Get a file listing of a specific directory
------------------------------------------------------------------------------*/
function getFiles($filepath){
    $files = array();
    $directory = opendir($filepath);
    while(false !== ($item = readdir($directory))){
    // We filter the elements that we don't want to appear ".", ".." and ".svn"
         if(($item != ".") && ($item != "..") ){
              $files[] = $item;
         }
    }
    sort($files);
    return $files;
} 

/*------------------------------------------------------------------------------
 Create a complete CSV for a subject
------------------------------------------------------------------------------*/
function createSubjectCSV($study_id, $module_nr, $group_nr, $subject_code, $filepath) {

$cache_life = 10;

if (file_exists($filepath . 'trials.csv')) {
  if ((time() - filemtime($filepath . 'trials.csv')) <= $cache_life) {
    return;
  }
}
// Create CSV file
unlink($filepath . 'trials.csv');
$files = getFiles($filepath);
$fptrials = fopen($filepath . 'trials.csv', 'w');
$first_row = 0;
$columns = array();
$sessions = array();

$columns[] = "Subject Code";
// loop through files to get column headers
for ($i=0; $i < count($files); $i++) {
$row = 0;
$handle = fopen ($filepath . $files[$i], "r");
  while ( ($data = fgetcsv ($handle, 10000, ";")) !== FALSE ) {
    $row++;
    if ($row == 1) {
      $num = count ($data);
      for ($x=0; $x < $num; $x++) {
        if (!in_array($data[$x], $columns)) {
          $columns[] = $data[$x];  
        }
      }
      continue;   
    }
  }
}

// write column headers to output
fputcsv ($fptrials , $columns, ';' );

// loop through files to get data and write it to output
for ($i=0; $i < count($files); $i++) {

$handle = fopen ($filepath . $files[$i], "r");
$file_sessions = array();
$file_columns = array();
$session_id = 0;
$row = 0;

while ( ($data = fgetcsv ($handle, 10000, ";")) !== FALSE ) {
  $row++;

  if ($row == 1) {
    $file_columns = $data;
  } else if ($row > 1) {
	$indexOfSessionID = array_search('session.id', $columns) - 1;
	$indexOfSessionEnd = array_search('session.endTime', $columns) - 1;
    $session_id = $data[$indexOfSessionID];
    $session_end_dt= $data[$indexOfSessionEnd];
    
    // session new overall
    if (!in_array($session_id, $sessions) && $session_end_dt != null) {
    
      // add session to current sessions
      if (!in_array($session_id, $file_sessions)) {
        $file_sessions[] = $session_id;
      }
      
      // add subject code
      fwrite($fptrials, $subject_code . ";");
      
      // loop through columns
      for ($x=1; $x < count($columns); $x++) {
        $idx = array_search($columns[$x], $file_columns);
        if ($idx !== false) {
          fwrite($fptrials, $data[$idx]); 
        }
        
        // add semicolon
        if ($x < (count($columns) - 1)) {
          fwrite($fptrials, ";"); 
        } else if ($x == (count($columns) - 1)) {
          fwrite($fptrials, "\n");
        }
      }
      //fputcsv ($fptrials , $data, ';' );
    }  	
  }
  
}
fclose ($handle);

// add new sessions to session array
for ($j=0; $j < count($file_sessions); $j++) {
  if (!in_array($file_sessions[$j], $sessions)) {
    $sessions[] = $file_sessions[$j];
  }
}

}

fclose ($fptrials);

}

/*------------------------------------------------------------------------------
 Create a complete CSV for a group
------------------------------------------------------------------------------*/
function createGroupCSV($study_id, $module_id, $subject_codes, $file) {

$cache_life = 21600;

// set the write path
$writepath=$studiespath 
."studies"."/".$study_id
."/"."modules"
."/".$module_id
."/"."upload"
."/";

if (file_exists($writepath . $file)) {
  if ((time() - filemtime($writepath . $file)) <= $cache_life) {
    //return;
  }
}

unlink($writepath . $file);

$fptrials = fopen($writepath . $file, 'w');
$first_row = 0;
$columns = array();

// loop through files to get column headers
for ($i=0; $i < 2; $i++) {

$row = 0;

$readpath=$studiespath 
."studies"."/".$study_id
."/"."modules"
."/".$module_id
."/"."upload"
."/".$subject_codes[$i]
."/";

if (file_exists($readpath . "trials.csv")) {
  $handle = fopen ($readpath . "trials.csv", "r");
  while ( ($data = fgetcsv ($handle, 10000, ";")) !== FALSE ) {
    $row++;
    if ($row == 1) {
      $num = count ($data);
      for ($x=0; $x < $num; $x++) {
        if (!in_array($data[$x], $columns)) {
          $columns[] = $data[$x];  
        }
      }
      continue;   
    }
  }  
}

}

// write column headers to output
fputcsv ($fptrials , $columns, ';' );

// loop through files to get data and write it to output
for ($i=0;$i<count($subject_codes);$i++) {

$readpath=$studiespath 
."studies"."/".$study_id
."/"."modules"
."/".$module_id
."/"."upload"
."/".$subject_codes[$i]
."/";

if (file_exists($readpath . "trials.csv")) {
$handle = fopen ($readpath . "trials.csv", "r");
$file_columns = array();
$row = 0;

while ( ($data = fgetcsv ($handle, 10000, ";")) !== FALSE ) {
  $row++;

  if ($row == 1) {
    $file_columns = $data;
  } else if ($row > 1) {

      // loop through columns
      for ($x=0; $x < count($columns); $x++) {
        $idx = array_search($columns[$x], $file_columns);
        if ($idx !== false) {
          fwrite($fptrials, $data[$idx]); 
        }
        
        // add semicolon
        if ($x < (count($columns) - 1)) {
          fwrite($fptrials, ";"); 
        } else if ($x == (count($columns) - 1)) {
          fwrite($fptrials, "\n");
        }
      }
    }  	
  
}
fclose ($handle);
}

}
fclose ($fptrials);

}

/*------------------------------------------------------------------------------
 Open a CSV
------------------------------------------------------------------------------*/
function getCSV($study_id, $module_id, $subject_code, $filepath, $file) {

if (file_exists($filepath . $file))
{
  $fp = @fopen($filepath . $file, 'rb');

  if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
  {
    header('Content-Type: "application/octet-stream"');
    header('Content-Disposition: attachment; filename="'. $file . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header("Content-Transfer-Encoding: binary");
    header('Pragma: public');
    header("Content-Length: ".filesize($filepath . $file));
  }
  else
  {
    header('Content-Type: "application/octet-stream"');
    header('Content-Disposition: attachment; filename="'. $file . '"');
    header("Content-Transfer-Encoding: binary");
    header('Expires: 0');
    header('Pragma: no-cache');
    header("Content-Length: ".filesize($filepath . $file));
  }

  fpassthru($fp);
  fclose($fp);

} else {
  header("location: ". $_SERVER['HTTP_REFERER']);
}
}

?>