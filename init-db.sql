-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

/* =====================================================
   ORGANIZATIONS
===================================================== */
CREATE TABLE organizations (
    id SERIAL PRIMARY KEY,
    org_id UUID NOT NULL DEFAULT uuid_generate_v4() UNIQUE,
    org_name VARCHAR(255) NOT NULL,
    org_alias VARCHAR(100),
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE organizations
ADD CONSTRAINT UQ_organizations_org_id UNIQUE (org_id);

ALTER TABLE organizations
ADD CONSTRAINT UQ_organizations_email UNIQUE (email);

-- Run this once in your PostgreSQL DB
ALTER TABLE organizations 
ADD COLUMN IF NOT EXISTS remember_token VARCHAR(64) NULL;
/* =====================================================
   ASSETS (Multi-Tenant)
===================================================== */
CREATE TABLE assets (
    id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    asset_id VARCHAR(50) NOT NULL,
    asset_name VARCHAR(255) NOT NULL,
    serial_no VARCHAR(255) NOT NULL,
    equipment_description VARCHAR(255),
    cost_center VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    location_id_1 VARCHAR(100),
    location_id_2 VARCHAR(100),
    location_id_3 VARCHAR(100),
    vendor_id VARCHAR(100),
    mfg_code VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_assets_organizations
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id)
);

-- Drop global unique (not needed in PG if using composite below)
-- Then add per-tenant unique
ALTER TABLE assets
ADD CONSTRAINT UQ_assets_tenant_asset_id 
UNIQUE (tenant_id, asset_id);

CREATE INDEX IX_assets_tenant_id ON assets (tenant_id);


/* =====================================================
   ASSET SCHEDULED MAINTENANCE
===================================================== */
CREATE TABLE asset_maintenance (
    id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    asset_id VARCHAR(50) NOT NULL,
    maintenance_type VARCHAR(50) NOT NULL,
    maintenance_date DATE NOT NULL,
    technician_name VARCHAR(255),
    work_order VARCHAR(255),
    description TEXT,
    next_maintenance_date DATE,
    status VARCHAR(50) DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_assetMaintenance_organizations
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id),

    CONSTRAINT FK_assetMaintenance_assets
        FOREIGN KEY (tenant_id, asset_id) REFERENCES assets(tenant_id, asset_id)
);

CREATE INDEX IX_assetMaintenance_tenant_id ON asset_maintenance (tenant_id);
CREATE INDEX IX_assetMaintenance_asset_id ON asset_maintenance (asset_id);


/* =====================================================
   CHECKLIST TEMPLATE
===================================================== */
CREATE TABLE checklist_template (
    id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    checklist_id VARCHAR(255) NOT NULL,
    maintenance_type VARCHAR(255),
    work_order VARCHAR(255),
    technician VARCHAR(255),
    interval_days INT DEFAULT 30,
    description TEXT,
    created_by UUID,
    updated_by UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT FK_checklist_template_org
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id),

    CONSTRAINT UQ_checklist_template_tenant_checklist
        UNIQUE (tenant_id, checklist_id)
);

CREATE INDEX IX_checklist_template_tenant_id ON checklist_template (tenant_id);


/* =====================================================
   CHECKLIST TASKS (Child)
===================================================== */
CREATE TABLE checklist_tasks (
    id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    checklist_id VARCHAR(255) NOT NULL,
    task_order INT NOT NULL,
    task_text TEXT NOT NULL,

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
    id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    asset_id VARCHAR(50) NOT NULL,
    asset_name VARCHAR(255) NOT NULL,
    location_id_1 VARCHAR(100),
    location_id_2 VARCHAR(100),
    location_id_3 VARCHAR(100),
    checklist_id VARCHAR(255) NOT NULL,
    maintenance_type VARCHAR(50) NOT NULL,
    maint_start_date DATE NOT NULL,
    maint_end_date DATE NOT NULL,
    technician_name VARCHAR(255),
    work_order_ref VARCHAR(255),
    description TEXT,
    next_maintenance_date DATE,
    status VARCHAR(50),

    CONSTRAINT FK_routineWO_asset
        FOREIGN KEY (tenant_id, asset_id) REFERENCES assets(tenant_id, asset_id),

    CONSTRAINT FK_routineWO_org
        FOREIGN KEY (tenant_id) REFERENCES organizations(org_id)
);


/* =====================================================
   MAINTENANCE CHECKLIST (Master)
===================================================== */
CREATE TABLE maintenance_checklist (
    maintenance_checklist_id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    asset_id VARCHAR(50) NOT NULL,
    asset_name VARCHAR(255),
    location_id_1 VARCHAR(50),
    location_id_2 VARCHAR(50),
    location_id_3 VARCHAR(50),
    work_order_ref VARCHAR(100),
    checklist_id VARCHAR(50) NOT NULL,
    maintenance_type VARCHAR(100),
    technician_name VARCHAR(150),
    status VARCHAR(30),
    date_started TIMESTAMP,
    date_completed TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    created_by VARCHAR(150),
    updated_by VARCHAR(150),

    CONSTRAINT UQ_tenant_checklist 
        UNIQUE (tenant_id, checklist_id, asset_id),

    CONSTRAINT UQ_checklist_id_tenant
        UNIQUE (maintenance_checklist_id, tenant_id)
);

CREATE INDEX IX_checklist_tenant_asset
ON maintenance_checklist (tenant_id, asset_id);

CREATE INDEX IX_checklist_status
ON maintenance_checklist (tenant_id, status);


/* =====================================================
   MAINTENANCE CHECKLIST TASKS (Child)
===================================================== */
CREATE TABLE maintenance_checklist_tasks (
    task_id SERIAL PRIMARY KEY,
    maintenance_checklist_id INT NOT NULL,
    tenant_id UUID NOT NULL,
    task_order INT NOT NULL,
    task_text VARCHAR(500) NOT NULL,
    task_status VARCHAR(30),
    result_value VARCHAR(255),
    result_notes TEXT,
    completed_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(150),
    completed_by VARCHAR(150),

    CONSTRAINT FK_tasks_checklist
        FOREIGN KEY (maintenance_checklist_id, tenant_id)
        REFERENCES maintenance_checklist(maintenance_checklist_id, tenant_id)
        ON DELETE CASCADE
);

CREATE INDEX IX_tasks_checklist
ON maintenance_checklist_tasks (tenant_id, maintenance_checklist_id);

CREATE INDEX IX_tasks_status
ON maintenance_checklist_tasks (tenant_id, task_status);


/* =====================================================
   COMPLETED WORK ORDER (Master)
===================================================== */
CREATE TABLE completed_work_order (
    maintenance_checklist_id SERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    asset_id VARCHAR(50) NOT NULL,
    asset_name VARCHAR(255),
    location_id_1 VARCHAR(50),
    location_id_2 VARCHAR(50),
    location_id_3 VARCHAR(50),
    work_order_ref VARCHAR(100),
    checklist_id VARCHAR(50) NOT NULL,
    maintenance_type VARCHAR(100),
    technician_name VARCHAR(150),
    status VARCHAR(30),
    date_started TIMESTAMP,
    date_completed TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    created_by VARCHAR(150),
    updated_by VARCHAR(150),

    CONSTRAINT UQ_completed_work_order_id_tenant
        UNIQUE (maintenance_checklist_id, tenant_id)
);


/* =====================================================
   COMPLETED WORK ORDER TASKS (Child)
===================================================== */
CREATE TABLE completed_work_order_tasks (
    task_id SERIAL PRIMARY KEY,
    maintenance_checklist_id INT NOT NULL,
    tenant_id UUID NOT NULL,
    task_order INT NOT NULL,
    task_text VARCHAR(500) NOT NULL,
    task_status VARCHAR(30),
    result_value VARCHAR(255),
    result_notes TEXT,
    completed_at TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(150),
    completed_by VARCHAR(150),

    CONSTRAINT FK_completed_work_order_tasks
        FOREIGN KEY (maintenance_checklist_id, tenant_id)
        REFERENCES completed_work_order(maintenance_checklist_id, tenant_id)
        ON DELETE CASCADE
);

CREATE INDEX IX_completed_work_order_tasks_checklist
ON completed_work_order_tasks (tenant_id, maintenance_checklist_id);

CREATE INDEX IX_completed_work_order_tasks_status
ON completed_work_order_tasks (tenant_id, task_status);


/* =====================================================
   GROUP-LOCATION MAP
===================================================== */
CREATE TABLE group_location_map (
    id SERIAL PRIMARY KEY,
    group_code VARCHAR(255) NOT NULL,
    location_code VARCHAR(255) NOT NULL,
    group_name VARCHAR(255),
    location_name VARCHAR(255),
    page_id VARCHAR(255) NOT NULL,
    page_name VARCHAR(255),
    org_id UUID NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seq_id INT,

    CONSTRAINT UQ_group_location_org 
        UNIQUE (org_id, group_code, location_code),

    CONSTRAINT FK_group_location_map_org 
        FOREIGN KEY (org_id) 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE
);


/* =====================================================
   REGISTERED TOOLS
===================================================== */
CREATE TABLE registered_tools (
    id SERIAL PRIMARY KEY,
    org_id UUID NOT NULL
        CONSTRAINT FK_registered_tools_org 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE,
    asset_id VARCHAR(255) NOT NULL,
    entity VARCHAR(255) NOT NULL,
    group_code INT NOT NULL,
    location_code INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    row_pos INT NOT NULL,
    col_pos INT NOT NULL,

    CONSTRAINT UQ_org_asset UNIQUE (org_id, asset_id)
);

CREATE INDEX IX_registered_tools_org_id ON registered_tools (org_id);
CREATE INDEX IX_registered_tools_asset_id ON registered_tools (asset_id);
CREATE INDEX IX_registered_tools_codes ON registered_tools (group_code, location_code);

-- Step 1: Add page_id column
ALTER TABLE registered_tools 
ADD COLUMN page_id VARCHAR(255) NOT NULL DEFAULT 1;




/* =====================================================
   TOOL STATE
===================================================== */
CREATE TABLE tool_state (
    id SERIAL PRIMARY KEY,
    org_id UUID NOT NULL
        CONSTRAINT FK_tool_state_org 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE,
    group_code VARCHAR(100) NOT NULL,
    location_code VARCHAR(100) NOT NULL,
    col_1 VARCHAR(255),  -- asset_id
    col_2 VARCHAR(255),  -- entity name
    col_3 VARCHAR(100),  -- status 
    col_4 VARCHAR(100),  -- machine state
    col_5 VARCHAR(100),  -- sub-state
    col_6 VARCHAR(50),   -- timestamp (ISO8601 string)
    col_7 VARCHAR(100),  -- operator
    col_8 VARCHAR(100),  -- posted_by
    col_9 TEXT,
    col_10 TEXT,
    col_11 TEXT,
    col_12 TEXT,
    col_13 TEXT,
    col_14 TEXT,
    col_15 TEXT,
    col_16 TEXT,

    CONSTRAINT UQ_tool_state_org_asset UNIQUE (org_id, col_1)
);


/* =====================================================
   TOOL STATE METADATA
===================================================== */
CREATE TABLE tool_state_metadata (
    org_id UUID NOT NULL
        CONSTRAINT FK_tool_state_metadata_org 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE,
    col_number VARCHAR(10) NOT NULL,
    label VARCHAR(100) NOT NULL,
    description VARCHAR(500),
    data_type VARCHAR(50),
    PRIMARY KEY (org_id, col_number),

    CONSTRAINT CHK_col_number 
        CHECK (col_number ~ '^col_([1-9]|1[0-6])$')
);


/* =====================================================
   MODE COLOR (for UI states)
===================================================== */
CREATE TABLE mode_color (
    id SERIAL PRIMARY KEY,
    org_id UUID NOT NULL
        CONSTRAINT FK_mode_color_org 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE,
    mode_key VARCHAR(50) NOT NULL,
    label VARCHAR(100) NOT NULL,
    tailwind_class VARCHAR(100) NOT NULL,

    CONSTRAINT UQ_mode_color_org_mode UNIQUE (org_id, mode_key)
);


/* =====================================================
   MACHINE PARTS LIST
===================================================== */
CREATE TABLE machine_parts_list (
    id SERIAL PRIMARY KEY,
    org_id UUID NOT NULL
        CONSTRAINT FK_parts_org 
        REFERENCES organizations(org_id) 
        ON DELETE CASCADE,
    asset_id VARCHAR(255) NOT NULL,
    entity VARCHAR(255) NOT NULL,
    part_id VARCHAR(255) NOT NULL,
    part_name VARCHAR(255) NOT NULL,
    serial_no VARCHAR(255),
    vendor_id VARCHAR(100),
    mfg_code VARCHAR(100),
    sap_code VARCHAR(100),
    category VARCHAR(50),
    parts_available_on_hand INT NOT NULL DEFAULT 0,
    description TEXT,
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT UQ_entity_part UNIQUE (org_id, asset_id, entity, part_id)
);

CREATE INDEX IX_parts_org_asset_entity ON machine_parts_list (org_id, asset_id, entity);