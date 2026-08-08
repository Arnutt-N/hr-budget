-- Migration: 062_create_organization_summary_view.sql
-- Description: Create a flattened view for easy reporting and querying
-- Supports hierarchy up to 5 levels (Ministry -> Dept -> Division -> Section -> SubSection)

CREATE OR REPLACE VIEW v_organization_summary AS
SELECT 
    o.id,
    o.code,
    o.name_th,
    o.name_th as org_name,
    o.abbreviation,
    o.org_type,
    o.level,
    o.is_active,
    o.budget_allocated,
    o.region,
    o.province_code,
    o.provincial_group,
    o.provincial_zone,
    o.inspection_zone,
    o.custom_zone,
    
    -- Immediate Parent
    p1.id as parent_id,
    p1.name_th as parent_name,
    
    -- Resolve Ministry (Find first ancestor with type 'ministry', prioritizing top-down logic if needed, but here searching upwards)
    COALESCE(
        CASE WHEN o.org_type = 'ministry' THEN o.name_th END,
        CASE WHEN p1.org_type = 'ministry' THEN p1.name_th END,
        CASE WHEN p2.org_type = 'ministry' THEN p2.name_th END,
        CASE WHEN p3.org_type = 'ministry' THEN p3.name_th END,
        CASE WHEN p4.org_type = 'ministry' THEN p4.name_th END
    ) as ministry_name,
    
    COALESCE(
        CASE WHEN o.org_type = 'ministry' THEN o.abbreviation END,
        CASE WHEN p1.org_type = 'ministry' THEN p1.abbreviation END,
        CASE WHEN p2.org_type = 'ministry' THEN p2.abbreviation END,
        CASE WHEN p3.org_type = 'ministry' THEN p3.abbreviation END,
        CASE WHEN p4.org_type = 'ministry' THEN p4.abbreviation END
    ) as ministry_abbr,

    -- Resolve Department
    COALESCE(
        CASE WHEN o.org_type = 'department' THEN o.name_th END,
        CASE WHEN p1.org_type = 'department' THEN p1.name_th END,
        CASE WHEN p2.org_type = 'department' THEN p2.name_th END,
        CASE WHEN p3.org_type = 'department' THEN p3.name_th END,
        CASE WHEN p4.org_type = 'department' THEN p4.name_th END
    ) as department_name,

    COALESCE(
        CASE WHEN o.org_type = 'department' THEN o.abbreviation END,
        CASE WHEN p1.org_type = 'department' THEN p1.abbreviation END,
        CASE WHEN p2.org_type = 'department' THEN p2.abbreviation END,
        CASE WHEN p3.org_type = 'department' THEN p3.abbreviation END,
        CASE WHEN p4.org_type = 'department' THEN p4.abbreviation END
    ) as department_abbr,

    -- Resolve Division
    COALESCE(
        CASE WHEN o.org_type = 'division' THEN o.name_th END,
        CASE WHEN p1.org_type = 'division' THEN p1.name_th END,
        CASE WHEN p2.org_type = 'division' THEN p2.name_th END,
        CASE WHEN p3.org_type = 'division' THEN p3.name_th END,
        CASE WHEN p4.org_type = 'division' THEN p4.name_th END
    ) as division_name

FROM organizations o
LEFT JOIN organizations p1 ON o.parent_id = p1.id
LEFT JOIN organizations p2 ON p1.parent_id = p2.id
LEFT JOIN organizations p3 ON p2.parent_id = p3.id
LEFT JOIN organizations p4 ON p3.parent_id = p4.id;
