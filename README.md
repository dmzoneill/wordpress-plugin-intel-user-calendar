# Intel User Calendar WordPress Plugin

<p align="center">
  <img src=".github/logo.png" alt="wordpress-plugin-intel-user-calendar logo" width="128" />
</p>

A WordPress plugin that adds an **Intel User Calendar** widget showing
holidays and pay dates for Intel staff in Ireland.

## What it does

- Reads public holidays from a Microsoft SQL Server table
  (`tblPublicHoliday`) and marks personal, public and American
  holidays on a calendar.
- Links to Intel's internal pay and HR pages.
- Adds an admin settings page and a configurable widget.

## Tech

- PHP and WordPress
- Microsoft SQL Server, through `mssql.class.php`

## Installation

Copy the folder into `wp-content/plugins`, activate **Intel User
Calendar**, set the database details on its settings page and add the
widget to a sidebar.

## License

Licensed under the Apache License 2.0. See [LICENSE](LICENSE).
