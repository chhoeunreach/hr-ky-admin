<?php

return [
    'navigation' => 'Maintenance',
    'title' => 'System diagnostics',
    'checks_title' => 'System checks',
    'recent_logs' => 'Recent application errors and warnings',
    'log_scope' => 'Up to :limit entries from the last 7 days of local Laravel log files. Entries are categorized so sensitive log details stay on the server.',
    'all_levels' => 'All levels',
    'level' => 'Level',
    'apply' => 'Apply',
    'time' => 'Server time',
    'issue' => 'Issue',
    'next_action' => 'How to fix',
    'empty' => 'No matching errors or warnings in the logs checked.',
    'unreadable' => 'Some log files could not be read. Check filesystem permissions for the application account.',
    'status' => [
        'passed' => 'Passed',
        'warning' => 'Warning',
        'error' => 'Error',
        'unavailable' => 'Could not check',
        'critical' => 'Critical',
        'alert' => 'Alert',
        'emergency' => 'Emergency',
    ],
    'checks' => [
        'database' => [
            'title' => 'Database connection',
            'passed' => 'The application can read from its configured database.',
            'fix' => 'Check that the database service is running and that the server has the correct connection settings.',
        ],
        'payday_queue' => [
            'title' => 'Payday Queue schema',
            'passed' => 'The session, ticket, and invitation tables are available.',
            'fix' => 'Check pending payroll queue migrations with php artisan migrate:status and apply missing migrations through your deployment process.',
        ],
        'salary_directory' => [
            'title' => 'Employee Salary directory',
            'passed' => 'The directory eligibility rules include all active, verified employee records.',
            'fix' => 'Review salary-list eligibility for employee accounts with an admin role or a missing role. Base payroll eligibility on employee status and retain payroll permissions and branch access.',
            'finding' => '{1} :count active, verified employee is excluded by the directory’s role rules.|[2,*] :count active, verified employees are excluded by the directory’s role rules.',
            'open' => 'Open Employee Salary',
        ],
        'debug_mode' => [
            'title' => 'Debug mode',
            'passed' => 'Detailed error pages are disabled.',
            'fix' => 'If this is a production server, set APP_DEBUG=false and rebuild the configuration cache during deployment.',
        ],
        'log_storage' => [
            'title' => 'Log storage',
            'passed' => 'The Laravel log directory is writable.',
            'fix' => 'Check free disk space and give the application account write access to storage/logs.',
        ],
    ],
    'categories' => [
        'schema' => ['title' => 'Database table or column missing', 'fix' => 'Check migration status and verify that the application and database schema are on the same release.'],
        'database_connection' => ['title' => 'Database connection failed', 'fix' => 'Check database availability, connectivity, and application database permissions.'],
        'database_query' => ['title' => 'Database query failed', 'fix' => 'Find the entry by timestamp in the server log and review the query and database error code.'],
        'firebase' => ['title' => 'Push notification issue', 'fix' => 'Check Firebase configuration and recipient device registration. Use the server log to identify the failed operation.'],
        'filesystem' => ['title' => 'Filesystem issue', 'fix' => 'Check disk space and filesystem permissions for the application account.'],
        'application' => ['title' => 'Application event', 'fix' => 'Find the matching timestamp in the server log and trace the failing request or background job.'],
    ],
];
