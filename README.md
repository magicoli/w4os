# W4OS

WordPress interface for OpenSimulator

## Description

The first ready to use WordPress interface for OpenSimulator. Provides user
registration and basic grid info. See enabled features below and ROADMAP file
for upcoming functionalties.

## Features

* **Grid info**: `[gridinfo]` shortcode and admin dashboard widgets
* **Grid status**: `[gridstatus]` shortcode and admin dashboard widgets
* **Avatar creation**:
  - `[w4os_profile]` shortcode can be inserted in any page
  - Avatar tab in account dashboard on WooCommerce websites
  - Choose avatar look from default models
* Avatar and website passwords are synchronized
* **Reserved names**: avatar whose first name or last name is "Default",
  "Test", "Admin" or the pattern used for appearance models are disallowed for
  public (such avatars must be created by admins from Robust console)
* **OpenSimulator settings page**:
  - grid name, login uri and database connection settings
  - naming scheme of default models
  - exclude models from grid stats
