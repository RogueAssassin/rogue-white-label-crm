# Configuration Guide

## Branding

Open **Rogue CRM → Settings** and configure:

- Business Name
- Business Email
- Business Phone
- Website
- Primary Colour
- Secondary Colour
- Currency Symbol

## Job Prefix

The job prefix controls automatically generated job numbers.

Examples:

- `JOB-` creates `JOB-0001`
- `CASE-` creates `CASE-0001`
- `WORK-` creates `WORK-0001`

## Job Statuses

Enter one status per line.

Example:

```text
Pending
Booked
In Progress
Awaiting Customer
Completed
Cancelled
```

## Users and Permissions

The plugin creates two roles:

- **CRM Manager**: can manage CRM records.
- **CRM Staff**: can view CRM records.

Administrators automatically receive CRM management permissions.
