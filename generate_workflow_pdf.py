import os
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_RIGHT

def create_pdf(filename="HubIT_MMS_Workflow_Documentation.pdf"):
    # Target path
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        rightMargin=54,
        leftMargin=54,
        topMargin=54,
        bottomMargin=54
    )

    styles = getSampleStyleSheet()
    
    # Custom styles
    title_style = ParagraphStyle(
        'CoverTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=26,
        leading=32,
        textColor=colors.HexColor('#0F172A'), # slate-900
        alignment=TA_CENTER,
        spaceAfter=15
    )
    
    subtitle_style = ParagraphStyle(
        'CoverSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=13,
        leading=18,
        textColor=colors.HexColor('#475569'), # slate-600
        alignment=TA_CENTER,
        spaceAfter=140
    )
    
    meta_style = ParagraphStyle(
        'CoverMeta',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=10,
        leading=14,
        textColor=colors.HexColor('#64748B'), # slate-500
        alignment=TA_CENTER
    )
    
    h1_style = ParagraphStyle(
        'Header1',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=18,
        leading=22,
        textColor=colors.HexColor('#1E3A8A'), # blue-900
        spaceBefore=15,
        spaceAfter=10,
        keepWithNext=True
    )

    h2_style = ParagraphStyle(
        'Header2',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=16,
        textColor=colors.HexColor('#2563EB'), # blue-600
        spaceBefore=12,
        spaceAfter=6,
        keepWithNext=True
    )
    
    body_style = ParagraphStyle(
        'Body',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=14,
        textColor=colors.HexColor('#334155'), # slate-700
        spaceAfter=8
    )

    code_style = ParagraphStyle(
        'Code',
        parent=styles['Normal'],
        fontName='Courier',
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor('#1E293B'),
        spaceBefore=4,
        spaceAfter=4
    )

    story = []

    # ================= PAGE 1: COVER PAGE =================
    story.append(Spacer(1, 100))
    story.append(Paragraph("HubIT MMS Interactive Workflows", title_style))
    story.append(Paragraph("A Developer & User Guide to the Maintenance Forms, Workflows, and MVC Patterns", subtitle_style))
    story.append(Spacer(1, 60))
    story.append(Paragraph("<b>Prepared For:</b> Developers, System Admins, and Technicians<br/><b>Date:</b> June 2026<br/><b>Target Module:</b> MMS Forms & Task Schedulers", meta_style))
    story.append(PageBreak())

    # ================= PAGE 2: FILE MAP OVERVIEW =================
    story.append(Paragraph("1. Functional File Mapping", h1_style))
    story.append(Paragraph(
        "The HubIT Maintenance Management System (MMS) relies on a structured set of forms "
        "and controllers located inside the <code>forms_mms</code> folder. Below is the mapping "
        "of active views, their specific roles, and their corresponding server-side endpoints:",
        body_style
    ))
    story.append(Spacer(1, 10))

    # File mapping table
    table_data = [
        [
            Paragraph("<b>File Name</b>", body_style), 
            Paragraph("<b>Functional Description</b>", body_style), 
            Paragraph("<b>Target Action / Route</b>", body_style)
        ],
        [
            Paragraph("<b>addAssets.php</b>", body_style),
            Paragraph("Fullpage form to register equipment profiles (ID, Serial, Location).", body_style),
            Paragraph("POST /form_mms/addAsset<br/>&rarr; <code>AssetController::store</code>", body_style)
        ],
        [
            Paragraph("<b>register_asset_modal.php</b>", body_style),
            Paragraph("Modal popup to schedule maintenance, auto-calculating next dates.", body_style),
            Paragraph("POST /handler/registerAsset_handler.php", body_style)
        ],
        [
            Paragraph("<b>addMaintenance.php</b>", body_style),
            Paragraph("Fullpage form to log or schedule specific maintenance actions.", body_style),
            Paragraph("POST /form_mms/addMaintenance<br/>&rarr; <code>AssetMaintenanceController::store</code>", body_style)
        ],
        [
            Paragraph("<b>checklist_template.php</b>", body_style),
            Paragraph("Form to define step-by-step task checklists and intervals.", body_style),
            Paragraph("POST /form_mms/checklist_template<br/>&rarr; <code>ChecklistTemplateController::store</code>", body_style)
        ],
        [
            Paragraph("<b>create_checklist.php</b>", body_style),
            Paragraph("Manager-facing view to initiate checklist logs for machines.", body_style),
            Paragraph("POST /form_mms/addMaintenance", body_style)
        ],
        [
            Paragraph("<b>checklist_lists.php</b>", body_style),
            Paragraph("Grid listing active, pending, or completed checklists.", body_style),
            Paragraph("GET /form_mms/checklists<br/>&rarr; <code>ChecklistController::index</code>", body_style)
        ],
        [
            Paragraph("<b>maintenance_checklist.php</b>", body_style),
            Paragraph("Tech-facing sheet to log results and pass/fail metrics.", body_style),
            Paragraph("POST /maintenance_checklist/update<br/>&rarr; <code>MaintenanceChecklistController::update</code>", body_style)
        ],
        [
            Paragraph("<b>routine_maintenance.php</b>", body_style),
            Paragraph("Form to manually generate batch routine work orders.", body_style),
            Paragraph("POST /form_mms/routine_maintenance<br/>&rarr; <code>RoutineMaintenanceController::generate</code>", body_style)
        ]
    ]

    t = Table(table_data, colWidths=[130, 200, 170])
    t.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#F1F5F9')),
        ('ALIGN', (0,0), (-1,-1), 'LEFT'),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('GRID', (0,0), (-1,-1), 0.5, colors.HexColor('#CBD5E1')),
    ]))
    story.append(t)
    
    # Note on legacy files
    story.append(Spacer(1, 15))
    story.append(Paragraph("<b>Note on Legacy/Unused Files:</b><br/>"
                           "&bull; <code>config_maint_form.php</code>: Inactive legacy configuration template.<br/>"
                           "&bull; <code>routine_maintenance_preview.php</code>: Misplaced controller code duplicate (now handled by <code>app/controllers/RoutineMaintenanceController.php</code>).", body_style))
    story.append(PageBreak())

    # ================= PAGE 3: OPERATIONAL WORKFLOW =================
    story.append(Paragraph("2. Overall Maintenance Workflow & Lifecycle", h1_style))
    story.append(Paragraph(
        "The HubIT MMS links equipment, schedule configurations, and physical checklist execution. "
        "The standard workflow flows through four distinct phases:",
        body_style
    ))
    story.append(Spacer(1, 5))

    story.append(Paragraph("Phase 1: Inventory & Asset Setup", h2_style))
    story.append(Paragraph(
        "Before scheduling work, physical assets are registered using <code>addAssets.php</code>. "
        "Each machine is registered with a unique Asset ID (e.g. <code>CNC-01</code>) and location. "
        "Once saved, these populate the <code>assets</code> table scoped by the tenant's organization ID.",
        body_style
    ))

    story.append(Paragraph("Phase 2: Defining Maintenance Templates", h2_style))
    story.append(Paragraph(
        "Using <code>checklist_template.php</code>, coordinators configure standard templates "
        "defining what must be done. They select a maintenance type (Preventive, Corrective, Inspection), "
        "set an interval in days (e.g., 30 days), and type out a series of step-by-step tasks "
        "(e.g., '1. Inspect oil level', '2. Verify emergency stop').",
        body_style
    ))

    story.append(Paragraph("Phase 3: Scheduling & Dispatching Work", h2_style))
    story.append(Paragraph(
        "Work orders can be generated manually or automatically:<br/>"
        "&bull; <b>Manual Scheduling:</b> The user triggers <code>register_asset_modal.php</code>, chooses a machine and maintenance type, and the system automatically calculates the next maintenance date based on the template interval, inserting it as a 'scheduled' log.<br/>"
        "&bull; <b>Batch Generation:</b> Coordinators use <code>routine_maintenance.php</code> to select multiple machines, check constraints, and generate a batch of work orders.",
        body_style
    ))

    story.append(Paragraph("Phase 4: Technician Checklist Execution", h2_style))
    story.append(Paragraph(
        "Technicians check off tasks in <code>maintenance_checklist.php</code>. "
        "They log remarks, input pass/fail/measurements, and click 'Mark as Completed' on their screen, "
        "which re-calculates the next maintenance cycle and archives the checklist results.",
        body_style
    ))
    story.append(PageBreak())

    # ================= PAGE 4: MVC EXECUTION PATTERN =================
    story.append(Paragraph("3. MVC Architecture & Request Flow", h1_style))
    story.append(Paragraph(
        "The system decouples data, processing, and interface layers using the Model-View-Controller pattern. "
        "Here is the request execution path using <b>Asset Registration</b> as a reference:",
        body_style
    ))
    story.append(Spacer(1, 5))

    # Flow chart table / step
    story.append(Paragraph("Step 1: Front Controller & Router", h2_style))
    story.append(Paragraph(
        "When the browser requests <code>GET /mes/form_mms/addAsset</code>, the webserver redirects the request to "
        "<code>index.php</code>. The routing engine loads <code>app/routes.php</code> and checks for matches. Upon finding the "
        "path matching <code>GET /form_mms/addAsset</code>, it instantiates <code>AssetController</code> and invokes <code>create()</code>.",
        body_style
    ))

    story.append(Paragraph("Step 2: Controller Response", h2_style))
    story.append(Paragraph(
        "The <code>AssetController</code> checks the user session, generates a CSRF token to prevent cross-site request "
        "forgery, and compiles local variables before loading the HTML form view:<br/>"
        "<code>include __DIR__ . '/../views/forms_mms/addAssets.php';</code>",
        body_style
    ))

    story.append(Paragraph("Step 3: Form Submission (POST)", h2_style))
    story.append(Paragraph(
        "The browser renders the form. The user types the information and clicks submit. "
        "A <code>POST</code> request goes to <code>/mes/form_mms/addAsset</code>, which is routed to <code>AssetController::store()</code>. "
        "The controller checks the CSRF token and calls the model:",
        body_style
    ))

    model_code = (
        "// Inside app/controllers/AssetController.php<br/>"
        "if ($this->model->addAsset($_POST)) {<br/>"
        "&nbsp;&nbsp;&nbsp;&nbsp;$_SESSION['success'] = 'Asset registered successfully';<br/>"
        "&nbsp;&nbsp;&nbsp;&nbsp;header('Location: /mes/form_mms/addAsset');<br/>"
        "&nbsp;&nbsp;&nbsp;&nbsp;exit;<br/>"
        "}"
    )
    story.append(Paragraph(model_code, code_style))

    story.append(Paragraph("Step 4: Model Execution & Database Write", h2_style))
    story.append(Paragraph(
        "The <code>AssetModel.php</code> interacts with PostgreSQL. It prepares a parameterized SQL query "
        "using PDO to write the record cleanly while preventing SQL injection:",
        body_style
    ))

    sql_code = (
        "// Inside app/models/AssetModel.php<br/>"
        "public function addAsset($data) {<br/>"
        "&nbsp;&nbsp;&nbsp;&nbsp;$stmt = $this->db->prepare(\"INSERT INTO assets (asset_id, asset_name, org_id) VALUES (?, ?, ?)\");<br/>"
        "&nbsp;&nbsp;&nbsp;&nbsp;return $stmt->execute([$data['asset_id'], $data['asset_name'], $data['tenant_id']]);<br/>"
        "}"
    )
    story.append(Paragraph(sql_code, code_style))
    
    # Build document
    def add_page_number(canvas, doc):
        canvas.saveState()
        canvas.setFont('Helvetica', 8)
        canvas.setFillColor(colors.HexColor('#64748B'))
        # Draw header rule and text
        canvas.drawString(54, 750, "HubIT Maintenance Management System — Workflow & MVC Reference Document")
        canvas.setStrokeColor(colors.HexColor('#E2E8F0'))
        canvas.setLineWidth(0.5)
        canvas.line(54, 742, 558, 742)
        
        # Draw footer page number
        page_num = canvas.getPageNumber()
        if page_num > 1:
            page_text = f"Page {page_num}"
            canvas.drawRightString(558, 40, page_text)
            canvas.drawString(54, 40, "DEVELOPER & USER DOCUMENTATION REFERENCE")
            canvas.line(54, 52, 558, 52)
        canvas.restoreState()

    doc.build(story, onFirstPage=lambda c, d: None, onLaterPages=add_page_number)
    print(f"PDF successfully created: {filename}")

if __name__ == "__main__":
    create_pdf()
