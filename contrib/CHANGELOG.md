# Changelog

## 2.0.0-rc2 25/07/2026
  - [FIX] Corrected the faction-name typo "Zaithan" → "Zhaitan". Game data verified current — all 9 professions and 5 races present (Elite Specializations are handled by the specialization system). (#1)

## 2.0.0-rc1 24/07/2026
  - [NEW] Elite Specialization catalog (bbguild#331 Phase 4) — 27 specs across all 9 professions
    - `gw2_provider` implements `specialization_provider_interface`; static `spec_catalog()` is the single source of truth
    - `gw2_installer::install_specs()` seeds specs on fresh install
    - Role mapping (Damage/Support/Control) follows established raid/WvW meta play (Firebrand, Druid, Scrapper, Mechanist, Specter, Tempest, Herald as Support; Chronomancer, Spellbreaker, Scourge, Renegade as Control; rest Damage)
    - `spec_icon` intentionally left empty — no icon assets exist yet; core already handles the empty-icon case. Tracked separately in #8.
  - [FIX] Migration dependency pointed at a since-removed bbguild core migration path (`basics\schema`, squashed into `v200b3` in an earlier core release) — this plugin could not install at all against current core
  - [FIX] `license.txt` file mode corrected to 644
  - [FIX] File mode corrected to 644 on 1428 cached PNG files
  - [FIX] Stripped ICC color profiles from 25 PNG icons (EPV compliance)
  - [CHG] Namespace/composer/repo dropped to no-separator form (`bbguildgw2`); DB-stored config keys (`bbguild_gw2_version`) preserved with the original underscore form
  - [CHG] Version tracking moved out of `phpbb_config` into `ext::BBGUILDGW2_VERSION`
  - [CHG] Soft-requires `avathar/bbguild >= 2.0.0-rc3`
  - [CHG] Provider return types use FQCN; removed unused `use` statements
  - [CHG] CI: unit tests now check out bbguild core alongside so plugin classes resolve core interfaces
  - [DOCS] README: fixed wrong GitHub org (bbGuild Core / Issue Tracker links pointed at `avandenberghe/bbguild` instead of `avatharbe/bbguildgw2`), stale PHP >= 7.4.0 requirement (actual has been 8.1.0)

## 2.0.0-a1 02/03/2026
  - [NEW] Initial release as standalone phpBB extension
  - [NEW] Extracted from bbGuild core as part of the game plugin architecture
  - [NEW] Implements `game_provider_interface` — registers GW2 with bbGuild via tagged services
  - [NEW] `gw2_installer` extends `abstract_game_install` with clean array-based table names
  - [NEW] `gw2_provider` supplies game metadata (factions, regions)
  - [NEW] Custom Damage/Support/Control roles (overrides standard holy trinity)
  - [NEW] Game images served from plugin directory including GW2 API emblem assets
  - [CHG] Installer uses `$this->table()` helper instead of direct property access
