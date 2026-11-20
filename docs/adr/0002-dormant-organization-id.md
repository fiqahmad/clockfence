# Dormant `organization_id` on every table

v1 is one organisation per install, but every table carries a nullable `organization_id` so a hosted multi-tenant version can come later without rewriting every table. In v1 the column has no foreign key, no `organizations` table and no query scoping: it is always null, and filtering on it would either do nothing or hide every row. Scoping arrives with multi-tenancy, not before. Do not "fix" the missing foreign key or add `where organization_id` clauses in the meantime.
