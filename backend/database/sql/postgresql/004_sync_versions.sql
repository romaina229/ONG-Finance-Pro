-- Finance Pro — version registry for optimistic concurrency

CREATE TABLE sync_versions (
    organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
    entity_type VARCHAR(50) NOT NULL,
    entity_id UUID NOT NULL,
    version BIGINT NOT NULL DEFAULT 0 CHECK (version > 0),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    PRIMARY KEY (organization_id, entity_type, entity_id)
);

CREATE INDEX idx_sync_versions_entity
    ON sync_versions(organization_id, entity_type, entity_id);
