-- ============================================================================
-- bbGuild GW2 Plugin - Database Cleanup Script
-- ============================================================================
--
-- WARNING: This script removes all Guild Wars 2 game data from the database.
--          It is intended for development/testing purposes only.
--          Back up your database before running this script!
--
-- Assumptions:
--   - phpBB table prefix is "phpbb_"
--   - bbGuild core tables exist
--
-- After running this script, disable and re-enable the extension in
-- phpBB ACP to re-run migrations and restore default data.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. Remove Guild Wars 2 game data from core tables
-- ----------------------------------------------------------------------------

DELETE FROM phpbb_bb_language WHERE game_id = 'gw2';
DELETE FROM phpbb_bb_classes WHERE game_id = 'gw2';
DELETE FROM phpbb_bb_races WHERE game_id = 'gw2';
DELETE FROM phpbb_bb_factions WHERE game_id = 'gw2';
DELETE FROM phpbb_bb_gameroles WHERE game_id = 'gw2';
DELETE FROM phpbb_bb_players WHERE game_id = 'gw2';
DELETE FROM phpbb_bb_games WHERE game_id = 'gw2';

-- ----------------------------------------------------------------------------
-- 2. phpBB extension registration
-- ----------------------------------------------------------------------------

DELETE FROM phpbb_ext WHERE ext_name = 'avathar/bbguildgw2';

-- ----------------------------------------------------------------------------
-- 3. phpBB migration tracking
-- ----------------------------------------------------------------------------

DELETE FROM phpbb_migrations WHERE migration_name LIKE '%avathar\\\\bbguildgw2%';

-- ============================================================================
-- Done. Now purge the phpBB cache and re-enable the extension from ACP.
-- ============================================================================
