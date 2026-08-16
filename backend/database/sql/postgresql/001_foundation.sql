-- Finance Pro — PostgreSQL foundation
-- Multi-tenant, sync-ready foundation. Business modules follow in later migrations.
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TYPE user_status AS ENUM ('active','suspended','invited');
CREATE TYPE project_status AS ENUM ('draft','active','suspended','closed');
CREATE TYPE approvable_status AS ENUM ('draft','pending_approval','approved','rejected','paid');
CREATE TYPE conflict_resolution AS ENUM ('server_wins','client_wins','manual','pending');

CREATE TABLE organizations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL,
  acronym VARCHAR(50),
  legal_status VARCHAR(100),
  registration_number VARCHAR(100),
  country VARCHAR(100) NOT NULL DEFAULT 'Bénin',
  city VARCHAR(100),
  address TEXT,
  logo_path VARCHAR(500),
  default_currency CHAR(3) NOT NULL DEFAULT 'XOF',
  fiscal_year_start_month SMALLINT NOT NULL DEFAULT 1 CHECK (fiscal_year_start_month BETWEEN 1 AND 12),
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE currencies (
  code CHAR(3) PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  symbol VARCHAR(10) NOT NULL
);
INSERT INTO currencies(code,name,symbol) VALUES
('XOF','Franc CFA (UEMOA)','FCFA'),('EUR','Euro','€'),('USD','Dollar américain','$');

CREATE TABLE users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  phone VARCHAR(30),
  password_hash VARCHAR(255) NOT NULL,
  preferred_language VARCHAR(5) NOT NULL DEFAULT 'fr',
  status user_status NOT NULL DEFAULT 'invited',
  last_login_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE roles (
  id SERIAL PRIMARY KEY,
  code VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(100) NOT NULL,
  hierarchy_level SMALLINT NOT NULL DEFAULT 0
);
INSERT INTO roles(code,name,hierarchy_level) VALUES
('super_admin','Super administrateur plateforme',100),('org_admin','Administrateur ONG',90),
('coordinator','Coordinateur national',80),('project_manager','Chef de projet',60),
('accountant','Comptable',50),('auditor','Auditeur / Commissaire aux comptes',70),
('field_officer','Agent terrain',20),('donor_viewer','Bailleur (lecture seule)',10);

CREATE TABLE user_organizations (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  role_id INT NOT NULL REFERENCES roles(id),
  is_primary BOOLEAN NOT NULL DEFAULT FALSE,
  status user_status NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  UNIQUE(user_id,organization_id)
);

CREATE TABLE devices (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  device_uuid VARCHAR(100) UNIQUE NOT NULL,
  device_name VARCHAR(150),
  platform VARCHAR(30),
  app_version VARCHAR(20),
  last_sync_at TIMESTAMPTZ,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Version monotone : utilisée par le protocole de synchronisation optimiste.
CREATE TABLE sync_versions (
  entity_type VARCHAR(50) NOT NULL,
  entity_id UUID NOT NULL,
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  version BIGINT NOT NULL DEFAULT 1 CHECK (version > 0),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  PRIMARY KEY(entity_type,entity_id)
);

CREATE TABLE sync_sessions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  device_id UUID NOT NULL REFERENCES devices(id),
  user_id UUID NOT NULL REFERENCES users(id),
  organization_id UUID NOT NULL REFERENCES organizations(id),
  started_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  completed_at TIMESTAMPTZ,
  records_pushed INT NOT NULL DEFAULT 0,
  records_pulled INT NOT NULL DEFAULT 0,
  conflicts_detected INT NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'in_progress' CHECK(status IN ('in_progress','completed','failed'))
);

CREATE TABLE sync_conflicts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  organization_id UUID NOT NULL REFERENCES organizations(id) ON DELETE CASCADE,
  entity_type VARCHAR(50) NOT NULL,
  entity_id UUID NOT NULL,
  local_version JSONB NOT NULL,
  server_version JSONB NOT NULL,
  device_id UUID REFERENCES devices(id),
  user_id UUID REFERENCES users(id),
  detected_at TIMESTAMPTZ NOT NULL DEFAULT now(),
  resolution conflict_resolution NOT NULL DEFAULT 'pending',
  resolved_by UUID REFERENCES users(id),
  resolved_at TIMESTAMPTZ
);

CREATE INDEX idx_user_orgs_lookup ON user_organizations(user_id,organization_id);
CREATE INDEX idx_sync_sessions_device ON sync_sessions(device_id,started_at DESC);
CREATE INDEX idx_sync_conflicts_org ON sync_conflicts(organization_id,resolution,detected_at DESC);
CREATE INDEX idx_sync_versions_org ON sync_versions(organization_id,updated_at DESC);

-- RLS sera activé table par table après définition du contexte d'organisation côté API.
