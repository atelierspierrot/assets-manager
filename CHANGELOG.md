# CHANGELOG

This is the changelog of the **atelierspierrot/assets-manager** package.

You may find the original remote repository to <https://github.com/atelierspierrot/assets-manager.git>.
The `#xxx` marks of this changelog may reference a bug ticket you can find at 
<http://github.com/atelierspierrot/assets-manager/issues/XXX>. To see a full commit diff, 
go to <https://github.com/atelierspierrot/assets-manager/commit/COMMIT_HASH>.

* (upcoming release)

    * Fix: new named `first/last` positions (was `top/bottom` - no real meaning)
    * Fix: the DB json file is now updated correctly at each Composer step (install/update/removal)
    * Presets assets can now use distant URLs (with or without protocol)
    * The plugin now handles ONLY packages with a `-assets` prefix in their type
    * Added a `positional` capability for each presets asset
    * The `\AssetsManager\Composer\Autoload\AssetsAutoloaderGenerator` is now a true singleton
    * Added some `XXX-class` configuration entries to choose which classes to use - each class must implement a plugin's interface
    * Internal exceptions thrown during Composer installation/update/removal of packages are now managed by the CLI compliant class `\AssetsManager\Error`
    * Rewriting the whole installer as a Composer plugin following the `composer-plugin-api`

    * 899a40e - review of package's info (piwi)
    * 5cee84b - the famous 'occured' error (piwi)
    * 36f7829 - review of the scripts license header (piwi)
    * 4e415cb - update of the gitignore (piwi)
    * 99dd047 - new documentation generation (piwi)
    * 97513d6 - fixes #4: using the NO_CONFLICT flag now retain the last definition of a preset (was the first one) (Piero Wbmstr)
    * 21041a2 - updating documentation (via Sami) (Piero Wbmstr)
    * 1f17732 - working on the doc (Piero Wbmstr)
    * 3e26f61 - correction in the JSON DB generation (Piero Wbmstr)
    * 9a696cb - updating documentation (via Sami) (Piero Wbmstr)
    * 61e83ea - sources review + README re-writing (Piero Wbmstr)
    * 1d8e1d0 - new 'last' and 'first' positional assets info (Piero Wbmstr)
    * 697a35b - updating documentation (via Sami) (Piero Wbmstr)
    * 2998b03 - URL of assets can now have no-protocol (Piero Wbmstr)
    * 92f16d3 - updating documentation (via Sami) (Piero Wbmstr)
    * 9ad8161 - cleanup & sources review (Piero Wbmstr)
    * 8c9e714 - new piwi github url + 2014 copyleft :( (Piero Wbmstr)
    * ee88cfa - Renaming "PieroWbmstr" in lowercase (Piero Wbmstr)
    * c2354a4 - Updating documentation (Piero Wbmstr)
    * 9208546 - Adding methods to test if a package or a preset exists in the project (Piero Wbmstr)
    * 8bfa082 - Avoid redundancy (Piero Wbmstr)
    * b20d079 - Updating documentation (Piero Wbmstr)
    * e43ab8b - Correcting a typo (Piero Wbmstr)
    * aaf032f - Updating documentation for version 0.0.5 (Piero Wbmstr)
    * a601daf - New README infor with the AssetsManager itself (Piero Wbmstr)
    * f6502cb - Merging wip (Piero Wbmstr)
    * 168c519 - Corrections in the README (Piero Wbmstr)
    * 82270d7 - No more assets handling for "composer-installer" package type (Piero Wbmstr)
    * f68607d - PHP documentation (Piero Wbmstr)

* v1.0.0-alpha1 (2014-06-23 - 151e1ca)

    * e28b379 - new CHANGELOG file (Piero Wbmstr)
    * ba4d735 - working on the doc (Piero Wbmstr)
    * 420e7f8 - correction in the JSON DB generation (Piero Wbmstr)

## BEFORE 23/06/2014

Old tags were named like `1.*` during development and are all renamed as `0.*` 
as they may not be considered as stable:

    v1.0.0 => v0.2.0
    v1.0.1 => v0.3.0
    v1.0.2 => v0.4.0
    v1.0.3 => v0.5.0
    v1.0.4 => v0.6.0
    v1.0.5 => v0.7.0

* v0.7.0 (2014-06-23 - 3a3f645)

    * 871fc98 - sources review + README re-writing (Piero Wbmstr)
    * 9cbe267 - new 'last' and 'first' positional assets info (Piero Wbmstr)

* v0.6.0 (2014-06-23 - d7b7b26)

    * e3fcd0a - URL of assets can now have no-protocol (Piero Wbmstr)

* v0.5.0 (2014-06-22 - e465f4c)

    * 68f0d24 - cleanup & sources review (Piero Wbmstr)
    * 364f764 - new piwi github url + 2014 copyleft :( (Piero Wbmstr)
    * 5fb16b5 - Renaming "PieroWbmstr" in lowercase (Piero Wbmstr)
    * 92fc99f - Adding methods to test if a package or a preset exists in the project (Piero Wbmstr)
    * 47932ef - Avoid redundancy (Piero Wbmstr)

* v0.4.0 (2014-06-10 - 5f27b65)

    * 8c9e714 - new piwi github url + 2014 copyleft :( (Piero Wbmstr)
    * ee88cfa - Renaming "PieroWbmstr" in lowercase (Piero Wbmstr)
    * c2354a4 - Updating documentation (Piero Wbmstr)
    * 9208546 - Adding methods to test if a package or a preset exists in the project (Piero Wbmstr)
    * 8bfa082 - Avoid redundancy (Piero Wbmstr)
    * b20d079 - Updating documentation (Piero Wbmstr)
    * e43ab8b - Correcting a typo (Piero Wbmstr)
    * aaf032f - Updating documentation for version 0.0.5 (Piero Wbmstr)
    * a601daf - New README infor with the AssetsManager itself (Piero Wbmstr)
    * f6502cb - Merging wip (Piero Wbmstr)
    * 168c519 - Corrections in the README (Piero Wbmstr)
    * 82270d7 - No more assets handling for "composer-installer" package type (Piero Wbmstr)
    * f68607d - PHP documentation (Piero Wbmstr)

* v0.3.0 (2013-10-13 - 8fc7859)

    * 92fc99f - Adding methods to test if a package or a preset exists in the project (Piero Wbmstr)
    * 47932ef - Avoid redundancy (Piero Wbmstr)

* v0.2.0 (2013-10-13 - 9c6990a)

    * 360f695 - Classic gitignore (Piero Wbmstr)

* v0.1.0 (2013-10-13 - bb8d828)

    * 9edc014 - Using the new semantic versioning for dependencies (Piero Wbmstr)
    * a7f2f28 - Correcting a typo (Piero Wbmstr)
    * 76b0b0f - deletion of the test (Piero Wbmstr)
    * 7744166 - just for tests (Piero Wbmstr)
    * f01ae05 - New README infor with the AssetsManager itself (Piero Wbmstr)
    * beb4ee2 - Correction in the "setAssetsDb" (Piero Wbmstr)
    * 9d77508 - Upgrade to version 0.0.4 (Piero Wbmstr)
    * a42ff0a - Merging wip (Piero Wbmstr)
    * ef03bed - Corrections in the README (Piero Wbmstr)
    * 476f753 - Simple correction (Piero Wbmstr)
    * 27133fc - No more assets handling for "composer-installer" package type (Piero Wbmstr)
    * 31983b4 - Missing "patterns" requirements (Piero Wbmstr)
    * 640fdf6 - Very first fully working version (Piero Wbmstr)
    * 38bfeef - Version 0.0.0 (Piero Wbmstr)
    * da56cc5 - Initial commit (Piero Wbmstr)

