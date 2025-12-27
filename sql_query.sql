SELECT 
    a.id            AS asset_db_id,
    a.asset_id,
    a.asset_name,
    a.location_id_1,
    a.location_id_2,
    a.location_id_3,
    c.id            AS checklist_db_id,
    c.checklist_id,
    c.maintenance_type,
    c.work_order,
    c.technician,
    c.interval_days

FROM [Tool_Monitoring_System].[dbo].[assets] AS a
INNER JOIN [Tool_Monitoring_System].[dbo].[checklist_template] AS c
    ON a.tenant_id = c.tenant_id
-- Optional: filter by tenant
WHERE a.tenant_id = '9C4FFC4D-CF21-466D-A256-34A38C0CCD59'
-- Optional: filter by specific asset
-- AND a.asset_id = 'ASSET-001'
ORDER BY a.asset_name, c.checklist_id;

-----------------------------------
--checklist_template
-----------------------------------

SELECT 
    t.id                AS template_id,
    t.tenant_id,
    t.checklist_id,
    t.maintenance_type,
    t.work_order,
    t.technician,
    t.interval_days,
    t.description,
    t.created_by,
    t.updated_by,
    t.created_at,
    t.updated_at,

    -- From checklist_tasks
    c.id                AS task_id,
    c.task_order,
    c.task_text

FROM [Tool_Monitoring_System].dbo.checklist_template AS t
LEFT JOIN [Tool_Monitoring_System].dbo.checklist_tasks AS c
    ON  t.tenant_id = c.tenant_id
    AND t.checklist_id = c.checklist_id

WHERE 
    t.tenant_id = '9C4FFC4D-CF21-466D-A256-34A38C0CCD59'
    AND t.checklist_id = 'MAINT-01'

ORDER BY 
    t.checklist_id,
    c.task_order;
	


-----------------------------------
-- Generatoring Routine Maintenance 
-- Join Assets + Checklist Template + Insert Into Routine Work Orders	
-----------------------------------

	
INSERT INTO [Tool_Monitoring_System].[dbo].[routine_Work_Orders] (
    tenant_id,
    asset_id,
    asset_name,
    location_id_1,
    location_id_2,
    location_id_3,
    maintenance_type,
    maint_start_date,
    maint_end_date,
    technician_name,
    work_order_ref,
    description,
    next_maintenance_date,
    status
)
SELECT 
    a.tenant_id,
    a.asset_id,
    a.asset_name,
    a.location_id_1,
    a.location_id_2,
    a.location_id_3,
    c.maintenance_type,
    CAST(GETDATE() AS DATE) AS maint_start_date,                    -- today
    DATEADD(DAY, c.interval_days, CAST(GETDATE() AS DATE)) AS maint_end_date, -- future date
    c.technician AS technician_name,
    c.work_order AS work_order_ref,
    c.title AS description,
    DATEADD(DAY, c.interval_days, CAST(GETDATE() AS DATE)) AS next_maintenance_date,
    'scheduled' AS status

FROM [Tool_Monitoring_System].[dbo].[assets] AS a
INNER JOIN [Tool_Monitoring_System].[dbo].[checklist_template] AS c
    ON a.tenant_id = c.tenant_id

WHERE 
    a.tenant_id = '9C4FFC4D-CF21-466D-A256-34A38C0CCD59'
    -- Optional: filter specific asset or maintenance type
    -- AND a.asset_id = 'ASSET-001'
ORDER BY 
    a.asset_name, 
    c.checklist_id;
	
-----------------------------------
-- Maintenance Checklist (staging)

-----------------------------------	
	
SELECT 
	mc.maintenance_checklist_id,
    mc.tenant_id,
    mc.asset_id,
    mc.asset_name,
    mc.location_id_1,
    mc.location_id_2,
    mc.location_id_3,
    mc.work_order_ref,
    mc.checklist_id,
    mc.technician,
    mc.status,
    mc.date_started,
    mc.date_completed,

    mct.task_order,
    mct.task_text,
    mct.result_value,
    mct.result_notes,
    mct.completed_at

FROM dbo.maintenance_checklist mc
LEFT JOIN dbo.maintenance_checklist_tasks mct
    ON  mct.asset_id       = mc.asset_id
    AND mct.checklist_id   = mc.checklist_id
    AND mct.work_order_ref = mc.work_order_ref
ORDER BY 
    mc.work_order_ref,
    mct.task_order;


-----------------------------------
--DELETE from active tables (child first, then master)

-----------------------------------

        $deleteTasksSql = "DELETE FROM dbo.maintenance_checklist_tasks WHERE maintenance_checklist_id = ?";
        $deleteTasksStmt = $this->conn->prepare($deleteTasksSql);
        $deleteTasksStmt->execute([$maintenanceChecklistId]);

        $deleteMasterSql = "DELETE FROM dbo.maintenance_checklist WHERE maintenance_checklist_id = ?";
        $deleteMasterStmt = $this->conn->prepare($deleteMasterSql);
        $deleteMasterStmt->execute([$maintenanceChecklistId]);

        $this->conn->commit();
        return true;



-----------------------------------
--DELETE ROW
-----------------------------------		
	
	  -- 1. Delete all tasks (child table)
DELETE FROM [Tool_Monitoring_System].dbo.maintenance_checklist_tasks;

-- 2. Delete all checklist instances (parent table)
DELETE FROM [Tool_Monitoring_System].dbo.maintenance_checklist;

DROP TABLE  dbo.maintenance_checklist_tasks;
DROP TABLE  dbo.maintenance_checklist;


-----------------------------------
--ALTER 
-----------------------------------	
    ALTER TABLE registered_tools
    ADD  col_pos int

    ALTER TABLE organizations
    ADD CONSTRAINT UQ_organizations_email UNIQUE (email);


---------------------------------------------
-- 	 Query: Get All Completed Work Orders --
--------------------------------------
SELECT 
    tc.CONSTRAINT_NAME,
    tc.CONSTRAINT_TYPE,
    kcu.COLUMN_NAME
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
    ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME

WHERE tc.TABLE_NAME = 'maintenance_checklist';


EXEC sp_helpindex 'dbo.maintenance_checklist';

SELECT * FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS 
WHERE TABLE_NAME='maintenance_checklist';	


-- Get completed checklists with task counts
SELECT 
    cwo.maintenance_checklist_id,
    cwo.asset_name,
    cwo.technician,
    cwo.date_completed,
    cwo.archived_at,
    COUNT(cwot.task_id) as task_count
FROM dbo.completed_work_order cwo
LEFT JOIN dbo.completed_work_order_tasks cwot 
    ON cwo.maintenance_checklist_id = cwot.maintenance_checklist_id
WHERE cwo.tenant_id = '9C4FFC4D-CF21-466D-A256-34A38C0CCD...'
GROUP BY 
    cwo.maintenance_checklist_id, cwo.asset_name, cwo.technician, 
    cwo.date_completed, cwo.archived_at
ORDER BY cwo.date_completed DESC;


IF OBJECT_ID('dbo.maintenance_checklist_tasks', 'U') IS NOT NULL
    DROP TABLE dbo.maintenance_checklist_tasks;

DROP TABLE  dbo.maintenance_checklist;

---*************************************

-- Archive master record (direct copy - same column names!)
INSERT INTO completed_work_order (
    maintenance_checklist_id,  -- ✅ Same column name!
    tenant_id, asset_id, asset_name, 
    location_id_1, location_id_2, location_id_3,
    work_order_ref, checklist_id, maintenance_type, 
    technician, status, date_started, date_completed
)
SELECT 
    maintenance_checklist_id,  -- ✅ Direct copy
    tenant_id, asset_id, asset_name,
    location_id_1, location_id_2, location_id_3,
    work_order_ref, checklist_id, maintenance_type,
    technician, status, date_started, date_completed
FROM dbo.maintenance_checklist 
WHERE id = @StagingRecordId;

-- Archive child tasks (direct copy - same column names!)
INSERT INTO completed_work_order_tasks (
    maintenance_checklist_id,  -- ✅ Same column name!
    task_id, tenant_id, asset_id, 
    checklist_id, work_order_ref, task_order, 
    task_text, result_value, result_notes, completed_at
)
SELECT 
    maintenance_checklist_id,  -- ✅ Direct copy
    task_id, tenant_id, asset_id,
    checklist_id, work_order_ref, task_order,
    task_text, result_value, result_notes, completed_at
FROM dbo.maintenance_checklist_tasks 
WHERE maintenance_checklist_id = @MaintenanceChecklistId;

--*************************************************

-- Check if already archived
IF NOT EXISTS (
    SELECT 1 FROM dbo.completed_work_order 
    WHERE maintenance_checklist_id = @MaintenanceChecklistId
)
BEGIN
    -- Perform archiving
END


-- Same query works for both staging and history
-- Get checklist by maintenance_checklist_id
SELECT * FROM maintenance_checklist WHERE maintenance_checklist_id = 8;
SELECT * FROM completed_work_order WHERE maintenance_checklist_id = 8;

-- Get tasks by maintenance_checklist_id  
SELECT * FROM maintenance_checklist_tasks WHERE maintenance_checklist_id = 8;
SELECT * FROM completed_work_order_tasks WHERE maintenance_checklist_id = 8;


--***************************************************************************************
-- PHP Code Benefits
// Same method works for both table types
public function getChecklistByCommonId(int $maintenanceChecklistId, string $table): array
{
    $sql = "SELECT * FROM dbo.{$table} WHERE maintenance_checklist_id = ?";
    // ... execute with same parameter
}

// Archiving is just copying identical structures
public function archiveChecklist(int $maintenanceChecklistId): bool
{
    // Copy master
    $this->copyToHistory($maintenanceChecklistId, 'maintenance_checklist', 'completed_work_order');
    
    // Copy tasks
    $this->copyToHistory($maintenanceChecklistId, 'maintenance_checklist_tasks', 'completed_work_order_tasks');
}