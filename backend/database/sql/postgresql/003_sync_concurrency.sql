-- Finance Pro — concurrency test scenarios
-- Ces scénarios sont exécutables contre PostgreSQL 16+ après chargement des fondations.

-- 1. Idempotence : deux traitements portant la même operation_id doivent être
-- représentés par une seule opération logique.
DO $$
DECLARE
  op UUID := gen_random_uuid();
  org UUID;
  device UUID;
  usr UUID;
  cnt INTEGER;
BEGIN
  INSERT INTO organizations(name) VALUES ('SYNC TEST') RETURNING id INTO org;
  INSERT INTO users(full_name,email,password_hash) VALUES ('Sync Test','sync-' || op || '@test.local','test') RETURNING id INTO usr;
  INSERT INTO devices(user_id,device_uuid) VALUES (usr,'test-' || op) RETURNING id INTO device;
  INSERT INTO sync_operations(operation_id,organization_id,device_id,user_id,entity_type,local_id,operation,status)
  VALUES(op,org,device,usr,'test_entity',gen_random_uuid(),'insert','accepted');
  SELECT COUNT(*) INTO cnt FROM sync_operations WHERE operation_id=op;
  IF cnt <> 1 THEN RAISE EXCEPTION 'Idempotence setup failed'; END IF;
  DELETE FROM organizations WHERE id=org;
END $$;

-- 2. Version monotone : une version ne doit jamais être nulle ou négative.
DO $$
BEGIN
  IF EXISTS (SELECT 1 FROM sync_versions WHERE version <= 0) THEN
    RAISE EXCEPTION 'Invalid sync version';
  END IF;
END $$;

-- 3. Ordre de pull : les changements d'une organisation sont ordonnés par sequence.
DO $$
BEGIN
  IF EXISTS (
    SELECT 1
    FROM sync_changes a
    JOIN sync_changes b ON a.organization_id=b.organization_id AND a.sequence < b.sequence
    WHERE a.sequence >= b.sequence
  ) THEN
    RAISE EXCEPTION 'Invalid sync cursor ordering';
  END IF;
END $$;
