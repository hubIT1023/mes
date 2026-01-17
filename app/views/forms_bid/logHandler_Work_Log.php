<?php 

include('../conf/connectDB.php');
 
function getData()	{
	
	date_default_timezone_set('NZ');

	$tm = time();
	$date=strtotime($tm);
	$dateTime_stamp = date('d/m/Y H:i:s',$tm);	
	$time_stamp = date('H:i:s',$tm);
	$date_stamp = date('m/d/Y',$tm);
	$month_stamp = date('m',$tm);
	$week_stamp = date('w',$tm);
	$day_stamp = date('d',$tm);
    $year_stamp = date('y',$tm);
	
	
	if ($time_stamp > '00:00:00' && $time_stamp < '07:29:00'){
		$code='GY';
	}	
	else if ($time_stamp > '07:30:00' && $time_stamp < '15:59:00'){
		$code='AM';
	}	
	else if ($time_stamp > '16:00:00' && $time_stamp < '24:00:00'){
		$code='PM';
	}
	
		$data 	 	= array();
		$data[1] 	= $_POST['entity'];
		$data[2] 	= $_POST['stopcause'];
		$data[3] 	= $_POST['issue'];
		$data[4] 	= $_POST['action'];
		$data[5] 	= $_POST['verification'];
		$data[6] 	= $_POST['result'];
		$data[7] 	= $_POST['status'];
		$data[8] 	= $_POST['category'];
		$data[9] 	= $_POST['machine_DT'];
		$data[10]	= $_POST['tech_DT'];
		$data[11] 	= $_POST['location']; ;  //location
		$data[12] 	= $code;	//shift
		$data[13]	= $_POST['date_time'];
		$data[14]	= $_POST['person_incharge'];
		$data[15]	= $date_stamp;
		$data[16]	= $_POST['other_ent'];
		return $data;
}


	
if(isset($_POST['add'])){
		$info = getData();
		
	if ( $_POST['entity'] == "OTHERS")
	{
			$info[1] = $info[16];
	}	
	

	$insert ="INSERT INTO maintWorkLog( entity
	, stopcause_start	
	, issue								
	, action			
	, verification		
	, result			
	, status			
	, category			
	, machine_DT
	, tech_DT
	, location			
	, shift				
	, dateStamp	
	,start_dateTime 
	, end_dateTime
	, person_start
	, person_end) 
				
	VALUES('$info[1]'
	, '$info[2]'
	, '$info[3]'
	, '$info[4]'
	, '$info[5]'
	, '$info[6]'
	, '$info[7]'
	, '$info[8]'
	, '$info[9]'
	, '$info[10]'
	, '$info[11]'
	, '$info[12]'
	, '$info[13]'
	, '$info[15]'
	, '$info[15]'
	, '$info[14]'
	, '$info[14]')" ;
	
	$stmtinsert = $conn->prepare($insert);
	$insertResult = $stmtinsert->execute();
		

}
	
		
	if($insertResult){
	
			if($info[11] == 'Cleanroom'){
			echo "<script> 
			location.href='../forms_mwlogs/logform_cleanroom.php';
			</script>";
			}
			
			else if($info[11] == 'XMEMS'){
			echo "<script> 
			location.href='../forms_mwlogs/form_xmems.php';
			</script>";
			}
			
			
			else if($info[11] == 'XTAL_Test'){
			echo "<script> 
			location.href='../forms_mwlogs/form_xtaltest.php';
			</script>";
			}
			
			else if($info[11] == 'TCXO'){
			echo "<script> 
			location.href='../forms_mwlogs/logform_tcxo.php';
			</script>";
			}
			
			else if($info[11] == 'IT'){
			echo "<script> 
			location.href='../forms_mwlogs/form_IT.php';
			</script>";
			}
			
			else if($info[11] == 'SMT'){
			echo "<script> 
			location.href='../forms_mwlogs/form_smt.php';
			</script>";
			}
			
			
		}
		else{
				echo "<script> alert ('error encountered') ;
			location.href='../../';
			</script>";
		}
				
	
	

?>