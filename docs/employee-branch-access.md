# Employee branch access

Open **Employees → Add/Edit → Company Details → Additional branch access**.
Select the other branches the employee may access and save the employee.
The existing Branch field remains the employee's home branch and is always included.
Clear the additional selections to return to home-branch access.

Only administrators and users with `role_permission` may change this field. They
also need the existing employee create/edit permission to save the form. Submitted
branch IDs must belong to the administrator's company. Existing role and module
permissions still apply; selecting branches does not grant new module permissions.
The role permission `access_all_branches` continues to override branch restrictions.
The assigned-work permission continues to allow individually assigned projects and
tasks across branches independently of these additional branch assignments.

Run `php artisan migrate` before deploying the updated employee form. The migration
adds a nullable JSON column, `users.additional_branch_ids`; existing employees keep
their current branch access until explicitly assigned additional branches.

The employee create/update requests accept `additional_branch_ids` as an array.
Omitting the field preserves existing assignments on update. The HTML form submits
`branch_access_present=1` so clearing all selections saves an empty array.
