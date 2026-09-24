<?php 
 

 
/*

$string = 'Example String\n'; 
$pyout = exec('python detect_wajah.py');
echo $pyout; 
*/
$namaFile="foto.jpg";

//$command = 'python detect_wajah.py ' . $namaFile ;


$pyout = exec('python detect_wajah.py ' . $namaFile);
echo $pyout; 

//$output = passthru($command);


if($pyout=='0'){
	echo"Gagal ";
}
else{
	echo "Berhasil";
}
?>