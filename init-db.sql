/* -- init-db.sql---*/

/* =====================================================
   ORGANIZATIONS
===================================================== */
CREATE TABLE organizations (
    id INT IDENTITY(1,1) PRIMARY KEY,
    org_id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() UNIQUE,
    org_name NVARCHAR(255) NOT NULL,
    org_alias NVARCHAR(100),
    email NVARCHAR(255) NOT NULL UNIQUE,
    password_hash NVARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT GETDATE()
);

   -- Add unique constraints (safe if data is already unique)
    ALTER TABLE organizations
    ADD CONSTRAINT UQ_organizations_org_id UNIQUE (org_id);

    ALTER TABLE organizations
    ADD CONSTRAINT UQ_organizations_email UNIQUE (email);

/* =====================================================
   ASSETS (Multi-Tenant)
===================================================== */
CREATE TABLE assets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    tenant_id UNIQUEIDENTIFIER NOT NULL,
    asset_id NVARCHAR(50) NOT NULL UNIQUE,
    asset_name NVARCHAR(255) NOT NULL,
    serial_no NVARCHAR(255) NOT NULL,
    equipment_description NVARCHAR(255),
    cost_center NVARCHAR(255) NOT NULL,
    department NVARCHAR(255) NOT NULL,
    location_id_1 NVARCHAR(100),
    location_id_2 NVARCHAR(100),
    location_id_3 NVARCHAR(100),
    vendor_id NVARCHAR(100),
    mfg_code NVARCHAR(100),
    status NVARCHAR(20) DEFAULT 'active',
    created_at DATETIME2 DEFAULT SYSDATETIME(),

    -- ⭐ FIX: Ensure tenant_id cannot orphan an asset
    CONSTRAINT FK_assets_organizations
    FOREIGN KEY (tenant_id) REFERENCES organizations(org_id)
);

CREATE INDEX IX_assets_tenant_id ON assets (tenant_id);



/* =====================================================
   ASSET SCHEDULED MAINTENANCE
===================================================== */
CREATE TABLE asset_maintenance (
    id INT IDENTITY(1,1) PRIMARY KEY,
    tenant_id UNIQUEIDENTIFIER NOT NULL,
    asset_id NVARCHAR(50) NOT NULL,
    maintenance_type NVARCHAR(50) NOT NULL,
    maintenance_date DATE NOT NULL,
    technician_name NVARCHAR(255),
    work_order NVARCHAR(255),
    description NVARCHAR(MAX),
    next_maintenance_date DATE,
    status NVARCHAR(50) DEFAULT 'scheduled',
    created_at DATETIME2 DEFAULT SYSDATETIME(),
    updated_at DATETIME2 DEFAULT SYSDATETIME(),

    CONSTRAINT FK_assetMaintenance_organizations
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id),

    -- ⭐ FIX: asset_id FK must match assets.asset_id (NVARCHAR(50))
    CONSTRAINT FK_assetMaintenance_assets
        FOREIGN KEY (asset_id) REFERENCES assets(asset_id)
);

CREATE INDEX IX_assetMaintenance_tenant_id ON asset_maintenance (tenant_id);
CREATE INDEX IX_assetMaintenance_asset_id ON asset_maintenance (asset_id);


-- 1. Drop foreign key
ALTER TABLE asset_maintenance
DROP CONSTRAINT FK_assetMaintenance_assets;

-- 2. Drop old global unique constraint
ALTER TABLE assets
DROP CONSTRAINT UQ__assets__D28B561C22187DE1;

-- 3. Add new per-tenant unique constraint
ALTER TABLE assets
ADD CONSTRAINT UQ_assets_tenant_asset_id 
UNIQUE (tenant_id, asset_id);

-- 4. Recreate foreign key with both columns
ALTER TABLE asset_maintenance
ADD CONSTRAINT FK_assetMaintenance_assets 
FOREIGN KEY (tenant_id, asset_id) 
REFERENCES assets (tenant_id, asset_id);



/* =====================================================
   CHECKLIST TEMPLATE
===================================================== */
CREATE TABLE checklist_template (
    id INT IDENTITY(1,1) PRIMARY KEY,
    tenant_id UNIQUEIDENTIFIER NOT NULL,
    checklist_id NVARCHAR(255) NOT NULL,
    maintenance_type NVARCHAR(255),
    work_order NVARCHAR(255),
    technician NVARCHAR(255),
    interval_days INT DEFAULT 30,
    description NVARCHAR(MAX),
    created_by UNIQUEIDENTIFIER,
    updated_by UNIQUEIDENTIFIER,
    created_at DATETIME2 DEFAULT SYSDATETIME(),
    updated_at DATETIME2 DEFAULT SYSDATETIME(),

    CONSTRAINT FK_checklist_template_org
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id),

    -- ⭐ Important: Composite key for referencing checklist instances
    CONSTRAINT UQ_checklist_template_tenant_checklist
        UNIQUE (tenant_id, checklist_id)
);

CREATE INDEX IX_checklist_template_tenant_id ON checklist_template (tenant_id);

/* =====================================================
   CHECKLIST TASKS (Child)
===================================================== */
CREATE TABLE checklist_tasks (
    id INT IDENTITY(1,1) PRIMARY KEY,
    tenant_id UNIQUEIDENTIFIER NOT NULL,
    checklist_id NVARCHAR(255) NOT NULL,
    task_order INT NOT NULL,
    task_text NVARCHAR(MAX) NOT NULL,

    CONSTRAINT FK_checklist_tasks_template
        FOREIGN KEY (tenant_id, checklist_id)
        REFERENCES checklist_template (tenant_id, checklist_id)
        ON DELETE CASCADE,

    CONSTRAINT UQ_checklist_task_order
        UNIQUE (tenant_id, checklist_id, task_order)
);

CREATE INDEX IX_checklist_tasks_lookup 
    ON checklist_tasks (tenant_id, checklist_id, task_order);

/* =====================================================
   ROUTINE WORK ORDERS
===================================================== */
CREATE TABLE routine_work_orders (
    id INT IDENTITY(1,1) PRIMARY KEY,
    tenant_id UNIQUEIDENTIFIER NOT NULL,
    asset_id NVARCHAR(50) NOT NULL,
    asset_name NVARCHAR(255) NOT NULL,
    location_id_1 NVARCHAR(100),
    location_id_2 NVARCHAR(100),
    location_id_3 NVARCHAR(100),
    checklist_id NVARCHAR(255) NOT NULL,
    maintenance_type NVARCHAR(50) NOT NULL,
    maint_start_date DATE NOT NULL,
    maint_end_date DATE NOT NULL,
    technician_name NVARCHAR(255),
    work_order_ref NVARCHAR(255),
    description NVARCHAR(MAX),
    next_maintenance_date DATE,
    status NVARCHAR(50),

    CONSTRAINT FK_routineWO_asset
        FOREIGN KEY (asset_id) REFERENCES assets(asset_id),

    -- ⭐ FIX: Add missing tenant FK
    CONSTRAINT FK_routineWO_org
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id)
);

/* ++++++++++++++++++++++++++++++++++++++++++++++++++ */

/* ------------------------------------------------------------------
   Master table: maintenance_checklist
   ------------------------------------------------------------------ */
CREATE TABLE dbo.maintenance_checklist
(
    maintenance_checklist_id INT IDENTITY(1,1) NOT NULL,
    tenant_id UNIQUEIDENTIFIER NOT NULL,

    asset_id NVARCHAR(50) NOT NULL,
    asset_name NVARCHAR(255) NULL,

    location_id_1 NVARCHAR(50) NULL,
    location_id_2 NVARCHAR(50) NULL,
    location_id_3 NVARCHAR(50) NULL,

    work_order_ref NVARCHAR(100) NULL,
    checklist_id NVARCHAR(50) NOT NULL,   -- keep original name

    maintenance_type NVARCHAR(100) NULL,
    technician_name NVARCHAR(150) NULL,   -- keep original name

    status NVARCHAR(30) NULL,             -- PHP handles status
    date_started DATETIME2 NULL,
    date_completed DATETIME2 NULL,

    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NULL,
    created_by NVARCHAR(150) NULL,
    updated_by NVARCHAR(150) NULL,

    CONSTRAINT PK_maintenance_checklist 
        PRIMARY KEY (maintenance_checklist_id),

    CONSTRAINT UQ_tenant_checklist 
        UNIQUE (tenant_id, checklist_id, asset_id),

    CONSTRAINT UQ_checklist_id_tenant
        UNIQUE (maintenance_checklist_id, tenant_id)
);

CREATE INDEX IX_checklist_tenant_asset
ON dbo.maintenance_checklist (tenant_id, asset_id);

CREATE INDEX IX_checklist_status
ON dbo.maintenance_checklist (tenant_id, status);


/* ------------------------------------------------------------------
   Child table: maintenance_checklist_tasks
   ------------------------------------------------------------------ */
CREATE TABLE dbo.maintenance_checklist_tasks
(
    task_id INT IDENTITY(1,1) NOT NULL,

    maintenance_checklist_id INT NOT NULL,
    tenant_id UNIQUEIDENTIFIER NOT NULL,

    task_order INT NOT NULL,
    task_text NVARCHAR(500) NOT NULL,

    task_status NVARCHAR(30) NULL,  -- PHP will handle validation

    result_value NVARCHAR(255) NULL,
    result_notes NVARCHAR(MAX) NULL,

    completed_at DATETIME2 NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    created_by NVARCHAR(150) NULL,
    completed_by NVARCHAR(150) NULL,

    CONSTRAINT PK_maintenance_checklist_tasks 
        PRIMARY KEY (task_id),

    CONSTRAINT FK_tasks_checklist
        FOREIGN KEY (maintenance_checklist_id, tenant_id)
        REFERENCES dbo.maintenance_checklist(maintenance_checklist_id, tenant_id)
        ON DELETE CASCADE
);

CREATE INDEX IX_tasks_checklist
ON dbo.maintenance_checklist_tasks (tenant_id, maintenance_checklist_id);

CREATE INDEX IX_tasks_status
ON dbo.maintenance_checklist_tasks (tenant_id, task_status);


/* ++++++++++++++++++++++++++++++++++++++++++++++++++ */


	
	


/* ------------------------------------------------------------------
   Master table: completed_work_order
   ------------------------------------------------------------------ */
CREATE TABLE dbo.completed_work_order
(
    maintenance_checklist_id INT IDENTITY(1,1) NOT NULL,
    tenant_id UNIQUEIDENTIFIER NOT NULL,
    asset_id NVARCHAR(50) NOT NULL,
    asset_name NVARCHAR(255) NULL,
    location_id_1 NVARCHAR(50) NULL,
    location_id_2 NVARCHAR(50) NULL,
    location_id_3 NVARCHAR(50) NULL,
    work_order_ref NVARCHAR(100) NULL,
    checklist_id NVARCHAR(50) NOT NULL,
    maintenance_type NVARCHAR(100) NULL,
    technician_name NVARCHAR(150) NULL,
    status NVARCHAR(30) NULL,
    date_started DATETIME2 NULL,
    date_completed DATETIME2 NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    updated_at DATETIME2 NULL,
    created_by NVARCHAR(150) NULL,
    updated_by NVARCHAR(150) NULL,

    CONSTRAINT PK_completed_work_order 
        PRIMARY KEY (maintenance_checklist_id),

    -- ✅ Keep this for FK safety
    CONSTRAINT UQ_completed_work_order_id_tenant
        UNIQUE (maintenance_checklist_id, tenant_id)
);


/* ------------------------------------------------------------------
   Child table: completed_work_order_tasks
   ------------------------------------------------------------------ */
CREATE TABLE dbo.completed_work_order_tasks
(
    task_id INT IDENTITY(1,1) NOT NULL,
    maintenance_checklist_id INT NOT NULL,
    tenant_id UNIQUEIDENTIFIER NOT NULL,

    task_order INT NOT NULL,
    task_text NVARCHAR(500) NOT NULL,

    task_status NVARCHAR(30) NULL,
    result_value NVARCHAR(255) NULL,
    result_notes NVARCHAR(MAX) NULL,

    completed_at DATETIME2 NULL,
    created_at DATETIME2 NOT NULL DEFAULT SYSUTCDATETIME(),
    created_by NVARCHAR(150) NULL,
    completed_by NVARCHAR(150) NULL,

    CONSTRAINT PK_completed_work_order_tasks
        PRIMARY KEY (task_id),

    CONSTRAINT FK_completed_work_order_tasks
        FOREIGN KEY (maintenance_checklist_id, tenant_id)
        REFERENCES dbo.completed_work_order(maintenance_checklist_id, tenant_id)
        ON DELETE CASCADE
);

CREATE INDEX IX_completed_work_order_tasks_checklist
ON dbo.completed_work_order_tasks (tenant_id, maintenance_checklist_id);

CREATE INDEX IX_completed_work_order_tasks_status
ON dbo.completed_work_order_tasks (tenant_id, task_status);

--  BID  ----

/*------------------------------- 
Group-Location Map
--------------------------------- */
CREATE TABLE group_location_map (
    id INT IDENTITY(1,1) PRIMARY KEY,
    group_code NVARCHAR(255) NOT NULL,
    location_code NVARCHAR(255) NOT NULL,
    group_name NVARCHAR(255),
    location_name NVARCHAR(255),
	page_id NVARCHAR(255) NOT NULL,
	page_name NVARCHAR(255),
    org_id UNIQUEIDENTIFIER NOT NULL,
    created_at DATETIME2 DEFAULT GETDATE(),
    CONSTRAINT UQ_group_location_org 
        UNIQUE (org_id, group_code, location_code),
    CONSTRAINT FK_group_location_map_org 
        FOREIGN KEY (org_id) 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE
);
ALTER TABLE group_location_map 
ADD seq_id INT NULL;

-- Registered Tools (for tools to be monitored at BID)
    CREATE TABLE registered_tools (
        id INT IDENTITY(1,1) PRIMARY KEY,
        org_id UNIQUEIDENTIFIER NOT NULL
            CONSTRAINT FK_registered_tools_org 
            FOREIGN KEY REFERENCES organizations(org_id) 
            ON DELETE CASCADE,
        asset_id NVARCHAR(255) NOT NULL,
        entity NVARCHAR(255) NOT NULL,
        group_code INT NOT NULL,
        location_code INT NOT NULL,
        created_at DATETIME2 DEFAULT GETDATE(),
		row_pos (INT) NOT NULL,
		col_pos (INT) NOT NULL,
        -- Unique per org
        CONSTRAINT UQ_org_asset UNIQUE (org_id, asset_id)
    );

	-- Indexes for performance
    CREATE INDEX IX_registered_tools_org_id ON registered_tools (org_id);
    CREATE INDEX IX_registered_tools_asset_id ON registered_tools (asset_id);
    CREATE INDEX IX_registered_tools_codes ON registered_tools (group_code, location_code);


    CREATE TABLE tool_state (
        id INT IDENTITY(1,1) PRIMARY KEY,
        org_id UNIQUEIDENTIFIER NOT NULL
            CONSTRAINT FK_tool_state_org 
            FOREIGN KEY REFERENCES organizations(org_id) 
            ON DELETE CASCADE,
        group_code NVARCHAR(100) NOT NULL,
        location_code NVARCHAR(100) NOT NULL,
        col_1 NVARCHAR(255),  -- asset_id (indexed via UQ constraint)
        col_2 NVARCHAR(255),  -- entity name
        col_3 NVARCHAR(100),  -- status 
        col_4 NVARCHAR(100),  -- machine state
        col_5 NVARCHAR(100),  -- sub-state
        col_6 NVARCHAR(50),   -- timestamp (as string, e.g., ISO8601)
        col_7 NVARCHAR(100),  -- operator
        col_8 NVARCHAR(100),  -- posted_by
        col_9 NVARCHAR(MAX),
        col_10 NVARCHAR(MAX),
        col_11 NVARCHAR(MAX),
        col_12 NVARCHAR(MAX),
        col_13 NVARCHAR(MAX),
        col_14 NVARCHAR(MAX),
        col_15 NVARCHAR(MAX),
        col_16 NVARCHAR(MAX),
        -- Ensure one row per org + asset_id (col_1)
        CONSTRAINT UQ_tool_state_org_asset UNIQUE (org_id, col_1)
    );


    CREATE TABLE tool_state_metadata (
        org_id UNIQUEIDENTIFIER NOT NULL
            CONSTRAINT FK_tool_state_metadata_org 
            FOREIGN KEY REFERENCES organizations(org_id) 
            ON DELETE CASCADE,
        col_number NVARCHAR(10) NOT NULL,
        label NVARCHAR(100) NOT NULL,
        description NVARCHAR(500),
        data_type NVARCHAR(50),
        PRIMARY KEY (org_id, col_number),
        -- Validate col_number: 'col_1' to 'col_16'
        CONSTRAINT CHK_col_number 
            CHECK (col_number LIKE 'col_[1-9]' OR col_number LIKE 'col_1[0-6]')
    );
	
	-- Create mode_color table for configurable state labels & Tailwind classes
	CREATE TABLE mode_color (
		id INT IDENTITY(1,1) PRIMARY KEY,
		org_id UNIQUEIDENTIFIER NOT NULL
			CONSTRAINT FK_mode_color_org 
			FOREIGN KEY REFERENCES organizations(org_id) 
			ON DELETE CASCADE,
		mode_key NVARCHAR(50) NOT NULL,          -- e.g., 'IDLE', 'PRODUCTION'
		label NVARCHAR(100) NOT NULL,            -- e.g., 'IDLE', 'PRODUCTION'
		tailwind_class NVARCHAR(100) NOT NULL,   -- e.g., 'bg-blue-400'
		
		CONSTRAINT UQ_mode_color_org_mode UNIQUE (org_id, mode_key)
	);
	
-- Machine Parts List (Single Table - per entity)
CREATE TABLE machine_parts_list (
    id INT IDENTITY(1,1) PRIMARY KEY,
    org_id UNIQUEIDENTIFIER NOT NULL
        CONSTRAINT FK_parts_org 
        FOREIGN KEY REFERENCES organizations(org_id) 
        ON DELETE CASCADE,

    -- Entity linkage
    asset_id NVARCHAR(255) NOT NULL,
    entity NVARCHAR(255) NOT NULL,

    -- Part details
    part_id NVARCHAR(255) NOT NULL,
    part_name NVARCHAR(255) NOT NULL,
    serial_no NVARCHAR(255),
    vendor_id NVARCHAR(100),
    mfg_code NVARCHAR(100),
    sap_code NVARCHAR(100),
    category NVARCHAR(50),  -- NEW: HIGH/MEDIUM/LOW
    parts_available_on_hand INT NOT NULL DEFAULT 0,
    description NVARCHAR(MAX),
    image_path NVARCHAR(500),
    created_at DATETIME2 DEFAULT GETDATE(),

    -- Unique per entity + part_id
    CONSTRAINT UQ_entity_part UNIQUE (org_id, asset_id, entity, part_id)
);

-- Index for performance
CREATE INDEX IX_parts_org_asset_entity ON machine_parts_list (org_id, asset_id, entity);	

