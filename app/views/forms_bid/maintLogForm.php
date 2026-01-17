<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
   	 <title>Maintenance Work Log</title>
	
	 <!-- Custom styles for this template-->
	<link href="../assets/css/style.css" rel="stylesheet">
	<link href="../assets/css/styles.css" 		type="text/css" rel="stylesheet">
	<link href="../assets/css/sb-admin-2.css" 	rel="stylesheet">
	<link href="../assets/css2/bootstrap.css" 	rel="stylesheet">
	<link href="../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">


    
  </head>
  <body style = "background-color:#ddeeee">
		<!--Modal-->
		<div id='workstation'></div>

				<div class="modal fade"  id="input_form" tabindex="-1" role="dialog" aria-labelledby="form_Label" aria-hidden="true">
					<div class="modal-dialog modal-lg" role="document">
						<div class="modal-content">
						<!-- Modal Header -->
						<div class="modal-header">
							<h5 class="modal-title" id="form_Label">Maintenance Work Log</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
							<!-- Modal body -->
			<div class="modal-body">
				<div class="content">
					<form action="../handlers/logHandler_Work_Log.php" method="POST" enctype="multipart/form-data">
							<div class="tool-details">
							
								<div class="input-box">
									<div class="tool-details ">
										<span class="details">Date / Time</span>
											<input type="text"  name ="date_time"
											value =" <?php
											include('../conf/dateTime_format.php');		
											 echo $dateTime_stamp;?>">
											</input>
									</div>								
								</div>
	
								<div class="input-box">
								<span class="details">Entity</span>
								<!--input type="text"  name="entity" value = "TCXO_DTRO_5"  ></input-->
								
								<input 	type='text' id = 'entity'  name = "entity" value =  inputMC.value >
								</div>
								
								<div class="input-box" >
									
									<div class="tool-details ">
										<div class="input-box ml-auto">
											<div class="input-group">
												<span class="details">Location</span>
												<div class="input-group" >
												<input 	type='text' id = 'location'  name = "location" value = "Cleanroom">
												</div>
											</div>
										</div>
									
									
									
										<div class="input-box ml-auto">
											<div class="input-group">
												<span class="details">Other Entity</span>
												<div class="input-group" >
												<input type="text" class="form-control"style="width:90px" name="other_ent" id="other_ent" 
												value="" placeholder="Entity ID" disabled>
												</div>
											</div>
										</div>
											
										
									</div>
								</div>	
								
								
								<div class="input-box">
								<span class="details">Stopcause</span>
									<select type="text"  name="stopcause" onchange="showDiv(this)" placeholder="Select" >
										<option  value =  	>Select stopcause</option>
										<option  value = "CALIBRATION" 	>CALIBRATION</option>
										<option  value = "CHANGE_OVER" 	>CHANGE OVER </option>
										<option  value = "FAULT" 		>FAULTY EQUIPMENT</option>
										<option  value = "MAINT" 	    >MAINTENANCE</option>
										<option  value = "PROCESS" 		>PROCESS RELATED</option>
										<option  value = "EDG/SDG" 		>EDG/SDG RELATED</option>
										<option  value = "EQUIPMENT_Upgrade/Update" >EQUIPMENT_Upgrade/Update </option>
										<option  value = "OPERATION_RELATED" >OPERATION RELATED</option>
										<option  value = "FACILITIES_RELATED" >FACILITIES RELATED</option>
										<option  value = "PRODUCT_RELATED" >PRODUCT RELATED</option>
										<option  value = "SOFWARE_RELATED" >SOFWARE RELATED</option>
										<option  value = "IS_RELATED" >IS RELATED</option>
										
									
									</select>
								  </div>
								  
								  <div class="input-box">
										<span class="details">Issue(s)</span>
										<textarea type="text" name="issue" value=""placeholder="Enter issue(s)" required></textarea>
								  </div>
								   <div class="input-box">
										<span class="details">Action(s)</span>
										<textarea  type="text" name="action" value="" placeholder="Enter action(s)" required></textarea>
								  </div>
								  
								
							</div>
							
						
							<div class="tool-details">
								
								
								<div class="input-box">
								<span class="details">Verification</span>
									<select type="text" name="verification" id="verification"   >
										<option value =  	>Select verification type </option>
										<option  value = "not_required"  					>NOT REQUIRED</option>
										<option  value = "measurement"  					>MEASUREMENT</option>
										<option  value = "electrical-installation/repair"  	>ELECTRICAL - installation/repair</option>
										<option  value = "mechanical assembly " 			>MECHANICAL ASSEMBLY</option>
										<option  value = "product_related " 				>PRODUCT RELATED</option>
										<option  value = "process_control_system " 			>PROCESS CONTROL SYSTEM</option>
										<option  value = "environmentalChamber_testOver " 	>ENVIRONMENTAL CHAMBER/TEST OVER</option>
										<option  value = "software_configuration " 			>SOFTWARE CONFIGURATION</option>
										<option  value = "equipment_safety_system " 		>EQUIPMENT SAFETY SYSTEM</option>
										<option  value = "unplanned_system_power_cut-off " 	>UNPLANNED SYSTEM POWER CUT-OFF</option>
										<option  value = "gas-CO2/N2" 						>GAS - CO2/N2</option>
										<option  value = "factory_environment " 			>FACTORY ENVIRONMENT</option>
										<option  value = "others " 							>OTHERS</option>
									</select>
								</div>

										<div class="input-box">
										  <input type="radio" name="result" id="dot-1"  value = "pass " >
										  <input type="radio" name="result" id="dot-2"	value = "fail ">
										  <input type="radio" name="result" id="dot-3"	value = "not applicable ">
										  <span class="details">Result</span>
										  <div class="category">
												<label for="dot-1">
												<span class="dot one"></span>
												<span class="option">Pass</span>
												</label>
												<label for="dot-2">
												<span class="dot two"></span>
												<span class="option">Fail</span>
												</label>
												<label for="dot-3">
												<span class="dot three"></span>
												<span class="option">N/A</span>
												</label>
										   </div>
									</div>
									
									<div class="input-box" >
								<span class="details">Category</span>
									<select type="text"  name="category" id="category" placeholder="Select"  >
										<option value =  				>Select category</option>
										<option value = "scheduled" 	>SCHEDULE</option>
										<option value = "unscheduled" 	>UNSCHEDULE</option>
										<option value = "others" 		>Others</option>
									 </select>
									</div>
									
									<div class="input-box" >
										<div class="tool-details ">
												<div class="input-box ml-auto">
														<div class="input-group">
														<span class="details">Machine Downtime</span>
														<div class="input-group" >
														<input type="text" class="form-control"style="width:90px" name="machine_DT" value="" placeholder="MC DT" 
														aria-label="Input group example" aria-describedby="btnGroupAddon">
														<div class="input-group-text" id="btnGroupAddon">mins</div>
														</div>
													  </div>
														</div>
													
													<div class="input-box ml-auto">
														<div class="input-group">
														<span class="details">Tech Downtime</span>
														<div class="input-group" >
														<input type="text" class="form-control"style="width:90px" name="tech_DT" value="" placeholder="Tech DT" 
														aria-label="Input group example" aria-describedby="btnGroupAddon">
														<div class="input-group-text" id="btnGroupAddon">mins</div>
														</div>
													  </div>
														</div>
												</div>
											</div>					
										</div>	
								
									<div class="tool-details">	
								
								<div class="input-box">
										 <span class="details" >Status</span>
									<select type="text" name="status" id ="hidden_div" placeholder="Select"  >
										<option value =  	>Select status</option>
										<option value = "completed"		>COMPLETED</option>
										<option value = "monitor" 		>MONITOR</option>
										<option value = "in-progress" 	>IN-PROGRESS</option>
										<option value = "awaiting_parts">AWAITING PARTS</option>
									</select>
								</div>
								
								<div class="input-box">
										<span class="details">Done by:</span>
										<input type="text" name="person_incharge" value="" placeholder="Type Your Name" required></input>
								</div>
								

							</div>
						
									
							<div class="tool-details">	
									 <div class="input-box">
									<span class="details" >Attached File(optional)</span>
										<input class="form-control" type="file" id="formFile"   disabled>
									
									 </div>
									<div class="input-box">
											<span class="details">File Description</span>
											<textarea type="text" name="description" value=""placeholder="Enter description"  disabled></textarea>
									 </div>
									 
							</div>
							</div>		
							</div>
								
						
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
								<button type="submit" class="btn btn-primary"  name="add" value="SUBMIT">Submit</button>
							</div>				
					</div>
				</div>
						</div>								
					</div>
				</div>
	
	
	<!------------------------------------------------end of modal form------------------------------------------------------------------->


<div class="container-lg px-5 py-3">
	<header>
	 
     <nav class="navbar navbar-expand navbar-light bg-light mb-4">
	 	<button type="button" style = "width:70px"class="btn btn-outline-secondary"
		onclick="window.location.href='http://<?php echo getenv('WEB_BASE_URL'); ?>/';">HOME</button>	
		<div class="card-body">
		
		<button type="button" style = "width:150px"class="btn btn-outline-primary"
		onclick="window.location.href='../forms_mwlogs/form_xtaltest.php';">XTAL TEST</button>
			
		<button type="button" style = "width:150px" class="btn btn-outline-primary" 
		onclick="window.location.href='../forms_mwlogs/form_xmems.php';">XMEMS</button>
		
		<button type="button" style = "width:200px" class="btn btn-outline-primary" 
		onclick="window.location.href='../logs/dailyMantenanceReport.php';">VIEW MAINT_LOG</button>
		
		</div>
		
	 </nav>

    <div class="text-center">
      <h1 class="display-6 fw-normal">Maintenance Work Log</h1>
       </div>
	</header>

	<main>
 
	<div class="pt-4 my-md-2 pt-md-2 "></div>
	<div class="container-fluid">
	<!--div class="row row-cols-1 row-cols-md-3 mb-3 text-center"-->

		<div class="row">
		<div class="table-responsive">
			<table class="table text-center " >
			<tbody>
				<tr>
					<td>
						<div class="card shadow " style=" width:280px; height:380px">
							<div class="card-header py-3"style="height :70px">
							  <h6 class="m-0 font-weight-bold text-primary" target = "blank "  >BASE  &nbsp PLATING </h6>
							</div>
							<div class="card-body" style="height:310px">
								<div  class ="body-bg" width="410">					
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_BP_3()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">BP_3</div>
										  </div>
										</a>
								
								
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_BP_5()" >	
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">BP_5</div>
										  </div>
										</a>
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_BP_6()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">BP_6</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_BP_7()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">BP_7</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_BPWASH_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Wash Station 2</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_CREST_DRYER()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Crest Dryer</div>
										  </div>
										</a>
										
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Spin Rinse Dryer 1</div>
										  </div>
										</a>
										
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Spin Rinse Dryer 2</div>
										  </div>
										</a>

								</div>
							</div>
						</div>
					</td>
								
								
		<!--1/2-->	<td>
						<div class="card shadow " style=" width:280px; height:380px">
							<div class="card-header py-3"style="height :70px">
							  <h6 class="m-0 font-weight-bold text-primary" target = "blank "  > FRAME LOADERS </h6>
							</div>
							<div class="card-body" style="height:310px">
								<div  class ="body-bg" width="410">				
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_FL_1()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">FL1 PMI11</div>
										  </div>
										</a>
								
								
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_FL_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">FL2 PMI11</div>
										  </div>
										</a>
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_FL_3()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">FL3 PMI21</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_FL_4()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">FL4 PMI21</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_FL_5()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">FL5 BU</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entit_xx" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">----</div>
										  </div>
										</a>
										
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="others()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">OTHERS</div>
										  </div>
										</a>
										
								</div>
							</div>
						</div>	
					</td>
									
							

		<!--1/3-->	<td>
						<div class="card shadow " style=" width:280px; height:380px">
							<div class="card-header py-3"style="height :70px">
							  <h6 class="m-0 font-weight-bold text-primary" target = "blank "  > MOUNTING & GLUING | WIREBONDER </h6>
							</div>
							<div class="card-body" style="height:310px">
								<div  class ="body-bg" width="410">				
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_p_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">P2</div>
										  </div>
										</a>
								
								
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_p_4()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">P4</div>
										  </div>
										</a>
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_p_5()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">P5</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_p_6()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">P6</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_AKIM_HEIGHT_CHECK()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Akim Height Check</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_wirebond_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Wire Bonder 2</div>
										  </div>
										</a>
								</div>
							</div>
						</div>
					</td>		
				</tr >					
			  
			  
				<tr>
					<td>
						<div class="card shadow " style=" width:280px; height:380px">
								<div class="card-header py-3"style="height :70px">
								  <h6 class="m-0 font-weight-bold text-primary" target = "blank "  > ION ETCH </h6>
								</div>
								<div class="card-body" style="height:310px">
									<div  class ="body-bg" width="410">				
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_1()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE1</div>
										  </div>
										</a>
								
								
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE2</div>
										  </div>
										</a>
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_3()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE3</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_4()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE4</div>
										  </div>
										</a>
										
										
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_5()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE5</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_6()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE6</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_IE_7()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">IE7</div>
										  </div>
										</a>
										
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_xx()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">----</div>
										  </div>
										</a>
								</div>
							</div>
						</div>	
					</td>

		<!--1/5-->	<td>
						<div class="card shadow " style=" width:280px; height:380px">
							<div class="card-header py-3"style="height :70px">
							  <h6 class="m-0 font-weight-bold text-primary" target = "blank "  > WELDING </h6>
							</div>
							<div class="card-body" style="height:310px">
								<div  class ="body-bg" width="410">				
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_AuSn_WELDING()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">AuSn</div>
										  </div>
										</a>
								
								
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_AVIO_1()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">AVIO 1</div>
										  </div>
										</a>
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_AVIO_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">AVIO 2</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_VOSS_4()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">VOSS 4</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_VOSS_5()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">VOSS 5</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_VOSS_6()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">VOSS 6</div>
										  </div>
										</a>
										
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_VOSS_7()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">VOSS 7</div>
										  </div>
										</a>
								</div>
							</div>
						</div>			
					</td>
				
		<!--1/5-->	<td>
						<div class="card shadow " style=" width:280px; height:380px">
							<div class="card-header py-3">
							  <h6 class="m-0 font-weight-bold text-primary" target = "blank "  > STRIPS ANNEALING OVEN | LEAK CHECK </h6>
							</div>
							<div class="card-body" style="height:310px">
								<div  class ="body-bg" width="410">				
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_monford--()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">VCXO OVEN</div>
										  </div>
										</a>
								
								
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_TO_2()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Tunnel Oven 2</div>
										  </div>
										</a>
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_TO_3()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Tunnel Oven 3</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_TO_5()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Tunnel Oven 5</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_TO_6()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">Tunnel Oven 6</div>
										  </div>
										</a>
										
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">He Leak Detector</div>
										  </div>
										</a>
										
										
											
										<a class="dropdown-item d-flex align-items-center bg-light" 
										data-toggle="modal" data-target="#input_form" onclick="entity_()" >
										  <div class="mr-3">
											<div >
											  <i class="fas fa-bullseye text-info"></i>
											</div>
										  </div>
										  <div>
											<div class="font-weight-bold">MS 5063 GLT</div>
										  </div>
										</a>
								</div>
							</div>
						</div>		
					</td>
				</tr>
		</table>
    </div>
	</div>
</div>
  </main>

  <footer class="pt-4 my-md-5 pt-md-5 border-top">
    <div class="row">
      <div class="col-12 col-md">
        <img class="mb-2" src="../assets/brand/bootstrap-logo.svg" alt="" width="24" height="19">
        <small class="d-block mb-3 text-muted">&copy; Pinoy_2022 | reuse::syntax</small>
      </div>
      
      </div>
    </div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@2.2.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.4/dist/umd/popper.min.js"></script>

		  <!-- Bootstrap core JavaScript-->
  <script src="../../vendor/jquery/jquery.min.js"></script>
  <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/mc_list/machinelist.js"></script>
  
  
  
    <!-- Bootstrap core JavaScript-->

  <script src="../../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="../../vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="../../js/sb-admin-2.min.js"></script>
	<script>
		function others() {
			document.getElementById('other_ent').disabled = false;
			inputMC.value  = "OTHERS";
		}
	</script>
  </body>
</html>

