### 1.0.0-alpha1 - 23/06/2014

* Fix: new named `first/last` positions (was `top/bottom` - no real meaning)
* Fix: the DB json file is now updated correctly at each Composer step (install/update/removal)
* Presets assets can now use distant URLs (with or without protocol)
* The plugin now handles ONLY packages with a `-assets` prefix in their type
* Added a `positional` capability for each presets asset
* The `\AssetsManager\Composer\Autoload\AssetsAutoloaderGenerator` is now a true singleton
* Added some `XXX-class` configuration entries to choose which classes to use - each class must implement a plugin's interface
* Internal exceptions thrown during Composer installation/update/removal of packages are now managed by the CLI compliant class `\AssetsManager\Error`
* Rewriting the whole installer as a Composer plugin following the `composer-plugin-api`

### 1.0.\* => 0.\*

Old tags were named like `1.*` during development and are all renamed as `0.*` as they may not be considered as stable

    BEFORE 23/06/2014
    v1.0.0 => v0.2.0
    v1.0.1 => v0.3.0
    v1.0.2 => v0.4.0
    v1.0.3 => v0.5.0
    v1.0.4 => v0.6.0
    v1.0.5 => v0.7.0
