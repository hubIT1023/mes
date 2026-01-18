  <!--i class="fas fa-microchip text-secondary small"></i-->
	<div class="dropdown">
		<a class="btn btn-sm border-0" 
		   href="#" 
		   id="alertsDropdown" 
		   role="button" 
		   data-bs-toggle="dropdown" 
		   aria-expanded="false">
			<i class="fas fa-list text-secondary"></i>
		</a>

		<div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-0 overflow-hidden" 
			 aria-labelledby="alertsDropdown" 
			 style="min-width: 280px;">
			
			<h6 class="dropdown-header bg-danger text-white py-3" style="font-size: 1.1rem;">
				<i class="fas fa-exclamation-triangle me-2"></i> Breakdown Details
			</h6>

			<a class="dropdown-item d-flex align-items-center py-3 border-bottom" href="#">
				<div class="me-3">
					<div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
						<i class="fas fa-wrench text-white"></i>
					</div>
				</div>
				<div>
					<div class="fw-bold text-danger small">Breakdown Issue(s)</div>
					<div class="text-muted small">
						<?php // brkdwn_info($entity); ?>
						Motor Overheat Detected
					</div>
				</div>
			</a>

			<a class="dropdown-item d-flex align-items-center py-3 border-bottom" href="#">
				<div class="me-3">
					<div class="bg-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
						<i class="far fa-clock text-white"></i>
					</div>
				</div>
				<div>
					<div class="fw-bold text-danger small">Downtime</div>
					<div class="text-muted small">
						<?php // downtime($entity); ?>
						02h 45m
					</div>
				</div>
			</a>

			<a class="dropdown-item d-flex align-items-center py-3" href="#">
				<div class="me-3">
					<div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
						<i class="fas fa-tools text-white"></i>
					</div>
				</div>
				<div>
					<div class="fw-bold text-warning small">Standing Issue(s)</div>
					<div class="text-muted small">
						<?php // standing_issue($entity); ?>
						Pending Part Replacement
					</div>
				</div>
			</a>
		</div>
	</div>