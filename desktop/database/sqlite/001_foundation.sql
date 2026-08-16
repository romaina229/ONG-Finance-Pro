-- Finance Pro — SQLite Offline First foundation
PRAGMA foreign_keys = ON;

CREATE TABLE app_settings (
  key TEXT PRIMARY KEY,
  value TEXT
);

CREATE TABLE organizations_local (
  id TEXT PRIMARY KEY,
  name TEXT NOT NULL,
  acronym TEXT,
  default_currency TEXT NOT NULL DEFAULT 'XOF',
  logo_path TEXT,
  cached_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE users_local (
  id TEXT PRIMARY KEY,
  full_name TEXT NOT NULL,
  email TEXT,
  role_code TEXT,
  cached_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE projects_local (
  id TEXT PRIMARY KEY,
  server_id TEXT UNIQUE,
  organization_id TEXT NOT NULL,
  code TEXT NOT NULL,
  name TEXT NOT NULL,
  total_budget_minor INTEGER NOT NULL DEFAULT 0,
  currency TEXT NOT NULL DEFAULT 'XOF',
  status TEXT NOT NULL DEFAULT 'draft',
  sync_status TEXT NOT NULL DEFAULT 'synced' CHECK(sync_status IN ('synced','pending','syncing','conflict','error')),
  is_dirty INTEGER NOT NULL DEFAULT 0 CHECK(is_dirty IN (0,1)),
  is_deleted INTEGER NOT NULL DEFAULT 0 CHECK(is_deleted IN (0,1)),
  local_version INTEGER NOT NULL DEFAULT 1,
  server_version INTEGER,
  local_updated_at TEXT NOT NULL DEFAULT (datetime('now')),
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  UNIQUE(organization_id,code)
);

-- Les montants locaux sont stockés en unités mineures entières.
-- Pour XOF : 1 unité = 1 FCFA. Cette convention évite les erreurs REAL/float.

CREATE TABLE expenses_local (
  id TEXT PRIMARY KEY,
  server_id TEXT UNIQUE,
  organization_id TEXT NOT NULL,
  project_id TEXT NOT NULL,
  amount_minor INTEGER NOT NULL CHECK(amount_minor > 0),
  currency TEXT NOT NULL DEFAULT 'XOF',
  amount_in_org_currency_minor INTEGER,
  supplier_name TEXT,
  payment_method_code TEXT NOT NULL,
  payment_reference TEXT,
  expense_date TEXT NOT NULL,
  description TEXT,
  status TEXT NOT NULL DEFAULT 'draft',
  created_by TEXT NOT NULL,
  approved_by TEXT,
  approved_at TEXT,
  rejection_reason TEXT,
  sync_status TEXT NOT NULL DEFAULT 'pending' CHECK(sync_status IN ('synced','pending','syncing','conflict','error')),
  is_dirty INTEGER NOT NULL DEFAULT 1 CHECK(is_dirty IN (0,1)),
  is_deleted INTEGER NOT NULL DEFAULT 0 CHECK(is_deleted IN (0,1)),
  local_version INTEGER NOT NULL DEFAULT 1,
  server_version INTEGER,
  local_updated_at TEXT NOT NULL DEFAULT (datetime('now')),
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE INDEX idx_expenses_local_sync ON expenses_local(sync_status,local_updated_at);
CREATE INDEX idx_expenses_local_project ON expenses_local(project_id,expense_date);

CREATE TABLE revenues_local (
  id TEXT PRIMARY KEY,
  server_id TEXT UNIQUE,
  organization_id TEXT NOT NULL,
  project_id TEXT,
  donor_id TEXT,
  amount_minor INTEGER NOT NULL CHECK(amount_minor > 0),
  currency TEXT NOT NULL DEFAULT 'XOF',
  amount_in_org_currency_minor INTEGER,
  revenue_type TEXT NOT NULL,
  received_date TEXT NOT NULL,
  payment_method_code TEXT NOT NULL,
  payment_reference TEXT,
  description TEXT,
  status TEXT NOT NULL DEFAULT 'draft',
  created_by TEXT NOT NULL,
  sync_status TEXT NOT NULL DEFAULT 'pending' CHECK(sync_status IN ('synced','pending','syncing','conflict','error')),
  is_dirty INTEGER NOT NULL DEFAULT 1 CHECK(is_dirty IN (0,1)),
  is_deleted INTEGER NOT NULL DEFAULT 0 CHECK(is_deleted IN (0,1)),
  local_version INTEGER NOT NULL DEFAULT 1,
  server_version INTEGER,
  local_updated_at TEXT NOT NULL DEFAULT (datetime('now')),
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE INDEX idx_revenues_local_sync ON revenues_local(sync_status,local_updated_at);

CREATE TABLE documents_local (
  id TEXT PRIMARY KEY,
  server_id TEXT UNIQUE,
  owner_type TEXT NOT NULL,
  owner_id TEXT NOT NULL,
  file_name TEXT NOT NULL,
  local_file_path TEXT NOT NULL,
  remote_file_path TEXT,
  file_type TEXT,
  file_size_bytes INTEGER,
  sha256_hash TEXT NOT NULL,
  uploaded_by TEXT NOT NULL,
  sync_status TEXT NOT NULL DEFAULT 'pending' CHECK(sync_status IN ('synced','pending','syncing','conflict','error')),
  is_dirty INTEGER NOT NULL DEFAULT 1 CHECK(is_dirty IN (0,1)),
  is_deleted INTEGER NOT NULL DEFAULT 0 CHECK(is_deleted IN (0,1)),
  local_updated_at TEXT NOT NULL DEFAULT (datetime('now')),
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE INDEX idx_documents_local_owner ON documents_local(owner_type,owner_id);
CREATE INDEX idx_documents_local_sync ON documents_local(sync_status,local_updated_at);

CREATE TABLE sync_queue (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  entity_type TEXT NOT NULL,
  entity_id TEXT NOT NULL,
  operation TEXT NOT NULL CHECK(operation IN ('insert','update','delete')),
  payload TEXT,
  priority INTEGER NOT NULL DEFAULT 5 CHECK(priority BETWEEN 1 AND 9),
  status TEXT NOT NULL DEFAULT 'pending' CHECK(status IN ('pending','syncing','synced','failed')),
  retry_count INTEGER NOT NULL DEFAULT 0,
  last_error TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  synced_at TEXT
);
CREATE INDEX idx_sync_queue_ready ON sync_queue(status,priority,created_at);

CREATE TABLE sync_conflicts_local (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  entity_type TEXT NOT NULL,
  entity_id TEXT NOT NULL,
  local_version TEXT NOT NULL,
  server_version TEXT NOT NULL,
  detected_at TEXT NOT NULL DEFAULT (datetime('now')),
  resolution TEXT NOT NULL DEFAULT 'pending' CHECK(resolution IN ('server_wins','client_wins','manual','pending')),
  resolved_at TEXT
);

CREATE TABLE audit_logs_local (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  entity_type TEXT NOT NULL,
  entity_id TEXT NOT NULL,
  action TEXT NOT NULL,
  old_values TEXT,
  new_values TEXT,
  user_id TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  sync_status TEXT NOT NULL DEFAULT 'pending' CHECK(sync_status IN ('pending','synced','error'))
);
CREATE INDEX idx_audit_local_sync ON audit_logs_local(sync_status,created_at);
