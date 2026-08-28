# Hostinger Auto Deploy Result

- status: FAILED
- final stage: committing deployed revision state
- from: e329b7df1e50e2a541478e579da48bee2f29a86f
- to: 46d307482f6e529d5f13520634f253e14049ba84
- document root: /home/u218517330/domains/impactads.io/public_html
- rollback directory: /home/u218517330/deploy-backups/impactads-auto-20260828T082707Z

## Output
```text
Deploy impactads.io
CURRENT_SHA=46d307482f6e529d5f13520634f253e14049ba84
ROOT=/home/u218517330/domains/impactads.io/public_html
DEPLOYED_SHA=e329b7df1e50e2a541478e579da48bee2f29a86f
Files to upload/update: 2
Files to delete: 0
New files (rollback tracking): 0
Upload/update sample:
wp-content/plugins/impact-accs-chrome/includes/i18n/ru-about.php
wp-content/plugins/impact-accs-chrome/includes/i18n/ru-team-supply.php
No syntax errors detected in wp-content/plugins/impact-accs-chrome/includes/i18n/ru-about.php
No syntax errors detected in wp-content/plugins/impact-accs-chrome/includes/i18n/ru-team-supply.php
Rollback file archive prepared.
Changed files uploaded.
Remote PHP syntax validation passed.
WP-CLI core check passed.
HTTP / => 403
ERROR: Homepage did not return HTTP 200.
Deployment failed during stage: committing deployed revision state
Starting rollback from /home/u218517330/deploy-backups/impactads-auto-20260828T082707Z
Rollback attempt completed; deployed SHA marker was not advanced.
```
