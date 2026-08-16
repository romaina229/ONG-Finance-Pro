-- Finance Pro — synchronization engine primitives
-- PostgreSQL 16+

CREATE TABLE sync_operations (
  operation_id UUID PRIMARY KEY,
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  device_id UUID NOT NULL REFERENCES devices(id),
  user_id UUID NOT NULL REFERENCES users(id),
  entity_type VARCHAR(50) NOT NULL,
  local_id UUID NOT NULL,
  server_id UUID,
  operation VARCHAR(10) NOT NULL CHECK (operation IN ('insert','update','delete')),
  base_server_version BIGINT,
  payload JSONB,
  status VARCHAR(20) NOT NULL DEFAULT 'accepted' CHECK (status IN ('accepted','conflict','rejected')),
  response JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  processed_at TIMESTAMPTZ
);
CREATE INDEX idx_sync_operations_org_created ON sync_operations(organization_id,created_at);
CREATE INDEX idx_sync_operations_entity ON sync_operations(entity_type,server_id,created_at);

CREATE TABLE sync_changes (
  sequence BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  entity_type VARCHAR(50) NOT NULL,
  entity_id UUID NOT NULL,
  operation_id UUID NOT NULL REFERENCES sync_operations(operation_id),
  operation VARCHAR(10) NOT NULL CHECK (operation IN ('insert','update','delete')),
  server_version BIGINT NOT NULL,
  payload JSONB,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE INDEX idx_sync_changes_cursor ON sync_changes(organization_id,sequence);

CREATE TABLE sync_tombstones (
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  entity_type VARCHAR(50) NOT NULL,
  entity_id UUID NOT NULL,
  server_version BIGINT NOT NULL,
  deleted_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY(organization_id,entity_type,entity_id)
);

CREATE TABLE sync_cursors (
  device_id UUID NOT NULL REFERENCES devices(id) ON DELETE CASCADE,
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  last_sequence BIGINT NOT NULL DEFAULT 0 CHECK(last_sequence >= 0),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY(device_id,organization_id)
);

-- Une seule opération avec la même clé d'idempotence peut être traitée.
CREATE UNIQUE INDEX uq_sync_operation_device_local
ON sync_operations(device_id,entity_type,local_id,operation_id);

COMMENT ON TABLE sync_operations IS 'Journal idempotent des mutations reçues des appareils.';
COMMENT ON TABLE sync_changes IS 'Flux ordonné utilisé par le pull Offline First.';
COMMENT ON TABLE sync_tombstones IS 'Suppressions conservées pour propagation aux appareils hors ligne.';
