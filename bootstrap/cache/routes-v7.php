<?php

/*
|--------------------------------------------------------------------------
| Load The Cached Routes
|--------------------------------------------------------------------------
|
| Here we will decode and unserialize the RouteCollection instance that
| holds all of the route information for an application. This allows
| us to instantaneously load the entire route map into the router.
|
*/

app('router')->setCompiledRoutes(
    array (
  'compiled' => 
  array (
    0 => false,
    1 => 
    array (
      '/_debugbar/open' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.openhandler',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/stylesheets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.css',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/assets/javascript' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.assets.js',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_debugbar/queries/explain' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.queries.explain',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/token' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.token',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/authorize' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.authorizations.authorize',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'passport.authorizations.approve',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        2 => 
        array (
          0 => 
          array (
            '_route' => 'passport.authorizations.deny',
          ),
          1 => NULL,
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/token/refresh' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.token.refresh',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/tokens' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.tokens.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/clients' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.clients.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'passport.clients.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/scopes' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.scopes.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/oauth/personal-access-tokens' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.personal.tokens.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'passport.personal.tokens.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/sanctum/csrf-cookie' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'sanctum.csrf-cookie',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/health-check' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.healthCheck',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/execute-solution' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.executeSolution',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/_ignition/update-config' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'ignition.updateConfig',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::VpTIgDBr1zgU9hJ9',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kp1O8ECyijotxvZd',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/users/profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2lf0YNnon4efAbEt',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/users/change-password' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::v1pi9tfdapvWvp5t',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/users/update-profile' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::hVxXSv8KzuiM7nVg',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/users/company/team-sheet' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::rKvvD3YVbP4zx7Dt',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/users/location' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::CqDkRyUCW3MYBkOS',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/company-rules' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::czgQNomEpdTgxdoj',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::hgYRpl40zWatpna3',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/notices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ObixOmOzaA6VSJwR',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::GhuHg0laBlWpUUhZ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employees/check-in' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::j2IkRAEo2epQWzRX',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employees/check-out' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::9jBZsEKhYOzLXmge',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employees/attendance-detail' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::5P6f8FXrmxKEPgaC',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employees/attendance' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::zqYFE3lBoCk5zjOl',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/leave-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::8d7wDaWZjmcB9I4a',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/leave-requests/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::tK2nEYeUoO9HXMtS',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/leave-requests/employee-leave-requests' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xIcGBrT0gtcIdD7V',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/leave-requests/employee-leave-calendar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QCL30K9ZbDFR8RcN',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/leave-requests/employee-leave-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::jpqJ2vuGJ9rBFxCZ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/office-calendar' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ceM5adQ8TJIASr7r',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/time-leave-requests/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DiWUmEvK4U2Swbtg',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/team-meetings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UPMC5p804bmmjeq7',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/holidays' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::0XRqIKuvU8iUMExL',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/project-management-dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::VWQ6OhDtKDJjmvoM',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/assigned-projects-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DF3orQoTPLzpOgJq',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/assigned-task-list' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::96kB0OxBkAJV6KLW',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/assigned-task-comments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::UfXH1RXMliCA5n6S',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/assigned-task/comments/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Jka3kSJ2KhwMEi9z',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/support/query-store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qyet93tatTDRshO2',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/support/department-lists' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::t6fKmxgq1c58EkTk',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/support/get-user-query-lists' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::D7awe7BghQCteMc8',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/tada-lists' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::7aqj4XgLgdVLkOOp',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/tada/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qJx0ngg8gvCMuoWZ',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/tada/update' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::Spezupr9s1mLgSq9',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/advance-salaries-lists' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3Kv5DPLwDmfKmnBj',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/advance-salaries/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::DlqZmtM3nxVpwzkS',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/nfc/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::yuXfcaeY01PLDs3J',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/push' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::knPmBFZEK0avF99T',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/employee/payslip' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::STUGjPaKze7mQS2z',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/awards' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::4XKv7N7BtIxHbQuT',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/events' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::oWj1FoE0gQ1ptRxD',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/training' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QT0Ac3fhRXwWhSqi',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/resignation/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::QooHSPZKUWANDEqn',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/resignation' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::jIp1c1MId9f5ar5b',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/warning' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::2Uqw3mUfHoG41j3S',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/complaint/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::cD5c8yFfX9jc7qD9',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/complaint' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::MHlZMeV7ncVBiSJs',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/department-employees' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qwrDf95kmUoxNhvQ',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/assets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3k39Vl0FlBIdWHoW',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/api/payroll/ssf-history' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::5SPuJk2BalcaSI0i',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/reset' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.request',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'password.update',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/email' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.email',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/password/confirm' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.confirm',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3EQn8V0M8aZyZ9ut',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::hf8jEnhf9udcSrOV',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/add-user' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::zXNwDcpxpAVbb2OH',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/iclock/cdata' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::IaKwPJJh3C9bcgyu',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
            'POST' => 2,
            'PUT' => 3,
            'PATCH' => 4,
            'DELETE' => 5,
            'OPTIONS' => 6,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/privacy' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'privacy-policy',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/login' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.login',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.login.process',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/logout' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logout',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/dashboard' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.dashboard',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/showQR' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.showQR',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/users/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/company' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/company/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/branch' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/branch/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/app-settings/index' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.app-settings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/general-settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/general-settings/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/feature/index' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.feature.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/roles' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/roles/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee/location-logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee.log',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employees' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employees/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/departments' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/departments/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/posts' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/posts/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/office-times' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/office-times/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/routers' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/routers/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/holidays/import-csv' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.import-csv.show',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.import-csv.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/holidays' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/holidays/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leaves' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leaves/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/static-page-contents' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/static-page-contents/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/notifications/get-nav-notification' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.nav-notifications',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/notifications' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/notifications/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/attendances' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/attendances/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-request' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-request/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-leave-request.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-request/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-request/add' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.add',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.save',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/time-leave-request' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.time-leave-request.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/time-leave-request/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.time-leave-request.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/time-leave-request/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.time-leave-request.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee/logout-requests' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logout-requests.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/notices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/notices/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/team-meetings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/team-meetings/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/ajax/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.ajax-store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/clients/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/projects' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/projects/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/projects/attachment/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.project-attachment.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/tasks/attachment/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-attachment.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/tasks' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/tasks/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/task-checklists/save' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-checklists.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/task-comment/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-comment.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/supports/get-all-query' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.supports.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/tadas' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/tadas/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/tadas/attachment/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.attachment.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-types-export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-type-export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-requests-export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request-export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-detail-export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-lists-export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/attendance-detail-export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance-lists-export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/asset-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/asset-types/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/assets' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/assets/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-components' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-components/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/payment-methods' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-methods.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-methods.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/payment-methods/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-methods.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/payment-currency' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-currency.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-currency.save',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-tds' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-tds/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-groups' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/salary-groups/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-salaries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-salaries/payroll-create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.payroll-create',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-salaries/payroll' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salary.payroll',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-salaries/salary-history/store' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.updated-salary-store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/advance-salaries/setting' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.setting',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/advance-salaries' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/advance-salaries/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/employee-salaries/tax-report' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payroll.tax-report.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/overtime' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/overtime/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/under-time' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/under-time/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/qr' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/qr/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/nfc' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.nfc.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/award-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/award-types/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/awards' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/awards/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/language/change' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.language.change',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/bonus' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/bonus/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fiscal_year' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/fiscal_year/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/ssf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ssf.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ssf.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/ssf/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ssf.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/attendance/logs' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance.log',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/calculate_tax' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.get-tax',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/change-theme' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.change-theme',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-approval/get-employees-by-role' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.fetchByRole',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-approval' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/leave-approval/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/attendance/export' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance.export',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/event' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/event/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/training-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/trainers' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/trainers/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/training' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/training/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/termination-types' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination-types.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination-types.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/termination' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/termination/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/resignation' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/resignation/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/warning' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/warning/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/complaint' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/complaint/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/task/update-members' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.update-member-data',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/promotion' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/promotion/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/transfer' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/transfer/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/theme-color-setting' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.theme-color-setting.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.theme-color-setting.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/theme-color-setting/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.theme-color-setting.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/asset-return' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-return.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pf' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pf.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pf.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/pf/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pf.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/biometric-devices' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.biometric-devices.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.biometric-devices.store',
          ),
          1 => NULL,
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/biometric-devices/create' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.biometric-devices.create',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/log' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::e42fIBSRkEXuD5K7',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      '/admin/attendance-settings' => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance-settings.index',
          ),
          1 => NULL,
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance-settings.update',
          ),
          1 => NULL,
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
    ),
    2 => 
    array (
      0 => '{^(?|/_debugbar/c(?|lockwork/([^/]++)(*:39)|ache/([^/]++)(?:/([^/]++))?(*:73))|/oauth/(?|tokens/([^/]++)(*:106)|clients/([^/]++)(?|(*:133))|personal\\-access\\-tokens/([^/]++)(*:175))|/a(?|pi/(?|users/profile\\-detail/([^/]++)(*:225)|static\\-page\\-content/([^/]++)(?|(*:266)|/([^/]++)(*:283))|leave\\-requests/cancel/([^/]++)(*:323)|t(?|ime\\-leave\\-requests/cancel/([^/]++)(*:371)|eam\\-meetings/([^/]++)(*:401)|raining/([^/]++)(*:425))|ass(?|igned\\-(?|projects\\-detail/([^/]++)(*:475)|task(?|\\-(?|detail/(?|([^/]++)(*:513)|change\\-status/([^/]++)(*:544))|checklist/toggle\\-status/([^/]++)(*:586))|/(?|comment/delete/([^/]++)(*:622)|reply/delete/([^/]++)(*:651))))|et\\-return/([^/]++)(*:681))|e(?|mployee/(?|tada(?|\\-details/([^/]++)(*:730)|/delete\\-attachment/([^/]++)(*:766))|advance\\-salaries\\-detail/(?|([^/]++)(*:812)|update(*:826))|payslip/([^/]++)(*:851))|vent/([^/]++)(*:873))|warning/store/([^/]++)(*:904)|complaint/response/store/([^/]++)(*:945))|dmin/(?|u(?|sers/(?|([^/]++)(?|(*:985)|/edit(*:998)|(*:1006))|toggle\\-status/([^/]++)(*:1039)|delete/([^/]++)(*:1063)|change\\-password/([^/]++)(*:1097))|nder\\-time/(?|([^/]++)(?|(*:1132)|/edit(*:1146)|(*:1155))|delete/([^/]++)(*:1180)|change\\-status/([^/]++)(*:1212)))|c(?|omp(?|any/([^/]++)(?|(*:1248)|/edit(*:1262)|(*:1271))|laint/(?|([^/]++)(?|(*:1301)|/edit(*:1315)|(*:1324))|delete/([^/]++)(*:1349)))|lients/(?|([^/]++)(?|(*:1381)|/edit(*:1395)|(*:1404))|delete/([^/]++)(*:1429)|toggle\\-status/([^/]++)(*:1461)))|b(?|ranch/(?|([^/]++)(?|(*:1496)|/edit(*:1510)|(*:1519))|toggle\\-status/([^/]++)(*:1552)|delete/([^/]++)(*:1576))|onus/(?|([^/]++)(?|/edit(*:1610)|(*:1619))|delete/([^/]++)(*:1644)|change\\-status/([^/]++)(*:1676)|get\\-user\\-department\\-data(*:1712))|iometric\\-devices/(?|([^/]++)(*:1751)|delete/([^/]++)(*:1775)))|a(?|pp\\-settings/toggle\\-status/([^/]++)(*:1826)|tt(?|endance(?|s/([^/]++)(?|(*:1863)|/edit(*:1877)|(*:1886))|\\-settings/toggle\\-status/([^/]++)(*:1930))|achment/delete/([^/]++)(*:1963))|sset(?|\\-(?|types/(?|([^/]++)(?|(*:2005)|/edit(*:2019)|(*:2028))|delete/([^/]++)(*:2053)|toggle\\-status/([^/]++)(*:2085))|assignment(?|/([^/]++)(*:2117)|s/([^/]++)(*:2136))|return/([^/]++)(*:2161))|s/(?|([^/]++)(?|(*:2187)|/edit(*:2201)|(*:2210))|delete/([^/]++)(*:2235)|toggle\\-status/([^/]++)(*:2267))|/toggle\\-repair\\-status/([^/]++)(*:2309))|dvance\\-salaries/(?|setting/([^/]++)(*:2355)|([^/]++)(?|(*:2375))|delete/([^/]++)(*:2400))|ward(?|\\-types/(?|([^/]++)(?|(*:2439)|/edit(*:2453)|(*:2462))|delete/([^/]++)(*:2487)|toggle\\-status/([^/]++)(*:2519))|s/(?|([^/]++)(?|(*:2545)|/edit(*:2559)|(*:2568))|delete/([^/]++)(*:2593))))|ge(?|neral\\-settings/(?|([^/]++)(?|(*:2640)|/edit(*:2654)|(*:2663))|delete/([^/]++)(*:2688))|t\\-(?|branch\\-(?|project\\-data/([^/]++)(*:2737)|employee\\-data/([^/]++)(*:2769)|a(?|sset\\-data/([^/]++)(*:2801)|ward\\-data/([^/]++)(*:2829))|leave\\-data/([^/]++)(*:2859)|t(?|ermination\\-data/([^/]++)(*:2897)|ask\\-data/([^/]++)(*:2924)|raining\\-data/([^/]++)(*:2955)))|project\\-member\\-data/([^/]++)(*:2996)))|f(?|eature/toggle\\-status/([^/]++)(*:3041)|iscal_year/(?|([^/]++)(?|/edit(*:3080)|(*:3089))|delete/([^/]++)(*:3114)|change\\-current\\-status/([^/]++)(*:3155)))|r(?|o(?|les/(?|([^/]++)(?|(*:3192)|/edit(*:3206)|(*:3215))|toggle\\-status/([^/]++)(*:3248)|delete/([^/]++)(*:3272)|permissions/([^/]++)(*:3301)|assign\\-permissions/([^/]++)(*:3338))|uters/(?|([^/]++)(?|(*:3368)|/edit(*:3382)|(*:3391))|toggle\\-status/([^/]++)(*:3424)|delete/([^/]++)(*:3448)))|esignation/(?|([^/]++)(?|(*:3484)|/edit(*:3498)|(*:3507))|delete/([^/]++)(*:3532)|status\\-update/([^/]++)(*:3564)))|e(?|mployee(?|s/(?|([^/]++)(?|(*:3605)|/edit(*:3619)|(*:3628))|toggle\\-(?|status/([^/]++)(*:3664)|holiday\\-checkin/([^/]++)(*:3698))|delete/([^/]++)(*:3723)|change\\-(?|workspace/([^/]++)(*:3761)|password/([^/]++)(*:3787))|get\\-(?|company\\-employee/([^/]++)(*:3831)|branch\\-employee/([^/]++)(*:3865)|all\\-employees/([^/]++)(*:3897))|f(?|orce\\-logout/([^/]++)(*:3932)|etch\\-employees\\-by\\-department(?|(*:3975)|s(*:3985)))|night\\-attendance/([^/]++)(*:4022)|attendance/(?|ch(?|eck\\-(?|in/([^/]++)/([^/]++)(*:4078)|out/([^/]++)/([^/]++)(*:4108))|ange\\-status/([^/]++)(*:4139))|delete/([^/]++)(*:4164)|([^/]++)(*:4181))|leave_type/delete/([^/]++)(*:4217))|/logout\\-requests/toggle\\-status/([^/]++)(*:4268)|\\-salaries/(?|update\\-cycle/([^/]++)/([^/]++)(*:4322)|payroll/([^/]++)(?|(*:4350)|/(?|print(*:4368)|edit(*:4381)|update(*:4396)|delete(*:4411)))|salary/(?|create/([^/]++)(*:4447)|([^/]++)(*:4464)|edit/([^/]++)(*:4486)|([^/]++)(?|(*:4506)))|([^/]++)/make\\-payment(*:4539)|getWeeks/([^/]++)(*:4565)|salary\\-(?|update/([^/]++)(*:4600)|increment\\-history/([^/]++)(*:4636))|tax\\-report/([^/]++)(?|/(?|de(?|tail(*:4682)|lete(*:4695))|print(*:4710)|edit(*:4723))|(*:4733))))|vent/(?|([^/]++)(?|(*:4764)|/edit(*:4778)|(*:4787))|delete/([^/]++)(*:4812)|remove\\-image/([^/]++)(*:4843)))|departments/(?|([^/]++)(?|(*:4880)|/edit(*:4894)|(*:4903))|toggle\\-status/([^/]++)(*:4936)|delete/([^/]++)(*:4960)|get\\-All\\-Departments/([^/]++)(*:4999))|p(?|osts/(?|([^/]++)(?|(*:5032)|/edit(*:5046)|(*:5055))|toggle\\-status/([^/]++)(*:5088)|delete/([^/]++)(*:5112)|get\\-All\\-posts/([^/]++)(*:5145))|ro(?|jects/(?|([^/]++)(?|(*:5180)|/edit(*:5194)|(*:5203))|delete/([^/]++)(*:5228)|t(?|oggle\\-status/([^/]++)(*:5263)|ask/create/([^/]++)(*:5291))|get\\-(?|assigned\\-members/([^/]++)(*:5335)|employees\\-to\\-add/([^/]++)/([^/]++)(*:5380))|update\\-(?|leaders(*:5408)|members(*:5424))|attachment/create/([^/]++)(*:5460))|motion/(?|([^/]++)(?|(*:5491)|/edit(*:5505)|(*:5514))|delete/([^/]++)(*:5539)|get\\-employees\\-posts/([^/]++)(*:5578)))|ayment\\-methods/(?|([^/]++)(*:5616)|delete/([^/]++)(*:5640)|change\\-status/([^/]++)(*:5672))|f/([^/]++)(?|/edit(*:5700)|(*:5709)))|o(?|ffice\\-times/(?|([^/]++)(?|(*:5751)|/edit(*:5765)|(*:5774))|toggle\\-status/([^/]++)(*:5807)|delete/([^/]++)(*:5831))|vertime/(?|get\\-employee\\-by\\-payroll/([^/]++)(*:5887)|([^/]++)(?|(*:5907)|/edit(*:5921)|(*:5930))|delete/([^/]++)(*:5955)|change\\-status/([^/]++)(*:5987)|get\\-user\\-department\\-data(*:6023)))|holidays/(?|([^/]++)(?|(*:6057)|/edit(*:6071)|(*:6080))|toggle\\-status/([^/]++)(*:6113)|delete/([^/]++)(*:6137))|leave(?|s/(?|([^/]++)(?|(*:6171)|/edit(*:6185)|(*:6194))|toggle\\-(?|status/([^/]++)(*:6230)|early\\-exit/([^/]++)(*:6259))|delete/([^/]++)(*:6284)|get\\-(?|leave\\-types/([^/]++)(*:6322)|gender\\-leave\\-types/([^/]++)/([^/]++)(*:6369)|employee\\-leave\\-types/([^/]++)(*:6409)))|\\-(?|request/(?|s(?|how/([^/]++)(*:6452)|tatus\\-update/([^/]++)(*:6483))|get\\-approvers/([^/]++)(*:6516))|approval/(?|([^/]++)(?|(*:6549)|/edit(*:6563)|(*:6572))|delete/([^/]++)(*:6597)|change\\-status/([^/]++)(*:6629))))|s(?|tatic\\-page\\-contents/(?|([^/]++)(?|(*:6681)|/edit(*:6695)|(*:6704))|toggle\\-status/([^/]++)(*:6737)|delete/([^/]++)(*:6761))|upports/(?|change\\-seen\\-status/([^/]++)(*:6811)|update\\-status/([^/]++)(*:6843)|delete/([^/]++)(*:6867))|alary\\-(?|components/(?|([^/]++)(?|/edit(*:6917)|(*:6926))|delete/([^/]++)(*:6951)|change\\-status/([^/]++)(*:6983))|tds/(?|([^/]++)(?|/edit(*:7016)|(*:7025))|delete/([^/]++)(*:7050)|change\\-status/([^/]++)(*:7082))|groups/(?|([^/]++)(?|/edit(*:7118)|(*:7127))|delete/([^/]++)(*:7152)|change\\-status/([^/]++)(*:7184)))|sf/([^/]++)(?|/edit(*:7214)|(*:7223)))|n(?|oti(?|fications/(?|([^/]++)(?|(*:7268)|/edit(*:7282)|(*:7291))|toggle\\-status/([^/]++)(*:7324)|delete/([^/]++)(*:7348)|send\\-notification/([^/]++)(*:7384))|ces/(?|([^/]++)(?|(*:7412)|/edit(*:7426)|(*:7435))|toggle\\-status/([^/]++)(*:7468)|delete/([^/]++)(*:7492)|send\\-notice/([^/]++)(*:7522)))|fc/delete/([^/]++)(*:7551))|t(?|ime\\-leave\\-request/s(?|tatus\\-update/([^/]++)(*:7611)|how/([^/]++)(*:7632))|e(?|am\\-meetings/(?|([^/]++)(?|(*:7673)|/edit(*:7687)|(*:7696))|delete/([^/]++)(*:7721)|remove\\-image/([^/]++)(*:7752))|rmination(?|\\-types/(?|([^/]++)(?|(*:7796))|delete/([^/]++)(*:7821)|toggle\\-status/([^/]++)(*:7853))|/(?|([^/]++)(?|(*:7878)|/edit(*:7892)|(*:7901))|delete/([^/]++)(*:7926)|status\\-update/([^/]++)(*:7958))))|a(?|sk(?|s/(?|attachment/create/([^/]++)(*:8010)|([^/]++)(?|(*:8030)|/edit(*:8044)|(*:8053))|delete/([^/]++)(*:8078)|toggle\\-status/([^/]++)(*:8110)|get\\-all\\-tasks/([^/]++)(*:8143))|\\-c(?|hecklists/(?|edit/([^/]++)(*:8185)|update/([^/]++)(*:8209)|delete/([^/]++)(*:8233)|toggle\\-status/([^/]++)(*:8265))|omment/(?|delete/([^/]++)(*:8300)|reply/delete/([^/]++)(*:8330)))|/get\\-employees\\-to\\-add/([^/]++)(*:8374))|das/(?|update\\-status/([^/]++)(*:8414)|([^/]++)(?|(*:8434)|/edit(*:8448)|(*:8457))|delete/([^/]++)(*:8482)|toggle\\-active\\-status/([^/]++)(*:8522)|attachment/(?|create/([^/]++)(*:8560)|delete/([^/]++)(*:8584))))|ra(?|in(?|ing(?|\\-types/(?|([^/]++)(?|(*:8634))|delete/([^/]++)(*:8659)|toggle\\-status/([^/]++)(*:8691))|/(?|([^/]++)(?|(*:8716)|/edit(*:8730)|(*:8739))|delete/([^/]++)(*:8764)|toggle\\-status/([^/]++)(*:8796)))|ers/(?|([^/]++)(?|(*:8825)|/edit(*:8839)|(*:8848))|delete/([^/]++)(*:8873)|toggle\\-status/([^/]++)(*:8905)|get\\-all\\-trainers/([^/]++)(*:8941)))|nsfer/(?|([^/]++)(?|(*:8972)|/edit(*:8986)|(*:8995))|delete/([^/]++)(*:9020)|get\\-user\\-(?|transfer\\-(?|department\\-data/([^/]++)(*:9081)|branch\\-data/([^/]++)(*:9111))|data/([^/]++)(*:9134))))|heme\\-color\\-setting/([^/]++)(?|/edit(*:9183)|(*:9192)))|qr/(?|([^/]++)(?|/edit(*:9225)|(*:9234))|delete/([^/]++)(*:9259)|print/([^/]++)(*:9282))|warning/(?|([^/]++)(?|(*:9314)|/edit(*:9328)|(*:9337))|delete/([^/]++)(*:9362))))|/password/reset/([^/]++)(*:9398)|/remove\\-user/([^/]++)(*:9429)|/(.*)(*:9443))/?$}sDu',
    ),
    3 => 
    array (
      39 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.clockwork',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      73 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'debugbar.cache.delete',
            'tags' => NULL,
          ),
          1 => 
          array (
            0 => 'key',
            1 => 'tags',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      106 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.tokens.destroy',
          ),
          1 => 
          array (
            0 => 'token_id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      133 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.clients.update',
          ),
          1 => 
          array (
            0 => 'client_id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'passport.clients.destroy',
          ),
          1 => 
          array (
            0 => 'client_id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      175 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'passport.personal.tokens.destroy',
          ),
          1 => 
          array (
            0 => 'token_id',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      225 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::lRAWEl1gNF32apSJ',
          ),
          1 => 
          array (
            0 => 'userId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      266 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ggAh5IsSHIaDnwkO',
          ),
          1 => 
          array (
            0 => 'contentType',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      283 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::XyQRLHsaSVNGTJ4m',
          ),
          1 => 
          array (
            0 => 'contentType',
            1 => 'titleSlug',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      323 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::LcXPIo2DA7zsBUqT',
          ),
          1 => 
          array (
            0 => 'leaveRequestId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      371 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qt7qQA7pnAIEohh7',
          ),
          1 => 
          array (
            0 => 'timeLeaveRequestId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      401 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::35XiNwoLE8vlLJLf',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      425 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::eFhdW5ZgQY0nVEMl',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      475 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::3oWGSan6jwujKFB3',
          ),
          1 => 
          array (
            0 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      513 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kSBpC66ywiOC0wft',
          ),
          1 => 
          array (
            0 => 'taskId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      544 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::xo7R6TOs2N8XBoKD',
          ),
          1 => 
          array (
            0 => 'taskId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      586 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::r8xugsYOuh5okGck',
          ),
          1 => 
          array (
            0 => 'checklistId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      622 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::WreMsfQFDpbiQvwy',
          ),
          1 => 
          array (
            0 => 'commentId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      651 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::YO0eisYBnZSBibFW',
          ),
          1 => 
          array (
            0 => 'replyId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      681 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::kscdCOri7iIxrFPJ',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      730 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::7db12u4LnFUCCYj8',
          ),
          1 => 
          array (
            0 => 'tadaId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      766 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::o5sVBsfDfulBoiE6',
          ),
          1 => 
          array (
            0 => 'attachmentId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      812 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::qqepxVGtGc9XqgP0',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      826 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::1FnbmqIgu174uvUh',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      851 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::zOcFGnW8n7ju04Ds',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      873 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::5LBqYrSA7Tj7CEjP',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      904 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::nfhqSe8MRQmetfco',
          ),
          1 => 
          array (
            0 => 'warning_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      945 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ElDe74LMGsjnej1u',
          ),
          1 => 
          array (
            0 => 'complaint_id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      985 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.show',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      998 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.edit',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1006 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.update',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.destroy',
          ),
          1 => 
          array (
            0 => 'user',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1039 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1063 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1097 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.users.change-password',
          ),
          1 => 
          array (
            0 => 'userId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1132 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.show',
          ),
          1 => 
          array (
            0 => 'under_time',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1146 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.edit',
          ),
          1 => 
          array (
            0 => 'under_time',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1155 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.update',
          ),
          1 => 
          array (
            0 => 'under_time',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1180 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1212 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.under-time.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1248 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.show',
          ),
          1 => 
          array (
            0 => 'company',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1262 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.edit',
          ),
          1 => 
          array (
            0 => 'company',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1271 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.update',
          ),
          1 => 
          array (
            0 => 'company',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.company.destroy',
          ),
          1 => 
          array (
            0 => 'company',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1301 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.show',
          ),
          1 => 
          array (
            0 => 'complaint',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1315 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.edit',
          ),
          1 => 
          array (
            0 => 'complaint',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1324 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.update',
          ),
          1 => 
          array (
            0 => 'complaint',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1349 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.complaint.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1381 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.show',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1395 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.edit',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1404 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.update',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.destroy',
          ),
          1 => 
          array (
            0 => 'client',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1429 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1461 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.clients.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1496 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.show',
          ),
          1 => 
          array (
            0 => 'branch',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1510 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.edit',
          ),
          1 => 
          array (
            0 => 'branch',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1519 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.update',
          ),
          1 => 
          array (
            0 => 'branch',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.destroy',
          ),
          1 => 
          array (
            0 => 'branch',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1552 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1576 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.branch.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1610 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.edit',
          ),
          1 => 
          array (
            0 => 'bonu',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1619 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.update',
          ),
          1 => 
          array (
            0 => 'bonu',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1644 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1676 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.bonus.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1712 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::jPXqLbXVpglvjrti',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1751 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.biometric-devices.update',
          ),
          1 => 
          array (
            0 => 'biometric_device',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1775 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.biometric-devices.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1826 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.app-settings.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1863 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.show',
          ),
          1 => 
          array (
            0 => 'attendance',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1877 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.edit',
          ),
          1 => 
          array (
            0 => 'attendance',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      1886 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.update',
          ),
          1 => 
          array (
            0 => 'attendance',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.destroy',
          ),
          1 => 
          array (
            0 => 'attendance',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1930 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance-settings.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      1963 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attachment.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2005 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.show',
          ),
          1 => 
          array (
            0 => 'asset_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2019 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.edit',
          ),
          1 => 
          array (
            0 => 'asset_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2028 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.update',
          ),
          1 => 
          array (
            0 => 'asset_type',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2053 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2085 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-types.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2117 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-assignment.store',
          ),
          1 => 
          array (
            0 => 'assetId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2136 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset-assignment.index',
          ),
          1 => 
          array (
            0 => 'assetId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2161 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset.return',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2187 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.show',
          ),
          1 => 
          array (
            0 => 'asset',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2201 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.edit',
          ),
          1 => 
          array (
            0 => 'asset',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2210 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.update',
          ),
          1 => 
          array (
            0 => 'asset',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2235 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2267 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.assets.change-Availability-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2309 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.asset.toggle-repair-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2355 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.setting.store',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2375 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.show',
          ),
          1 => 
          array (
            0 => 'advance_salary',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.update',
          ),
          1 => 
          array (
            0 => 'advance_salary',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2400 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.advance-salaries.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2439 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.show',
          ),
          1 => 
          array (
            0 => 'award_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2453 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.edit',
          ),
          1 => 
          array (
            0 => 'award_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2462 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.update',
          ),
          1 => 
          array (
            0 => 'award_type',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2487 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2519 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.award-types.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2545 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.show',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2559 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.edit',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2568 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.update',
          ),
          1 => 
          array (
            0 => 'award',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2593 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.awards.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2640 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.show',
          ),
          1 => 
          array (
            0 => 'general_setting',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2654 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.edit',
          ),
          1 => 
          array (
            0 => 'general_setting',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      2663 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.update',
          ),
          1 => 
          array (
            0 => 'general_setting',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.destroy',
          ),
          1 => 
          array (
            0 => 'general_setting',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2688 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.general-settings.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2737 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::4K1bACRiAHiz9iCa',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2769 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::Cf9tdFyOYEjffsQc',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2801 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::Jz7yxoIlcflNHpMn',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2829 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::iBVJZcjk3YDPH4DJ',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2859 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::4Wpu31LaGR2gW7J1',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2897 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::4Y8o0UHDbBAk9HEr',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2924 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::pdCQaEQrDGRHhaHY',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2955 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::wlL5L4VZ1LlQL7TY',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      2996 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::lXmEUFND67qxgitz',
          ),
          1 => 
          array (
            0 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3041 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.feature.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3080 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.edit',
          ),
          1 => 
          array (
            0 => 'fiscal_year',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3089 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.update',
          ),
          1 => 
          array (
            0 => 'fiscal_year',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3114 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3155 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.fiscal_year.toggle-current-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3192 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.show',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3206 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.edit',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3215 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.update',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.destroy',
          ),
          1 => 
          array (
            0 => 'role',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3248 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3272 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3301 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.roles.permission',
          ),
          1 => 
          array (
            0 => 'roleId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3338 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.role.assign-permissions',
          ),
          1 => 
          array (
            0 => 'roleId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3368 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.show',
          ),
          1 => 
          array (
            0 => 'router',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3382 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.edit',
          ),
          1 => 
          array (
            0 => 'router',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3391 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.update',
          ),
          1 => 
          array (
            0 => 'router',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.destroy',
          ),
          1 => 
          array (
            0 => 'router',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3424 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3448 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.routers.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3484 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.show',
          ),
          1 => 
          array (
            0 => 'resignation',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3498 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.edit',
          ),
          1 => 
          array (
            0 => 'resignation',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3507 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.update',
          ),
          1 => 
          array (
            0 => 'resignation',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3532 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3564 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.resignation.update-status',
          ),
          1 => 
          array (
            0 => 'resignationId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3605 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.show',
          ),
          1 => 
          array (
            0 => 'employee',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3619 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.edit',
          ),
          1 => 
          array (
            0 => 'employee',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3628 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.update',
          ),
          1 => 
          array (
            0 => 'employee',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.destroy',
          ),
          1 => 
          array (
            0 => 'employee',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3664 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3698 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.toggle-holiday-checkin',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3723 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3761 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.change-workspace',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3787 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.change-password',
          ),
          1 => 
          array (
            0 => 'userId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3831 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.getAllCompanyUsers',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3865 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.getAllBranchEmployees',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3897 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.getAllUsersByDepartmentId',
          ),
          1 => 
          array (
            0 => 'departmentId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3932 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.force-logout',
          ),
          1 => 
          array (
            0 => 'userId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      3975 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.fetchByDepartment',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      3985 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.getByDepartment',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4022 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.night_attendances.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4078 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.check-in',
          ),
          1 => 
          array (
            0 => 'companyId',
            1 => 'userId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4108 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.check-out',
          ),
          1 => 
          array (
            0 => 'companyId',
            1 => 'userId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4139 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendances.change-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4164 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.attendance.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4181 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.dashboard.takeAttendance',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4217 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee_leave_type.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4268 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.logout-requests.accept',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4322 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.update-salary-cycle',
          ),
          1 => 
          array (
            0 => 'employeeId',
            1 => 'cycle',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4350 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salary.payroll-detail',
          ),
          1 => 
          array (
            0 => 'payslipId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4368 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salary.payroll-print',
          ),
          1 => 
          array (
            0 => 'payslipId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4381 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salary.payroll-edit',
          ),
          1 => 
          array (
            0 => 'payslipId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4396 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salary.payroll-update',
          ),
          1 => 
          array (
            0 => 'payslipId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4411 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salary.payroll-delete',
          ),
          1 => 
          array (
            0 => 'payslipId',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4447 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.add',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4464 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.store-salary',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4486 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.edit-salary',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4506 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.update-salary',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.delete-salary',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4539 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.make_payment',
          ),
          1 => 
          array (
            0 => 'payslipId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4565 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.get-weeks',
          ),
          1 => 
          array (
            0 => 'year',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4600 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.increase-salary',
          ),
          1 => 
          array (
            0 => 'accountId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4636 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employee-salaries.salary-revise-history.show',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4682 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payroll.tax-report.detail',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4695 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payroll.tax-report.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4710 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payroll.tax-report.print',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4723 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payroll.tax-report.edit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4733 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payroll.tax-report.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4764 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.show',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4778 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.edit',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4787 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.update',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.destroy',
          ),
          1 => 
          array (
            0 => 'event',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4812 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4843 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.event.remove-image',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4880 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.show',
          ),
          1 => 
          array (
            0 => 'department',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4894 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.edit',
          ),
          1 => 
          array (
            0 => 'department',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      4903 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.update',
          ),
          1 => 
          array (
            0 => 'department',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.destroy',
          ),
          1 => 
          array (
            0 => 'department',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4936 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4960 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      4999 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.departments.getAllDepartmentsByBranchId',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5032 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.show',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5046 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.edit',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5055 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.update',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.destroy',
          ),
          1 => 
          array (
            0 => 'post',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5088 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5112 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5145 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.posts.getAllPostsByBranchId',
          ),
          1 => 
          array (
            0 => 'deptId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5180 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.show',
          ),
          1 => 
          array (
            0 => 'project',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5194 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.edit',
          ),
          1 => 
          array (
            0 => 'project',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5203 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.update',
          ),
          1 => 
          array (
            0 => 'project',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.destroy',
          ),
          1 => 
          array (
            0 => 'project',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5228 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5263 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5291 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.project-task.create',
          ),
          1 => 
          array (
            0 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5335 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.get-assigned-members',
          ),
          1 => 
          array (
            0 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5380 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.add-employee',
          ),
          1 => 
          array (
            0 => 'addEmployeeType',
            1 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5408 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.update-leader-data',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5424 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.projects.update-member-data',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5460 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.project-attachment.create',
          ),
          1 => 
          array (
            0 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5491 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.show',
          ),
          1 => 
          array (
            0 => 'promotion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5505 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.edit',
          ),
          1 => 
          array (
            0 => 'promotion',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5514 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.update',
          ),
          1 => 
          array (
            0 => 'promotion',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5539 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5578 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.promotion.getUsersAndPostByDepartment',
          ),
          1 => 
          array (
            0 => 'departmentId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5616 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-methods.update',
          ),
          1 => 
          array (
            0 => 'payment_method',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5640 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-methods.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5672 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.payment-methods.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5700 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pf.edit',
          ),
          1 => 
          array (
            0 => 'pf',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5709 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.pf.update',
          ),
          1 => 
          array (
            0 => 'pf',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5751 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.show',
          ),
          1 => 
          array (
            0 => 'office_time',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5765 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.edit',
          ),
          1 => 
          array (
            0 => 'office_time',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5774 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.update',
          ),
          1 => 
          array (
            0 => 'office_time',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.destroy',
          ),
          1 => 
          array (
            0 => 'office_time',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5807 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5831 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.office-times.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5887 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.get-employee',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5907 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.show',
          ),
          1 => 
          array (
            0 => 'overtime',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5921 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.edit',
          ),
          1 => 
          array (
            0 => 'overtime',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      5930 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.update',
          ),
          1 => 
          array (
            0 => 'overtime',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5955 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      5987 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.overtime.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6023 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::uPuhbvQpJaoLd5xU',
          ),
          1 => 
          array (
          ),
          2 => 
          array (
            'POST' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      6057 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.show',
          ),
          1 => 
          array (
            0 => 'holiday',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6071 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.edit',
          ),
          1 => 
          array (
            0 => 'holiday',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      6080 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.update',
          ),
          1 => 
          array (
            0 => 'holiday',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.destroy',
          ),
          1 => 
          array (
            0 => 'holiday',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6113 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6137 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.holidays.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6171 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.show',
          ),
          1 => 
          array (
            0 => 'leaf',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6185 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.edit',
          ),
          1 => 
          array (
            0 => 'leaf',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      6194 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.update',
          ),
          1 => 
          array (
            0 => 'leaf',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.destroy',
          ),
          1 => 
          array (
            0 => 'leaf',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6230 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6259 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.toggle-early-exit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6284 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6322 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.',
          ),
          1 => 
          array (
            0 => 'earlyExitStatus',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6369 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.gender-data',
          ),
          1 => 
          array (
            0 => 'branchId',
            1 => 'gender',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6409 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leaves.employee-data',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6452 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.show',
          ),
          1 => 
          array (
            0 => 'leaveId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6483 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.update-status',
          ),
          1 => 
          array (
            0 => 'leaveRequestId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6516 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-request.approval-details',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6549 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.show',
          ),
          1 => 
          array (
            0 => 'leave_approval',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6563 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.edit',
          ),
          1 => 
          array (
            0 => 'leave_approval',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      6572 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.update',
          ),
          1 => 
          array (
            0 => 'leave_approval',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6597 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6629 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.leave-approval.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6681 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.show',
          ),
          1 => 
          array (
            0 => 'static_page_content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6695 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.edit',
          ),
          1 => 
          array (
            0 => 'static_page_content',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      6704 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.update',
          ),
          1 => 
          array (
            0 => 'static_page_content',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.destroy',
          ),
          1 => 
          array (
            0 => 'static_page_content',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6737 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6761 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.static-page-contents.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6811 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.supports.changeSeenStatus',
          ),
          1 => 
          array (
            0 => 'queryId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6843 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.supports.updateStatus',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6867 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.supports.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6917 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.edit',
          ),
          1 => 
          array (
            0 => 'salary_component',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      6926 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.update',
          ),
          1 => 
          array (
            0 => 'salary_component',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6951 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      6983 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-components.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7016 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.edit',
          ),
          1 => 
          array (
            0 => 'salary_td',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7025 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.update',
          ),
          1 => 
          array (
            0 => 'salary_td',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7050 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7082 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-tds.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7118 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.edit',
          ),
          1 => 
          array (
            0 => 'salary_group',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7127 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.update',
          ),
          1 => 
          array (
            0 => 'salary_group',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7152 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7184 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.salary-groups.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7214 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ssf.edit',
          ),
          1 => 
          array (
            0 => 'ssf',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7223 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.ssf.update',
          ),
          1 => 
          array (
            0 => 'ssf',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7268 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.show',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7282 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.edit',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7291 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.update',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.destroy',
          ),
          1 => 
          array (
            0 => 'notification',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7324 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7348 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7384 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notifications.send-notification',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7412 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.show',
          ),
          1 => 
          array (
            0 => 'notice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7426 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.edit',
          ),
          1 => 
          array (
            0 => 'notice',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7435 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.update',
          ),
          1 => 
          array (
            0 => 'notice',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.destroy',
          ),
          1 => 
          array (
            0 => 'notice',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7468 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7492 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7522 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.notices.send-notice',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7551 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.nfc.destroy',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7611 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.time-leave-request.update-status',
          ),
          1 => 
          array (
            0 => 'leaveRequestId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7632 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.time-leave-request.show',
          ),
          1 => 
          array (
            0 => 'leaveId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7673 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.show',
          ),
          1 => 
          array (
            0 => 'team_meeting',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7687 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.edit',
          ),
          1 => 
          array (
            0 => 'team_meeting',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7696 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.update',
          ),
          1 => 
          array (
            0 => 'team_meeting',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.destroy',
          ),
          1 => 
          array (
            0 => 'team_meeting',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7721 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7752 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.team-meetings.remove-image',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7796 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination-types.show',
          ),
          1 => 
          array (
            0 => 'termination_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination-types.update',
          ),
          1 => 
          array (
            0 => 'termination_type',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7821 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination-types.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7853 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination-types.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7878 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.show',
          ),
          1 => 
          array (
            0 => 'termination',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7892 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.edit',
          ),
          1 => 
          array (
            0 => 'termination',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      7901 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.update',
          ),
          1 => 
          array (
            0 => 'termination',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7926 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      7958 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.termination.update-status',
          ),
          1 => 
          array (
            0 => 'terminationId',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8010 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-attachment.create',
          ),
          1 => 
          array (
            0 => 'taskId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8030 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.show',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8044 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.edit',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      8053 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.update',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.destroy',
          ),
          1 => 
          array (
            0 => 'task',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8078 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8110 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8143 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.employees.getAllTaskByProjectId',
          ),
          1 => 
          array (
            0 => 'projectId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8185 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-checklists.edit',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8209 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-checklists.update',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8233 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-checklists.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8265 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.task-checklists.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8300 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.comment.delete',
          ),
          1 => 
          array (
            0 => 'commentId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8330 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.reply.delete',
          ),
          1 => 
          array (
            0 => 'replyId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8374 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tasks.add-employee',
          ),
          1 => 
          array (
            0 => 'taskId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8414 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.update-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'PUT' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8434 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.show',
          ),
          1 => 
          array (
            0 => 'tada',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8448 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.edit',
          ),
          1 => 
          array (
            0 => 'tada',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      8457 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.update',
          ),
          1 => 
          array (
            0 => 'tada',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.destroy',
          ),
          1 => 
          array (
            0 => 'tada',
          ),
          2 => 
          array (
            'DELETE' => 0,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8482 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8522 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8560 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.attachment.create',
          ),
          1 => 
          array (
            0 => 'tadaId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8584 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.tadas.attachment-delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8634 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training-types.show',
          ),
          1 => 
          array (
            0 => 'training_type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training-types.update',
          ),
          1 => 
          array (
            0 => 'training_type',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8659 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training-types.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8691 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training-types.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8716 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.show',
          ),
          1 => 
          array (
            0 => 'training',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8730 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.edit',
          ),
          1 => 
          array (
            0 => 'training',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      8739 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.update',
          ),
          1 => 
          array (
            0 => 'training',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8764 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8796 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.training.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8825 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.show',
          ),
          1 => 
          array (
            0 => 'trainer',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8839 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.edit',
          ),
          1 => 
          array (
            0 => 'trainer',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      8848 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.update',
          ),
          1 => 
          array (
            0 => 'trainer',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8873 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8905 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.toggle-status',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8941 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.trainers.getAllTrainersByType',
          ),
          1 => 
          array (
            0 => 'type',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8972 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.show',
          ),
          1 => 
          array (
            0 => 'transfer',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      8986 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.edit',
          ),
          1 => 
          array (
            0 => 'transfer',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      8995 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.update',
          ),
          1 => 
          array (
            0 => 'transfer',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9020 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.transfer.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9081 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::0djg0GN3kLjQ1398',
          ),
          1 => 
          array (
            0 => 'departmentId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9111 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::2iP1jdZd30XCbI2n',
          ),
          1 => 
          array (
            0 => 'branchId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9134 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.generated::UmsoPupS70eno53q',
          ),
          1 => 
          array (
            0 => 'employeeId',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9183 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.theme-color-setting.edit',
          ),
          1 => 
          array (
            0 => 'theme_color_setting',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      9192 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.theme-color-setting.update',
          ),
          1 => 
          array (
            0 => 'theme_color_setting',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9225 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.edit',
          ),
          1 => 
          array (
            0 => 'qr',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      9234 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.update',
          ),
          1 => 
          array (
            0 => 'qr',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9259 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.destroy',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9282 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.qr.print',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9314 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.show',
          ),
          1 => 
          array (
            0 => 'warning',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9328 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.edit',
          ),
          1 => 
          array (
            0 => 'warning',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => false,
          6 => NULL,
        ),
      ),
      9337 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.update',
          ),
          1 => 
          array (
            0 => 'warning',
          ),
          2 => 
          array (
            'PUT' => 0,
            'PATCH' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9362 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'admin.warning.delete',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9398 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'password.reset',
          ),
          1 => 
          array (
            0 => 'token',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9429 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::k6GfsShDPKEnRFCb',
          ),
          1 => 
          array (
            0 => 'id',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
      ),
      9443 => 
      array (
        0 => 
        array (
          0 => 
          array (
            '_route' => 'generated::ySFdF52WLugeoUvd',
          ),
          1 => 
          array (
            0 => 'fallbackPlaceholder',
          ),
          2 => 
          array (
            'GET' => 0,
            'HEAD' => 1,
          ),
          3 => NULL,
          4 => false,
          5 => true,
          6 => NULL,
        ),
        1 => 
        array (
          0 => NULL,
          1 => NULL,
          2 => NULL,
          3 => NULL,
          4 => false,
          5 => false,
          6 => 0,
        ),
      ),
    ),
    4 => NULL,
  ),
  'attributes' => 
  array (
    'debugbar.openhandler' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/open',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'as' => 'debugbar.openhandler',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@handle',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.clockwork' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/clockwork/{id}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'as' => 'debugbar.clockwork',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\OpenHandlerController@clockwork',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.css' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/stylesheets',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'as' => 'debugbar.assets.css',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@css',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.assets.js' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_debugbar/assets/javascript',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'as' => 'debugbar.assets.js',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\AssetController@js',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.cache.delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => '_debugbar/cache/{key}/{tags?}',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'as' => 'debugbar.cache.delete',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\CacheController@delete',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'debugbar.queries.explain' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_debugbar/queries/explain',
      'action' => 
      array (
        'domain' => NULL,
        'middleware' => 
        array (
          0 => 'Barryvdh\\Debugbar\\Middleware\\DebugbarEnabled',
        ),
        'uses' => 'Barryvdh\\Debugbar\\Controllers\\QueriesController@explain',
        'as' => 'debugbar.queries.explain',
        'controller' => 'Barryvdh\\Debugbar\\Controllers\\QueriesController@explain',
        'namespace' => 'Barryvdh\\Debugbar\\Controllers',
        'prefix' => '_debugbar',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.token' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'oauth/token',
      'action' => 
      array (
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\AccessTokenController@issueToken',
        'as' => 'passport.token',
        'middleware' => 'throttle',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\AccessTokenController@issueToken',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.authorizations.authorize' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/authorize',
      'action' => 
      array (
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\AuthorizationController@authorize',
        'as' => 'passport.authorizations.authorize',
        'middleware' => 'web',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\AuthorizationController@authorize',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.token.refresh' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'oauth/token/refresh',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\TransientTokenController@refresh',
        'as' => 'passport.token.refresh',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\TransientTokenController@refresh',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.authorizations.approve' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'oauth/authorize',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\ApproveAuthorizationController@approve',
        'as' => 'passport.authorizations.approve',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\ApproveAuthorizationController@approve',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.authorizations.deny' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'oauth/authorize',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\DenyAuthorizationController@deny',
        'as' => 'passport.authorizations.deny',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\DenyAuthorizationController@deny',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.tokens.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/tokens',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\AuthorizedAccessTokenController@forUser',
        'as' => 'passport.tokens.index',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\AuthorizedAccessTokenController@forUser',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.tokens.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'oauth/tokens/{token_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\AuthorizedAccessTokenController@destroy',
        'as' => 'passport.tokens.destroy',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\AuthorizedAccessTokenController@destroy',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.clients.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@forUser',
        'as' => 'passport.clients.index',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@forUser',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.clients.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'oauth/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@store',
        'as' => 'passport.clients.store',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@store',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.clients.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'oauth/clients/{client_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@update',
        'as' => 'passport.clients.update',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@update',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.clients.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'oauth/clients/{client_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@destroy',
        'as' => 'passport.clients.destroy',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\ClientController@destroy',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.scopes.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/scopes',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\ScopeController@all',
        'as' => 'passport.scopes.index',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\ScopeController@all',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.personal.tokens.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'oauth/personal-access-tokens',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\PersonalAccessTokenController@forUser',
        'as' => 'passport.personal.tokens.index',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\PersonalAccessTokenController@forUser',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.personal.tokens.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'oauth/personal-access-tokens',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\PersonalAccessTokenController@store',
        'as' => 'passport.personal.tokens.store',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\PersonalAccessTokenController@store',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'passport.personal.tokens.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'oauth/personal-access-tokens/{token_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'auth:web',
        ),
        'uses' => 'Laravel\\Passport\\Http\\Controllers\\PersonalAccessTokenController@destroy',
        'as' => 'passport.personal.tokens.destroy',
        'controller' => 'Laravel\\Passport\\Http\\Controllers\\PersonalAccessTokenController@destroy',
        'namespace' => 'Laravel\\Passport\\Http\\Controllers',
        'prefix' => 'oauth',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'sanctum.csrf-cookie' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'sanctum/csrf-cookie',
      'action' => 
      array (
        'uses' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'controller' => 'Laravel\\Sanctum\\Http\\Controllers\\CsrfCookieController@show',
        'namespace' => NULL,
        'prefix' => 'sanctum',
        'where' => 
        array (
        ),
        'middleware' => 
        array (
          0 => 'web',
        ),
        'as' => 'sanctum.csrf-cookie',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.healthCheck' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '_ignition/health-check',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\HealthCheckController',
        'as' => 'ignition.healthCheck',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.executeSolution' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/execute-solution',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\ExecuteSolutionController',
        'as' => 'ignition.executeSolution',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'ignition.updateConfig' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => '_ignition/update-config',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'Spatie\\LaravelIgnition\\Http\\Middleware\\RunnableSolutionsEnabled',
        ),
        'uses' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController@__invoke',
        'controller' => 'Spatie\\LaravelIgnition\\Http\\Controllers\\UpdateConfigController',
        'as' => 'ignition.updateConfig',
        'namespace' => NULL,
        'prefix' => '_ignition',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::VpTIgDBr1zgU9hJ9' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\Auth\\AuthApiController@login',
        'controller' => 'App\\Http\\Controllers\\Api\\Auth\\AuthApiController@login',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::VpTIgDBr1zgU9hJ9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kp1O8ECyijotxvZd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\Auth\\AuthApiController@logout',
        'controller' => 'App\\Http\\Controllers\\Api\\Auth\\AuthApiController@logout',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::kp1O8ECyijotxvZd',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2lf0YNnon4efAbEt' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@userProfileDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@userProfileDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::2lf0YNnon4efAbEt',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::v1pi9tfdapvWvp5t' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/users/change-password',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@changePassword',
        'controller' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@changePassword',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::v1pi9tfdapvWvp5t',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::hVxXSv8KzuiM7nVg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/users/update-profile',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@updateUserProfile',
        'controller' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@updateUserProfile',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::hVxXSv8KzuiM7nVg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::lRAWEl1gNF32apSJ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/profile-detail/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@findEmployeeDetailById',
        'controller' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@findEmployeeDetailById',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::lRAWEl1gNF32apSJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::rKvvD3YVbP4zx7Dt' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/users/company/team-sheet',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@getTeamSheetOfCompany',
        'controller' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@getTeamSheetOfCompany',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::rKvvD3YVbP4zx7Dt',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::CqDkRyUCW3MYBkOS' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/users/location',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@storeLocation',
        'controller' => 'App\\Http\\Controllers\\Api\\UserProfileApiController@storeLocation',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::CqDkRyUCW3MYBkOS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ggAh5IsSHIaDnwkO' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/static-page-content/{contentType}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\StaticPageContentApiController@getStaticPageContentByContentType',
        'controller' => 'App\\Http\\Controllers\\Api\\StaticPageContentApiController@getStaticPageContentByContentType',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ggAh5IsSHIaDnwkO',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::czgQNomEpdTgxdoj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/company-rules',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\StaticPageContentApiController@getCompanyRulesDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\StaticPageContentApiController@getCompanyRulesDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::czgQNomEpdTgxdoj',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::XyQRLHsaSVNGTJ4m' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/static-page-content/{contentType}/{titleSlug}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\StaticPageContentApiController@getStaticPageContentByContentTypeAndTitleSlug',
        'controller' => 'App\\Http\\Controllers\\Api\\StaticPageContentApiController@getStaticPageContentByContentTypeAndTitleSlug',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::XyQRLHsaSVNGTJ4m',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::hgYRpl40zWatpna3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\NotificationApiController@getAllRecentPublishedNotification',
        'controller' => 'App\\Http\\Controllers\\Api\\NotificationApiController@getAllRecentPublishedNotification',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::hgYRpl40zWatpna3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ObixOmOzaA6VSJwR' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/notices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\NoticeApiController@getAllRecentlyReceivedNotice',
        'controller' => 'App\\Http\\Controllers\\Api\\NoticeApiController@getAllRecentlyReceivedNotice',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ObixOmOzaA6VSJwR',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::GhuHg0laBlWpUUhZ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\DashboardApiController@userDashboardDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\DashboardApiController@userDashboardDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::GhuHg0laBlWpUUhZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::j2IkRAEo2epQWzRX' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employees/check-in',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@employeeCheckIn',
        'controller' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@employeeCheckIn',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::j2IkRAEo2epQWzRX',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::9jBZsEKhYOzLXmge' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employees/check-out',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@employeeCheckOut',
        'controller' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@employeeCheckOut',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::9jBZsEKhYOzLXmge',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::5P6f8FXrmxKEPgaC' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employees/attendance-detail',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@getEmployeeAllAttendanceDetailOfTheMonth',
        'controller' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@getEmployeeAllAttendanceDetailOfTheMonth',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::5P6f8FXrmxKEPgaC',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::zqYFE3lBoCk5zjOl' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employees/attendance',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@employeeAttendance',
        'controller' => 'App\\Http\\Controllers\\Api\\AttendanceApiController@employeeAttendance',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::zqYFE3lBoCk5zjOl',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::8d7wDaWZjmcB9I4a' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/leave-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveTypeApiController@getAllLeaveTypeWithEmployeeLeaveRecord',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveTypeApiController@getAllLeaveTypeWithEmployeeLeaveRecord',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::8d7wDaWZjmcB9I4a',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::tK2nEYeUoO9HXMtS' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/leave-requests/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@saveLeaveRequestDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@saveLeaveRequestDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::tK2nEYeUoO9HXMtS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xIcGBrT0gtcIdD7V' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/leave-requests/employee-leave-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getAllLeaveRequestOfEmployee',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getAllLeaveRequestOfEmployee',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::xIcGBrT0gtcIdD7V',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QCL30K9ZbDFR8RcN' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/leave-requests/employee-leave-calendar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getLeaveCountDetailOfEmployeeOfTwoMonth',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getLeaveCountDetailOfEmployeeOfTwoMonth',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::QCL30K9ZbDFR8RcN',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::jpqJ2vuGJ9rBFxCZ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/leave-requests/employee-leave-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getAllEmployeeLeaveDetailBySpecificDay',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getAllEmployeeLeaveDetailBySpecificDay',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::jpqJ2vuGJ9rBFxCZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ceM5adQ8TJIASr7r' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/office-calendar',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getCalendarDetailBySpecificDay',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@getCalendarDetailBySpecificDay',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ceM5adQ8TJIASr7r',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::LcXPIo2DA7zsBUqT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/leave-requests/cancel/{leaveRequestId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@cancelLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@cancelLeaveRequest',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::LcXPIo2DA7zsBUqT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DiWUmEvK4U2Swbtg' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/time-leave-requests/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@saveTimeLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@saveTimeLeaveRequest',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::DiWUmEvK4U2Swbtg',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qt7qQA7pnAIEohh7' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/time-leave-requests/cancel/{timeLeaveRequestId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\LeaveApiController@cancelTimeLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Api\\LeaveApiController@cancelTimeLeaveRequest',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::qt7qQA7pnAIEohh7',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UPMC5p804bmmjeq7' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/team-meetings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TeamMeetingApiController@getAllAssignedTeamMeetingDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\TeamMeetingApiController@getAllAssignedTeamMeetingDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::UPMC5p804bmmjeq7',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::35XiNwoLE8vlLJLf' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/team-meetings/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TeamMeetingApiController@findTeamMeetingDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\TeamMeetingApiController@findTeamMeetingDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::35XiNwoLE8vlLJLf',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::0XRqIKuvU8iUMExL' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/holidays',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\HolidayApiController@getAllActiveHoliday',
        'controller' => 'App\\Http\\Controllers\\Api\\HolidayApiController@getAllActiveHoliday',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::0XRqIKuvU8iUMExL',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::VWQ6OhDtKDJjmvoM' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/project-management-dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ProjectManagementDashboardApiController@getUserProjectManagementDashboardDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\ProjectManagementDashboardApiController@getUserProjectManagementDashboardDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::VWQ6OhDtKDJjmvoM',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DF3orQoTPLzpOgJq' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-projects-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ProjectApiController@getUserAssignedAllProjects',
        'controller' => 'App\\Http\\Controllers\\Api\\ProjectApiController@getUserAssignedAllProjects',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::DF3orQoTPLzpOgJq',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3oWGSan6jwujKFB3' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-projects-detail/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ProjectApiController@getProjectDetailById',
        'controller' => 'App\\Http\\Controllers\\Api\\ProjectApiController@getProjectDetailById',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::3oWGSan6jwujKFB3',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::96kB0OxBkAJV6KLW' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task-list',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskApiController@getUserAssignedAllTasks',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskApiController@getUserAssignedAllTasks',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::96kB0OxBkAJV6KLW',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kSBpC66ywiOC0wft' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task-detail/{taskId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskApiController@getTaskDetailById',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskApiController@getTaskDetailById',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::kSBpC66ywiOC0wft',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::xo7R6TOs2N8XBoKD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task-detail/change-status/{taskId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskApiController@changeTaskStatus',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskApiController@changeTaskStatus',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::xo7R6TOs2N8XBoKD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::UfXH1RXMliCA5n6S' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task-comments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskApiController@getTaskComments',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskApiController@getTaskComments',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::UfXH1RXMliCA5n6S',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::r8xugsYOuh5okGck' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task-checklist/toggle-status/{checklistId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskChecklistApiController@toggleCheckListIsCompletedStatus',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskChecklistApiController@toggleCheckListIsCompletedStatus',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::r8xugsYOuh5okGck',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Jka3kSJ2KhwMEi9z' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/assigned-task/comments/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskCommentApiController@saveComment',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskCommentApiController@saveComment',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::Jka3kSJ2KhwMEi9z',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::WreMsfQFDpbiQvwy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task/comment/delete/{commentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskCommentApiController@deleteComment',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskCommentApiController@deleteComment',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::WreMsfQFDpbiQvwy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::YO0eisYBnZSBibFW' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assigned-task/reply/delete/{replyId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TaskCommentApiController@deleteReply',
        'controller' => 'App\\Http\\Controllers\\Api\\TaskCommentApiController@deleteReply',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::YO0eisYBnZSBibFW',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qyet93tatTDRshO2' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/support/query-store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\SupportApiController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\SupportApiController@store',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::qyet93tatTDRshO2',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::t6fKmxgq1c58EkTk' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/support/department-lists',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\SupportApiController@getAuthUserBranchDepartmentLists',
        'controller' => 'App\\Http\\Controllers\\Api\\SupportApiController@getAuthUserBranchDepartmentLists',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::t6fKmxgq1c58EkTk',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::D7awe7BghQCteMc8' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/support/get-user-query-lists',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\SupportApiController@getAllAuthUserSupportQueryList',
        'controller' => 'App\\Http\\Controllers\\Api\\SupportApiController@getAllAuthUserSupportQueryList',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::D7awe7BghQCteMc8',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::7aqj4XgLgdVLkOOp' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/tada-lists',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TadaApiController@getEmployeesTadaLists',
        'controller' => 'App\\Http\\Controllers\\Api\\TadaApiController@getEmployeesTadaLists',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::7aqj4XgLgdVLkOOp',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::7db12u4LnFUCCYj8' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/tada-details/{tadaId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TadaApiController@getEmployeesTadaDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\TadaApiController@getEmployeesTadaDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::7db12u4LnFUCCYj8',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qJx0ngg8gvCMuoWZ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employee/tada/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TadaApiController@storeTadaDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\TadaApiController@storeTadaDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::qJx0ngg8gvCMuoWZ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::Spezupr9s1mLgSq9' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employee/tada/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TadaApiController@updateTadaDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\TadaApiController@updateTadaDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::Spezupr9s1mLgSq9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::o5sVBsfDfulBoiE6' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/tada/delete-attachment/{attachmentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TadaApiController@deleteTadaAttachment',
        'controller' => 'App\\Http\\Controllers\\Api\\TadaApiController@deleteTadaAttachment',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::o5sVBsfDfulBoiE6',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3Kv5DPLwDmfKmnBj' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/advance-salaries-lists',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@getEmployeesAdvanceSalaryDetailLists',
        'controller' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@getEmployeesAdvanceSalaryDetailLists',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::3Kv5DPLwDmfKmnBj',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::DlqZmtM3nxVpwzkS' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employee/advance-salaries/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@store',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::DlqZmtM3nxVpwzkS',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qqepxVGtGc9XqgP0' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/advance-salaries-detail/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@getEmployeeAdvanceSalaryDetailById',
        'controller' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@getEmployeeAdvanceSalaryDetailById',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::qqepxVGtGc9XqgP0',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::1FnbmqIgu174uvUh' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employee/advance-salaries-detail/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@updateDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\AdvanceSalaryApiController@updateDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::1FnbmqIgu174uvUh',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::yuXfcaeY01PLDs3J' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/nfc/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\NfcApiController@save',
        'controller' => 'App\\Http\\Controllers\\Api\\NfcApiController@save',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::yuXfcaeY01PLDs3J',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::knPmBFZEK0avF99T' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employee/push',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\PushNotificationController@sendPushNotification',
        'controller' => 'App\\Http\\Controllers\\Api\\PushNotificationController@sendPushNotification',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::knPmBFZEK0avF99T',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::STUGjPaKze7mQS2z' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/employee/payslip',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EmployeePayrollApiController@getPayrollList',
        'controller' => 'App\\Http\\Controllers\\Api\\EmployeePayrollApiController@getPayrollList',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::STUGjPaKze7mQS2z',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::zOcFGnW8n7ju04Ds' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/employee/payslip/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EmployeePayrollApiController@getEmployeePayslipDetailById',
        'controller' => 'App\\Http\\Controllers\\Api\\EmployeePayrollApiController@getEmployeePayslipDetailById',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::zOcFGnW8n7ju04Ds',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::4XKv7N7BtIxHbQuT' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/awards',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AwardApiController@getEmployeeAwards',
        'controller' => 'App\\Http\\Controllers\\Api\\AwardApiController@getEmployeeAwards',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::4XKv7N7BtIxHbQuT',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::oWj1FoE0gQ1ptRxD' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/events',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EventApiController@getAllAssignedEvents',
        'controller' => 'App\\Http\\Controllers\\Api\\EventApiController@getAllAssignedEvents',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::oWj1FoE0gQ1ptRxD',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::5LBqYrSA7Tj7CEjP' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/event/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EventApiController@findEventDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\EventApiController@findEventDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::5LBqYrSA7Tj7CEjP',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QT0Ac3fhRXwWhSqi' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/training',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TrainingApiController@getAllTrainings',
        'controller' => 'App\\Http\\Controllers\\Api\\TrainingApiController@getAllTrainings',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::QT0Ac3fhRXwWhSqi',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::eFhdW5ZgQY0nVEMl' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/training/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\TrainingApiController@findTrainingDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\TrainingApiController@findTrainingDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::eFhdW5ZgQY0nVEMl',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::QooHSPZKUWANDEqn' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/resignation/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResignationApiController@saveResignationDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\ResignationApiController@saveResignationDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::QooHSPZKUWANDEqn',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::jIp1c1MId9f5ar5b' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/resignation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ResignationApiController@resignationDetail',
        'controller' => 'App\\Http\\Controllers\\Api\\ResignationApiController@resignationDetail',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::jIp1c1MId9f5ar5b',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::nfhqSe8MRQmetfco' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/warning/store/{warning_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\WarningApiController@saveWarningResponse',
        'controller' => 'App\\Http\\Controllers\\Api\\WarningApiController@saveWarningResponse',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::nfhqSe8MRQmetfco',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::2Uqw3mUfHoG41j3S' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/warning',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\WarningApiController@getAllWarnings',
        'controller' => 'App\\Http\\Controllers\\Api\\WarningApiController@getAllWarnings',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::2Uqw3mUfHoG41j3S',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::cD5c8yFfX9jc7qD9' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/complaint/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@saveComplaint',
        'controller' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@saveComplaint',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::cD5c8yFfX9jc7qD9',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ElDe74LMGsjnej1u' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/complaint/response/store/{complaint_id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@saveComplaintResponse',
        'controller' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@saveComplaintResponse',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::ElDe74LMGsjnej1u',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::MHlZMeV7ncVBiSJs' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/complaint',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@getAllComplaints',
        'controller' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@getAllComplaints',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::MHlZMeV7ncVBiSJs',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::qwrDf95kmUoxNhvQ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/department-employees',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@getDepartmentEmployees',
        'controller' => 'App\\Http\\Controllers\\Api\\ComplaintApiController@getDepartmentEmployees',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::qwrDf95kmUoxNhvQ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3k39Vl0FlBIdWHoW' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/assets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AssetAssignmentApiController@index',
        'controller' => 'App\\Http\\Controllers\\Api\\AssetAssignmentApiController@index',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::3k39Vl0FlBIdWHoW',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::kscdCOri7iIxrFPJ' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'api/asset-return/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\AssetAssignmentApiController@store',
        'controller' => 'App\\Http\\Controllers\\Api\\AssetAssignmentApiController@store',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::kscdCOri7iIxrFPJ',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::5SPuJk2BalcaSI0i' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'api/payroll/ssf-history',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'api',
          1 => 'auth:api',
          2 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Api\\EmployeePayrollApiController@ssfHistory',
        'controller' => 'App\\Http\\Controllers\\Api\\EmployeePayrollApiController@ssfHistory',
        'namespace' => NULL,
        'prefix' => 'api',
        'where' => 
        array (
        ),
        'as' => 'generated::5SPuJk2BalcaSI0i',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.request' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@showLinkRequestForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.request',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.email' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/email',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'controller' => 'App\\Http\\Controllers\\Auth\\ForgotPasswordController@sendResetLinkEmail',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.email',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.reset' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/reset/{token}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@showResetForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.reset',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.update' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/reset',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'controller' => 'App\\Http\\Controllers\\Auth\\ResetPasswordController@reset',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.update',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'password.confirm' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'password/confirm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@showConfirmForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@showConfirmForm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'password.confirm',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::3EQn8V0M8aZyZ9ut' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'password/confirm',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@confirm',
        'controller' => 'App\\Http\\Controllers\\Auth\\ConfirmPasswordController@confirm',
        'namespace' => 'App\\Http\\Controllers',
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::3EQn8V0M8aZyZ9ut',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::hf8jEnhf9udcSrOV' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '/',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:61:"function () {
    return \\redirect()->route(\'admin.login\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000e2a0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::hf8jEnhf9udcSrOV',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::zXNwDcpxpAVbb2OH' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'add-user',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\iclockController@addUsers',
        'controller' => 'App\\Http\\Controllers\\Web\\iclockController@addUsers',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::zXNwDcpxpAVbb2OH',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::k6GfsShDPKEnRFCb' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'remove-user/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\iclockController@removeUser',
        'controller' => 'App\\Http\\Controllers\\Web\\iclockController@removeUser',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::k6GfsShDPKEnRFCb',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::IaKwPJJh3C9bcgyu' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
        2 => 'POST',
        3 => 'PUT',
        4 => 'PATCH',
        5 => 'DELETE',
        6 => 'OPTIONS',
      ),
      'uri' => 'iclock/cdata',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\BioAttendanceController@handleDevice',
        'controller' => 'App\\Http\\Controllers\\Web\\BioAttendanceController@handleDevice',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::IaKwPJJh3C9bcgyu',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'privacy-policy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'privacy',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PrivacyPolicyController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\PrivacyPolicyController@index',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'privacy-policy',
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.login' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\AdminAuthController@showAdminLoginForm',
        'controller' => 'App\\Http\\Controllers\\Auth\\AdminAuthController@showAdminLoginForm',
        'as' => 'admin.login',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.login.process' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/login',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\AdminAuthController@login',
        'controller' => 'App\\Http\\Controllers\\Auth\\AdminAuthController@login',
        'as' => 'admin.login.process',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logout' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/logout',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Auth\\AdminAuthController@logout',
        'controller' => 'App\\Http\\Controllers\\Auth\\AdminAuthController@logout',
        'as' => 'admin.logout',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.dashboard' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/dashboard',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DashboardController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\DashboardController@index',
        'as' => 'admin.dashboard',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.showQR' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/showQR',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DashboardController@showQR',
        'controller' => 'App\\Http\\Controllers\\Web\\DashboardController@showQR',
        'as' => 'admin.showQR',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.store',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/{user}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/users/{user}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.users.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@toggleStatus',
        'as' => 'admin.users.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/users/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@delete',
        'as' => 'admin.users.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.users.change-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/users/change-password/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AdminController@changePassword',
        'controller' => 'App\\Http\\Controllers\\Web\\AdminController@changePassword',
        'as' => 'admin.users.change-password',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/company',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.index',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/company/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.create',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/company',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.store',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/company/{company}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.show',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/company/{company}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/company/{company}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.update',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.company.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/company/{company}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.company.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\CompanyController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\CompanyController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/branch',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.index',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/branch/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.create',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/branch',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.store',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/branch/{branch}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.show',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/branch/{branch}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/branch/{branch}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.update',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/branch/{branch}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.branch.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/branch/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@toggleStatus',
        'as' => 'admin.branch.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.branch.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/branch/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\BranchController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\BranchController@delete',
        'as' => 'admin.branch.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.app-settings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/app-settings/index',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AppSettingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AppSettingController@index',
        'as' => 'admin.app-settings.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.app-settings.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/app-settings/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AppSettingController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AppSettingController@toggleStatus',
        'as' => 'admin.app-settings.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/general-settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.index',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/general-settings/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.create',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/general-settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.store',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/general-settings/{general_setting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.show',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/general-settings/{general_setting}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/general-settings/{general_setting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.update',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/general-settings/{general_setting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.general-settings.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.general-settings.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/general-settings/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\GeneralSettingController@delete',
        'as' => 'admin.general-settings.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.feature.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/feature/index',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\FeatureController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\FeatureController@index',
        'as' => 'admin.feature.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.feature.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/feature/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\FeatureController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\FeatureController@toggleStatus',
        'as' => 'admin.feature.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.index',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.create',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/roles',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.store',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.show',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/{role}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.update',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/roles/{role}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'as' => 'admin.roles.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@toggleStatus',
        'as' => 'admin.roles.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@delete',
        'as' => 'admin.roles.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.roles.permission' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/roles/permissions/{roleId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@createPermission',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@createPermission',
        'as' => 'admin.roles.permission',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.role.assign-permissions' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/roles/assign-permissions/{roleId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
          4 => 'superAdmin',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\RoleController@assignPermissionToRole',
        'controller' => 'App\\Http\\Controllers\\Web\\RoleController@assignPermissionToRole',
        'as' => 'admin.role.assign-permissions',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee.log' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee/location-logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@logs',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@logs',
        'as' => 'admin.employee.log',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.index',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.create',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employees',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.store',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/{employee}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.show',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/{employee}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/employees/{employee}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.update',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/employees/{employee}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employees.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@toggleStatus',
        'as' => 'admin.employees.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.toggle-holiday-checkin' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/toggle-holiday-checkin/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@toggleHolidayCheckIn',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@toggleHolidayCheckIn',
        'as' => 'admin.employees.toggle-holiday-checkin',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@delete',
        'as' => 'admin.employees.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.change-workspace' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/change-workspace/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@changeWorkSpace',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@changeWorkSpace',
        'as' => 'admin.employees.change-workspace',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.getAllCompanyUsers' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/get-company-employee/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@getAllCompanyEmployeeDetail',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@getAllCompanyEmployeeDetail',
        'as' => 'admin.employees.getAllCompanyUsers',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.getAllBranchEmployees' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/get-branch-employee/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@getAllBranchEmployees',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@getAllBranchEmployees',
        'as' => 'admin.employees.getAllBranchEmployees',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.change-password' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employees/change-password/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@changePassword',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@changePassword',
        'as' => 'admin.employees.change-password',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.force-logout' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/force-logout/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@forceLogOutEmployee',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@forceLogOutEmployee',
        'as' => 'admin.employees.force-logout',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.getAllUsersByDepartmentId' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/get-all-employees/{departmentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@getAllEmployeeByDepartmentId',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@getAllEmployeeByDepartmentId',
        'as' => 'admin.employees.getAllUsersByDepartmentId',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.fetchByDepartment' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employees/fetch-employees-by-department',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@fetchEmployeesByDepartment',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@fetchEmployeesByDepartment',
        'as' => 'admin.employees.fetchByDepartment',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.getByDepartment' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employees/fetch-employees-by-departments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@fetchDepartmentEmployees',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@fetchDepartmentEmployees',
        'as' => 'admin.employees.getByDepartment',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.index',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.create',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/departments',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.store',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments/{department}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.show',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments/{department}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/departments/{department}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.update',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/departments/{department}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.departments.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@toggleStatus',
        'as' => 'admin.departments.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@delete',
        'as' => 'admin.departments.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.departments.getAllDepartmentsByBranchId' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/departments/get-All-Departments/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DepartmentController@getAllDepartmentsByBranchId',
        'controller' => 'App\\Http\\Controllers\\Web\\DepartmentController@getAllDepartmentsByBranchId',
        'as' => 'admin.departments.getAllDepartmentsByBranchId',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.index',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.create',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/posts',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.store',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.show',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts/{post}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/posts/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.update',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/posts/{post}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.posts.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@toggleStatus',
        'as' => 'admin.posts.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@delete',
        'as' => 'admin.posts.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.posts.getAllPostsByBranchId' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/posts/get-All-posts/{deptId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PostController@getAllPostsByBranchId',
        'controller' => 'App\\Http\\Controllers\\Web\\PostController@getAllPostsByBranchId',
        'as' => 'admin.posts.getAllPostsByBranchId',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/office-times',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.index',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/office-times/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.create',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/office-times',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.store',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/office-times/{office_time}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.show',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/office-times/{office_time}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/office-times/{office_time}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.update',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/office-times/{office_time}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.office-times.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/office-times/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@toggleStatus',
        'as' => 'admin.office-times.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.office-times.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/office-times/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\OfficeTimeController@delete',
        'as' => 'admin.office-times.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/routers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.index',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/routers/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.create',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/routers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.store',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/routers/{router}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.show',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/routers/{router}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/routers/{router}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.update',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/routers/{router}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.routers.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/routers/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@toggleStatus',
        'as' => 'admin.routers.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.routers.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/routers/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\RouterController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\RouterController@delete',
        'as' => 'admin.routers.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.import-csv.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays/import-csv',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@holidayImport',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@holidayImport',
        'as' => 'admin.holidays.import-csv.show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.import-csv.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/holidays/import-csv',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@importHolidays',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@importHolidays',
        'as' => 'admin.holidays.import-csv.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.index',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.create',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/holidays',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.store',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays/{holiday}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.show',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays/{holiday}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/holidays/{holiday}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.update',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/holidays/{holiday}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.holidays.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@toggleStatus',
        'as' => 'admin.holidays.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.holidays.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/holidays/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\HolidayController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\HolidayController@delete',
        'as' => 'admin.holidays.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.index',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.create',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leaves',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.store',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/{leaf}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.show',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/{leaf}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/leaves/{leaf}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.update',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/leaves/{leaf}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leaves.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@toggleStatus',
        'as' => 'admin.leaves.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.toggle-early-exit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/toggle-early-exit/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@toggleEarlyExit',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@toggleEarlyExit',
        'as' => 'admin.leaves.toggle-early-exit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@delete',
        'as' => 'admin.leaves.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/get-leave-types/{earlyExitStatus}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@getLeaveTypesBasedOnEarlyExitStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@getLeaveTypesBasedOnEarlyExitStatus',
        'as' => 'admin.',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.gender-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/get-gender-leave-types/{branchId}/{gender}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@getGenderSpecificLeaveTypes',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@getGenderSpecificLeaveTypes',
        'as' => 'admin.leaves.gender-data',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leaves.employee-data' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leaves/get-employee-leave-types/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@getEmployeeLeaveTypes',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveTypeController@getEmployeeLeaveTypes',
        'as' => 'admin.leaves.employee-data',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/static-page-contents',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.index',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/static-page-contents/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.create',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/static-page-contents',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.store',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/static-page-contents/{static_page_content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.show',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/static-page-contents/{static_page_content}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/static-page-contents/{static_page_content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.update',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/static-page-contents/{static_page_content}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.static-page-contents.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/static-page-contents/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@toggleStatus',
        'as' => 'admin.static-page-contents.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.static-page-contents.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/static-page-contents/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\StaticPageContentController@delete',
        'as' => 'admin.static-page-contents.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.nav-notifications' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/get-nav-notification',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@getNotificationForNavBar',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@getNotificationForNavBar',
        'as' => 'admin.nav-notifications',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.index',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.create',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/notifications',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.store',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/{notification}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.show',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/{notification}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/notifications/{notification}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.update',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/notifications/{notification}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notifications.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@toggleStatus',
        'as' => 'admin.notifications.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@delete',
        'as' => 'admin.notifications.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notifications.send-notification' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notifications/send-notification/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NotificationController@sendNotificationToAllCompanyUser',
        'controller' => 'App\\Http\\Controllers\\Web\\NotificationController@sendNotificationToAllCompanyUser',
        'as' => 'admin.notifications.send-notification',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.night_attendances.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/employees/night-attendance/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@updateNightAttendance',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@updateNightAttendance',
        'as' => 'admin.night_attendances.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendances',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendances/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/attendances',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.store',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendances/{attendance}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendances/{attendance}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/attendances/{attendance}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/attendances/{attendance}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.attendances.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.check-in' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/attendance/check-in/{companyId}/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@checkInEmployee',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@checkInEmployee',
        'as' => 'admin.employees.check-in',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.check-out' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/attendance/check-out/{companyId}/{userId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@checkOutEmployee',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@checkOutEmployee',
        'as' => 'admin.employees.check-out',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/attendance/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@delete',
        'as' => 'admin.attendance.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendances.change-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/attendance/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@changeAttendanceStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@changeAttendanceStatus',
        'as' => 'admin.attendances.change-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.dashboard.takeAttendance' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/attendance/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@dashboardAttendance',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@dashboardAttendance',
        'as' => 'admin.dashboard.takeAttendance',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-request',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@index',
        'as' => 'admin.leave-request.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-leave-request.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leave-request/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@storeLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@storeLeaveRequest',
        'as' => 'admin.employee-leave-request.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-request/show/{leaveId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@show',
        'as' => 'admin.leave-request.show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.update-status' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/leave-request/status-update/{leaveRequestId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@updateLeaveRequestStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@updateLeaveRequestStatus',
        'as' => 'admin.leave-request.update-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-request/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@createLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@createLeaveRequest',
        'as' => 'admin.leave-request.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.add' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-request/add',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@addLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@addLeaveRequest',
        'as' => 'admin.leave-request.add',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leave-request/add',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@saveLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@saveLeaveRequest',
        'as' => 'admin.leave-request.save',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.time-leave-request.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/time-leave-request',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@index',
        'as' => 'admin.time-leave-request.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.time-leave-request.update-status' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/time-leave-request/status-update/{leaveRequestId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@updateLeaveRequestStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@updateLeaveRequestStatus',
        'as' => 'admin.time-leave-request.update-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.time-leave-request.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/time-leave-request/show/{leaveId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@show',
        'as' => 'admin.time-leave-request.show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.time-leave-request.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/time-leave-request/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@createLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@createLeaveRequest',
        'as' => 'admin.time-leave-request.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.time-leave-request.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/time-leave-request/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@storeLeaveRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\TimeLeaveController@storeLeaveRequest',
        'as' => 'admin.time-leave-request.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logout-requests.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee/logout-requests',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeLogOutRequestController@getAllCompanyEmployeeLogOutRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeLogOutRequestController@getAllCompanyEmployeeLogOutRequest',
        'as' => 'admin.logout-requests.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.logout-requests.accept' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee/logout-requests/toggle-status/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeLogOutRequestController@acceptLogoutRequest',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeLogOutRequestController@acceptLogoutRequest',
        'as' => 'admin.logout-requests.accept',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.index',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.create',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/notices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.store',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices/{notice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.show',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices/{notice}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/notices/{notice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.update',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/notices/{notice}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.notices.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@toggleStatus',
        'as' => 'admin.notices.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@delete',
        'as' => 'admin.notices.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.notices.send-notice' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/notices/send-notice/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NoticeController@sendNotice',
        'controller' => 'App\\Http\\Controllers\\Web\\NoticeController@sendNotice',
        'as' => 'admin.notices.send-notice',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/team-meetings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/team-meetings/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/team-meetings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/team-meetings/{team_meeting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/team-meetings/{team_meeting}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/team-meetings/{team_meeting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/team-meetings/{team_meeting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.team-meetings.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/team-meetings/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@delete',
        'as' => 'admin.team-meetings.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.team-meetings.remove-image' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/team-meetings/remove-image/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@removeImage',
        'controller' => 'App\\Http\\Controllers\\Web\\TeamMeetingController@removeImage',
        'as' => 'admin.team-meetings.remove-image',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.ajax-store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients/ajax/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@ajaxClientStore',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@ajaxClientStore',
        'as' => 'admin.clients.ajax-store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.index',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.create',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/clients',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.store',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/{client}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.show',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/{client}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/clients/{client}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.update',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/clients/{client}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.clients.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@delete',
        'as' => 'admin.clients.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.clients.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/clients/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ClientController@toggleIsActiveStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\ClientController@toggleIsActiveStatus',
        'as' => 'admin.clients.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.index',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.create',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/projects',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.store',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/{project}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.show',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/{project}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/projects/{project}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.update',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/projects/{project}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.projects.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@delete',
        'as' => 'admin.projects.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@toggleStatus',
        'as' => 'admin.projects.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.get-assigned-members' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/get-assigned-members/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@getProjectAssignedMembersByProjectId',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@getProjectAssignedMembersByProjectId',
        'as' => 'admin.projects.get-assigned-members',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.add-employee' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/get-employees-to-add/{addEmployeeType}/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@getEmployeesToAddTpProject',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@getEmployeesToAddTpProject',
        'as' => 'admin.projects.add-employee',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.update-leader-data' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/projects/update-leaders',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@updateLeaderToProject',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@updateLeaderToProject',
        'as' => 'admin.projects.update-leader-data',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.projects.update-member-data' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/projects/update-members',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@updateMemberToProject',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@updateMemberToProject',
        'as' => 'admin.projects.update-member-data',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.project-attachment.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/attachment/create/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttachmentController@createProjectAttachment',
        'controller' => 'App\\Http\\Controllers\\Web\\AttachmentController@createProjectAttachment',
        'as' => 'admin.project-attachment.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.project-attachment.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/projects/attachment/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttachmentController@storeProjectAttachment',
        'controller' => 'App\\Http\\Controllers\\Web\\AttachmentController@storeProjectAttachment',
        'as' => 'admin.project-attachment.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-attachment.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/attachment/create/{taskId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttachmentController@createTaskAttachment',
        'controller' => 'App\\Http\\Controllers\\Web\\AttachmentController@createTaskAttachment',
        'as' => 'admin.task-attachment.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-attachment.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/tasks/attachment/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttachmentController@storeTaskAttachment',
        'controller' => 'App\\Http\\Controllers\\Web\\AttachmentController@storeTaskAttachment',
        'as' => 'admin.task-attachment.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attachment.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attachment/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttachmentController@deleteAttachmentById',
        'controller' => 'App\\Http\\Controllers\\Web\\AttachmentController@deleteAttachmentById',
        'as' => 'admin.attachment.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/tasks',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/{task}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/{task}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/tasks/{task}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/tasks/{task}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tasks.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.project-task.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/projects/task/create/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@createTaskFromProjectPage',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@createTaskFromProjectPage',
        'as' => 'admin.project-task.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@delete',
        'as' => 'admin.tasks.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@toggleStatus',
        'as' => 'admin.tasks.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employees.getAllTaskByProjectId' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tasks/get-all-tasks/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@getAllTaskByProjectId',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@getAllTaskByProjectId',
        'as' => 'admin.employees.getAllTaskByProjectId',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-checklists.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/task-checklists/save',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@store',
        'as' => 'admin.task-checklists.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-checklists.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/task-checklists/edit/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@edit',
        'as' => 'admin.task-checklists.edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-checklists.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/task-checklists/update/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@update',
        'as' => 'admin.task-checklists.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-checklists.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/task-checklists/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@delete',
        'as' => 'admin.task-checklists.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-checklists.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/task-checklists/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@toggleIsCompletedStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskChecklistController@toggleIsCompletedStatus',
        'as' => 'admin.task-checklists.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.task-comment.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/task-comment/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskCommentController@saveCommentDetail',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskCommentController@saveCommentDetail',
        'as' => 'admin.task-comment.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.comment.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/task-comment/delete/{commentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskCommentController@deleteComment',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskCommentController@deleteComment',
        'as' => 'admin.comment.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.reply.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/task-comment/reply/delete/{replyId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskCommentController@deleteReply',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskCommentController@deleteReply',
        'as' => 'admin.reply.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.supports.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/supports/get-all-query',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SupportController@getAllQueriesPaginated',
        'controller' => 'App\\Http\\Controllers\\Web\\SupportController@getAllQueriesPaginated',
        'as' => 'admin.supports.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.supports.changeSeenStatus' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/supports/change-seen-status/{queryId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SupportController@changeIsSeenStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\SupportController@changeIsSeenStatus',
        'as' => 'admin.supports.changeSeenStatus',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.supports.updateStatus' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/supports/update-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SupportController@changeQueryStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\SupportController@changeQueryStatus',
        'as' => 'admin.supports.updateStatus',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.supports.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/supports/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SupportController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\SupportController@delete',
        'as' => 'admin.supports.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.update-status' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/tadas/update-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@changeTadaStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@changeTadaStatus',
        'as' => 'admin.tadas.update-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/tadas',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/{tada}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/{tada}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/tadas/{tada}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/tadas/{tada}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.tadas.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@delete',
        'as' => 'admin.tadas.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/toggle-active-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TadaController@toggleTadaIsActive',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaController@toggleTadaIsActive',
        'as' => 'admin.tadas.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.attachment.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/attachment/create/{tadaId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TadaAttachmentController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaAttachmentController@create',
        'as' => 'admin.tadas.attachment.create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.attachment.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/tadas/attachment/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TadaAttachmentController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaAttachmentController@store',
        'as' => 'admin.tadas.attachment.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tadas.attachment-delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/tadas/attachment/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TadaAttachmentController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TadaAttachmentController@delete',
        'as' => 'admin.tadas.attachment-delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-type-export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-types-export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DataExportController@exportLeaveType',
        'controller' => 'App\\Http\\Controllers\\Web\\DataExportController@exportLeaveType',
        'as' => 'admin.leave-type-export',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request-export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-requests-export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DataExportController@exportEmployeeLeaveRequestLists',
        'controller' => 'App\\Http\\Controllers\\Web\\DataExportController@exportEmployeeLeaveRequestLists',
        'as' => 'admin.leave-request-export',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-lists-export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-detail-export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DataExportController@exportEmployeeDetail',
        'controller' => 'App\\Http\\Controllers\\Web\\DataExportController@exportEmployeeDetail',
        'as' => 'admin.employee-lists-export',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance-lists-export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendance-detail-export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DataExportController@exportAttendanceDetail',
        'controller' => 'App\\Http\\Controllers\\Web\\DataExportController@exportAttendanceDetail',
        'as' => 'admin.attendance-lists-export',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.asset-types.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-types/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.asset-types.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/asset-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.asset-types.store',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-types/{asset_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.asset-types.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-types/{asset_type}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.asset-types.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/asset-types/{asset_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.asset-types.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-types/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@delete',
        'as' => 'admin.asset-types.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-types.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-types/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetTypeController@toggleIsActiveStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetTypeController@toggleIsActiveStatus',
        'as' => 'admin.asset-types.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/assets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.assets.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/assets/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.assets.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/assets',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.assets.store',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/assets/{asset}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.assets.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/assets/{asset}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.assets.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/assets/{asset}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.assets.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/assets/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@delete',
        'as' => 'admin.assets.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.assets.change-Availability-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/assets/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@changeAvailabilityStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@changeAvailabilityStatus',
        'as' => 'admin.assets.change-Availability-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-components',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-components.index',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-components/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-components.create',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/salary-components',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-components.store',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-components/{salary_component}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-components.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/salary-components/{salary_component}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-components.update',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-components/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@delete',
        'as' => 'admin.salary-components.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-components.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-components/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@toggleSalaryComponentStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryComponentController@toggleSalaryComponentStatus',
        'as' => 'admin.salary-components.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-methods.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/payment-methods',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.payment-methods.index',
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-methods.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/payment-methods/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.payment-methods.create',
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-methods.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/payment-methods',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.payment-methods.store',
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-methods.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/payment-methods/{payment_method}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.payment-methods.update',
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-methods.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/payment-methods/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@deletePaymentMethod',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@deletePaymentMethod',
        'as' => 'admin.payment-methods.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-methods.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/payment-methods/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@togglePaymentMethodStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentMethodController@togglePaymentMethodStatus',
        'as' => 'admin.payment-methods.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-currency.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/payment-currency',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentCurrencyController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentCurrencyController@index',
        'as' => 'admin.payment-currency.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payment-currency.save' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/payment-currency',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PaymentCurrencyController@updateOrSetPaymentCurrency',
        'controller' => 'App\\Http\\Controllers\\Web\\PaymentCurrencyController@updateOrSetPaymentCurrency',
        'as' => 'admin.payment-currency.save',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-tds',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-tds.index',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-tds/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-tds.create',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/salary-tds',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-tds.store',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-tds/{salary_td}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-tds.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/salary-tds/{salary_td}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-tds.update',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-tds/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@deleteSalaryTDS',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@deleteSalaryTDS',
        'as' => 'admin.salary-tds.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-tds.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-tds/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@toggleSalaryTDSStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryTDSController@toggleSalaryTDSStatus',
        'as' => 'admin.salary-tds.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-groups',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-groups.index',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-groups/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-groups.create',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/salary-groups',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-groups.store',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-groups/{salary_group}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-groups.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/salary-groups/{salary_group}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.salary-groups.update',
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-groups/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@deleteSalaryGroup',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@deleteSalaryGroup',
        'as' => 'admin.salary-groups.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.salary-groups.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/salary-groups/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@toggleSalaryGroupStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryGroupController@toggleSalaryGroupStatus',
        'as' => 'admin.salary-groups.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.employee-salaries.index',
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.update-salary-cycle' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/update-cycle/{employeeId}/{cycle}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@changeSalaryCycle',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@changeSalaryCycle',
        'as' => 'admin.employee-salaries.update-salary-cycle',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.payroll-create' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employee-salaries/payroll-create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@payrollCreate',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@payrollCreate',
        'as' => 'admin.employee-salaries.payroll-create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salary.payroll' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/payroll',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@payroll',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@payroll',
        'as' => 'admin.employee-salary.payroll',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salary.payroll-detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/payroll/{payslipId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@viewPayroll',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@viewPayroll',
        'as' => 'admin.employee-salary.payroll-detail',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salary.payroll-print' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/payroll/{payslipId}/print',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@printPayslip',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@printPayslip',
        'as' => 'admin.employee-salary.payroll-print',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salary.payroll-edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/payroll/{payslipId}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@editPayroll',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@editPayroll',
        'as' => 'admin.employee-salary.payroll-edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salary.payroll-update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/employee-salaries/payroll/{payslipId}/update',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@updatePayroll',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@updatePayroll',
        'as' => 'admin.employee-salary.payroll-update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salary.payroll-delete' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/employee-salaries/payroll/{payslipId}/delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@deletePayroll',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@deletePayroll',
        'as' => 'admin.employee-salary.payroll-delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.add' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/salary/create/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@createSalary',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@createSalary',
        'as' => 'admin.employee-salaries.add',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.store-salary' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employee-salaries/salary/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@saveSalary',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@saveSalary',
        'as' => 'admin.employee-salaries.store-salary',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.edit-salary' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/salary/edit/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@editSalary',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@editSalary',
        'as' => 'admin.employee-salaries.edit-salary',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.update-salary' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/employee-salaries/salary/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@updateSalary',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@updateSalary',
        'as' => 'admin.employee-salaries.update-salary',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.delete-salary' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/salary/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@deleteSalary',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@deleteSalary',
        'as' => 'admin.employee-salaries.delete-salary',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.make_payment' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/employee-salaries/{payslipId}/make-payment',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@makePayment',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@makePayment',
        'as' => 'admin.employee-salaries.make_payment',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.get-weeks' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/getWeeks/{year}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@getWeeks',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@getWeeks',
        'as' => 'admin.employee-salaries.get-weeks',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.increase-salary' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/salary-update/{accountId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryHistoryController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryHistoryController@create',
        'as' => 'admin.employee-salaries.increase-salary',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.updated-salary-store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/employee-salaries/salary-history/store',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryHistoryController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryHistoryController@store',
        'as' => 'admin.employee-salaries.updated-salary-store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee-salaries.salary-revise-history.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/salary-increment-history/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\SalaryHistoryController@getEmployeeAllSalaryHistory',
        'controller' => 'App\\Http\\Controllers\\Web\\SalaryHistoryController@getEmployeeAllSalaryHistory',
        'as' => 'admin.employee-salaries.salary-revise-history.show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.setting' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/advance-salaries/setting',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@setting',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@setting',
        'as' => 'admin.advance-salaries.setting',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.setting.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/advance-salaries/setting/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@updateSetting',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@updateSetting',
        'as' => 'admin.advance-salaries.setting.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/advance-salaries',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.advance-salaries.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/advance-salaries/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.advance-salaries.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/advance-salaries/{advance_salary}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.advance-salaries.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/advance-salaries/{advance_salary}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.advance-salaries.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.advance-salaries.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/advance-salaries/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AdvanceSalaryController@delete',
        'as' => 'admin.advance-salaries.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payroll.tax-report.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/tax-report',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaxReportController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TaxReportController@index',
        'as' => 'admin.payroll.tax-report.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payroll.tax-report.detail' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/tax-report/{id}/detail',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaxReportController@taxReport',
        'controller' => 'App\\Http\\Controllers\\Web\\TaxReportController@taxReport',
        'as' => 'admin.payroll.tax-report.detail',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payroll.tax-report.print' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/tax-report/{id}/print',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaxReportController@printTaxReport',
        'controller' => 'App\\Http\\Controllers\\Web\\TaxReportController@printTaxReport',
        'as' => 'admin.payroll.tax-report.print',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payroll.tax-report.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/tax-report/{id}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaxReportController@editTaxReport',
        'controller' => 'App\\Http\\Controllers\\Web\\TaxReportController@editTaxReport',
        'as' => 'admin.payroll.tax-report.edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payroll.tax-report.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employee-salaries/tax-report/{id}/delete',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaxReportController@deleteTaxReport',
        'controller' => 'App\\Http\\Controllers\\Web\\TaxReportController@deleteTaxReport',
        'as' => 'admin.payroll.tax-report.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.payroll.tax-report.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/employee-salaries/tax-report/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaxReportController@updateTaxReport',
        'controller' => 'App\\Http\\Controllers\\Web\\TaxReportController@updateTaxReport',
        'as' => 'admin.payroll.tax-report.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.get-employee' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime/get-employee-by-payroll/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@getEmployeeSalaryData',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@getEmployeeSalaryData',
        'as' => 'admin.overtime.get-employee',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.overtime.index',
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.overtime.create',
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/overtime',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.overtime.store',
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime/{overtime}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.overtime.show',
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime/{overtime}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.overtime.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/overtime/{overtime}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.overtime.update',
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@delete',
        'as' => 'admin.overtime.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.overtime.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/overtime/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@toggleOTStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\OverTimeSettingController@toggleOTStatus',
        'as' => 'admin.overtime.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/under-time',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.under-time.index',
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/under-time/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.under-time.create',
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/under-time',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.under-time.store',
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/under-time/{under_time}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.under-time.show',
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/under-time/{under_time}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.under-time.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/under-time/{under_time}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.under-time.update',
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/under-time/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@delete',
        'as' => 'admin.under-time.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.under-time.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/under-time/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@toggleUTStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\UnderTimeSettingController@toggleUTStatus',
        'as' => 'admin.under-time.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/qr',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.qr.index',
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/qr/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.qr.create',
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/qr',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.qr.store',
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/qr/{qr}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.qr.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/qr/{qr}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.qr.update',
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/qr/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@delete',
        'as' => 'admin.qr.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.qr.print' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/qr/print/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\QrCodeController@print',
        'controller' => 'App\\Http\\Controllers\\Web\\QrCodeController@print',
        'as' => 'admin.qr.print',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.nfc.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/nfc',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NFCController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\NFCController@index',
        'as' => 'admin.nfc.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.nfc.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/nfc/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\NFCController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\NFCController@delete',
        'as' => 'admin.nfc.destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.employee_leave_type.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/employees/leave_type/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@deleteEmployeeLeaveType',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@deleteEmployeeLeaveType',
        'as' => 'admin.employee_leave_type.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/award-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.award-types.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/award-types/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.award-types.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/award-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.award-types.store',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/award-types/{award_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.award-types.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/award-types/{award_type}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.award-types.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/award-types/{award_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.award-types.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/award-types/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@delete',
        'as' => 'admin.award-types.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.award-types.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/award-types/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AwardTypeController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardTypeController@toggleStatus',
        'as' => 'admin.award-types.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.awards.index',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.awards.create',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/awards',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.awards.store',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/{award}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.awards.show',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/{award}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.awards.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/awards/{award}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.awards.update',
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.awards.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/awards/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@delete',
        'as' => 'admin.awards.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.language.change' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/language/change',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\LanguageController@change',
        'controller' => 'App\\Http\\Controllers\\LanguageController@change',
        'as' => 'admin.language.change',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/bonus',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.bonus.index',
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/bonus/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.bonus.create',
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/bonus',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.bonus.store',
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/bonus/{bonu}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.bonus.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/bonus/{bonu}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.bonus.update',
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/bonus/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@delete',
        'as' => 'admin.bonus.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.bonus.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/bonus/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@toggleBonusStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@toggleBonusStatus',
        'as' => 'admin.bonus.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fiscal_year',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.fiscal_year.index',
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fiscal_year/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.fiscal_year.create',
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/fiscal_year',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.fiscal_year.store',
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fiscal_year/{fiscal_year}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.fiscal_year.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/fiscal_year/{fiscal_year}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.fiscal_year.update',
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fiscal_year/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@delete',
        'as' => 'admin.fiscal_year.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.fiscal_year.toggle-current-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/fiscal_year/change-current-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\FiscalYearController@toggleCurrentStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\FiscalYearController@toggleCurrentStatus',
        'as' => 'admin.fiscal_year.toggle-current-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ssf.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ssf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.ssf.index',
        'uses' => 'App\\Http\\Controllers\\Web\\SSFController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\SSFController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ssf.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ssf/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.ssf.create',
        'uses' => 'App\\Http\\Controllers\\Web\\SSFController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\SSFController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ssf.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/ssf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.ssf.store',
        'uses' => 'App\\Http\\Controllers\\Web\\SSFController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\SSFController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ssf.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/ssf/{ssf}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.ssf.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\SSFController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\SSFController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.ssf.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/ssf/{ssf}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.ssf.update',
        'uses' => 'App\\Http\\Controllers\\Web\\SSFController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\SSFController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance.log' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendance/logs',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@logs',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@logs',
        'as' => 'admin.attendance.log',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.get-tax' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/calculate_tax',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@calculateTax',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@calculateTax',
        'as' => 'admin.get-tax',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.change-theme' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/change-theme',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ThemeController@changeTheme',
        'controller' => 'App\\Http\\Controllers\\Web\\ThemeController@changeTheme',
        'as' => 'admin.change-theme',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.fetchByRole' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval/get-employees-by-role',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@getEmployeesByRole',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@getEmployeesByRole',
        'as' => 'admin.leave-approval.fetchByRole',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leave-approval.index',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leave-approval.create',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/leave-approval',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leave-approval.store',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval/{leave_approval}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leave-approval.show',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval/{leave_approval}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leave-approval.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/leave-approval/{leave_approval}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.leave-approval.update',
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@delete',
        'as' => 'admin.leave-approval.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-approval.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-approval/change-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@toggleStatus',
        'as' => 'admin.leave-approval.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.leave-request.approval-details' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/leave-request/get-approvers/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveController@getLeaveRequestApproval',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveController@getLeaveRequestApproval',
        'as' => 'admin.leave-request.approval-details',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance.export' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendance/export',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceController@export',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceController@export',
        'as' => 'admin.attendance.export',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/event',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.index',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/event/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.create',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/event',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.store',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/event/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.show',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/event/{event}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/event/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.update',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.destroy' => 
    array (
      'methods' => 
      array (
        0 => 'DELETE',
      ),
      'uri' => 'admin/event/{event}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.event.destroy',
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@destroy',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@destroy',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/event/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@delete',
        'as' => 'admin.event.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.event.remove-image' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/event/remove-image/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EventController@removeImage',
        'controller' => 'App\\Http\\Controllers\\Web\\EventController@removeImage',
        'as' => 'admin.event.remove-image',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training-types.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/training-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training-types.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training-types/{training_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training-types.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/training-types/{training_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training-types.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training-types.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training-types/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@delete',
        'as' => 'admin.training-types.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training-types.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training-types/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingTypeController@toggleStatus',
        'as' => 'admin.training-types.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.trainers.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.trainers.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/trainers',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.trainers.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers/{trainer}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.trainers.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers/{trainer}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.trainers.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/trainers/{trainer}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.trainers.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@delete',
        'as' => 'admin.trainers.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@toggleStatus',
        'as' => 'admin.trainers.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.trainers.getAllTrainersByType' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/trainers/get-all-trainers/{type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainerController@getAllTrainersByType',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainerController@getAllTrainersByType',
        'as' => 'admin.trainers.getAllTrainersByType',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/training',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training/{training}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training/{training}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/training/{training}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.training.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@delete',
        'as' => 'admin.training.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.training.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/training/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@toggleStatus',
        'as' => 'admin.training.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination-types.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination-types.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination-types.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/termination-types',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination-types.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination-types.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination-types/{termination_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination-types.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination-types.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/termination-types/{termination_type}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination-types.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination-types.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination-types/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@delete',
        'as' => 'admin.termination-types.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination-types.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination-types/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationTypeController@toggleStatus',
        'as' => 'admin.termination-types.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/termination',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination/{termination}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination/{termination}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/termination/{termination}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.termination.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/termination/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@delete',
        'as' => 'admin.termination.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.termination.update-status' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/termination/status-update/{terminationId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@updateTerminationStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@updateTerminationStatus',
        'as' => 'admin.termination.update-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/resignation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.resignation.index',
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/resignation/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.resignation.create',
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/resignation',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.resignation.store',
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/resignation/{resignation}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.resignation.show',
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/resignation/{resignation}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.resignation.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/resignation/{resignation}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.resignation.update',
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/resignation/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@delete',
        'as' => 'admin.resignation.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.resignation.update-status' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/resignation/status-update/{resignationId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ResignationController@updateResignationStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\ResignationController@updateResignationStatus',
        'as' => 'admin.resignation.update-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/warning',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.warning.index',
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/warning/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.warning.create',
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/warning',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.warning.store',
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/warning/{warning}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.warning.show',
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/warning/{warning}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.warning.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/warning/{warning}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.warning.update',
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.warning.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/warning/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\WarningController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\WarningController@delete',
        'as' => 'admin.warning.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/complaint',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.complaint.index',
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/complaint/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.complaint.create',
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/complaint',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.complaint.store',
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/complaint/{complaint}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.complaint.show',
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/complaint/{complaint}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.complaint.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/complaint/{complaint}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.complaint.update',
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.complaint.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/complaint/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ComplaintController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\ComplaintController@delete',
        'as' => 'admin.complaint.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.add-employee' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/task/get-employees-to-add/{taskId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@getEmployeesToAddToTask',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@getEmployeesToAddToTask',
        'as' => 'admin.tasks.add-employee',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.tasks.update-member-data' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/task/update-members',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@updateTaskMember',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@updateTaskMember',
        'as' => 'admin.tasks.update-member-data',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/promotion',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.promotion.index',
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/promotion/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.promotion.create',
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/promotion',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.promotion.store',
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/promotion/{promotion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.promotion.show',
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/promotion/{promotion}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.promotion.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/promotion/{promotion}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.promotion.update',
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/promotion/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@delete',
        'as' => 'admin.promotion.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.promotion.getUsersAndPostByDepartment' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/promotion/get-employees-posts/{departmentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\PromotionController@getEmployeeAndPostByDepartment',
        'controller' => 'App\\Http\\Controllers\\Web\\PromotionController@getEmployeeAndPostByDepartment',
        'as' => 'admin.promotion.getUsersAndPostByDepartment',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.transfer.index',
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.transfer.create',
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/transfer',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.transfer.store',
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.show' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/{transfer}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.transfer.show',
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@show',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@show',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/{transfer}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.transfer.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/transfer/{transfer}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.transfer.update',
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.transfer.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@delete',
        'as' => 'admin.transfer.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::0djg0GN3kLjQ1398' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/get-user-transfer-department-data/{departmentId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@getUserTransferDepartmentData',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@getUserTransferDepartmentData',
        'as' => 'admin.generated::0djg0GN3kLjQ1398',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::2iP1jdZd30XCbI2n' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/get-user-transfer-branch-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@getUserTransferBranchData',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@getUserTransferBranchData',
        'as' => 'admin.generated::2iP1jdZd30XCbI2n',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::UmsoPupS70eno53q' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/transfer/get-user-data/{employeeId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TransferController@getUserTransferData',
        'controller' => 'App\\Http\\Controllers\\Web\\TransferController@getUserTransferData',
        'as' => 'admin.generated::UmsoPupS70eno53q',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.theme-color-setting.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/theme-color-setting',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.theme-color-setting.index',
        'uses' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.theme-color-setting.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/theme-color-setting/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.theme-color-setting.create',
        'uses' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.theme-color-setting.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/theme-color-setting',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.theme-color-setting.store',
        'uses' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.theme-color-setting.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/theme-color-setting/{theme_color_setting}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.theme-color-setting.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.theme-color-setting.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/theme-color-setting/{theme_color_setting}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.theme-color-setting.update',
        'uses' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\ThemeSettingController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::4K1bACRiAHiz9iCa' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-project-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\ProjectController@getBranchProjectData',
        'controller' => 'App\\Http\\Controllers\\Web\\ProjectController@getBranchProjectData',
        'as' => 'admin.generated::4K1bACRiAHiz9iCa',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::Cf9tdFyOYEjffsQc' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-employee-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\UserController@getBranchEmployeeData',
        'controller' => 'App\\Http\\Controllers\\Web\\UserController@getBranchEmployeeData',
        'as' => 'admin.generated::Cf9tdFyOYEjffsQc',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::Jz7yxoIlcflNHpMn' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-asset-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetController@getBranchAssetData',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetController@getBranchAssetData',
        'as' => 'admin.generated::Jz7yxoIlcflNHpMn',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::4Wpu31LaGR2gW7J1' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-leave-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@getBranchLeaveData',
        'controller' => 'App\\Http\\Controllers\\Web\\LeaveApprovalController@getBranchLeaveData',
        'as' => 'admin.generated::4Wpu31LaGR2gW7J1',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::4Y8o0UHDbBAk9HEr' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-termination-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TerminationController@getBranchTerminationData',
        'controller' => 'App\\Http\\Controllers\\Web\\TerminationController@getBranchTerminationData',
        'as' => 'admin.generated::4Y8o0UHDbBAk9HEr',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::iBVJZcjk3YDPH4DJ' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-award-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AwardController@getBranchAwardData',
        'controller' => 'App\\Http\\Controllers\\Web\\AwardController@getBranchAwardData',
        'as' => 'admin.generated::iBVJZcjk3YDPH4DJ',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::pdCQaEQrDGRHhaHY' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-task-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@getBranchTaskData',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@getBranchTaskData',
        'as' => 'admin.generated::pdCQaEQrDGRHhaHY',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::lXmEUFND67qxgitz' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-project-member-data/{projectId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TaskController@getProjectMemberData',
        'controller' => 'App\\Http\\Controllers\\Web\\TaskController@getProjectMemberData',
        'as' => 'admin.generated::lXmEUFND67qxgitz',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::wlL5L4VZ1LlQL7TY' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/get-branch-training-data/{branchId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\TrainingController@getBranchTrainingData',
        'controller' => 'App\\Http\\Controllers\\Web\\TrainingController@getBranchTrainingData',
        'as' => 'admin.generated::wlL5L4VZ1LlQL7TY',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-assignment.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/asset-assignment/{assetId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@store',
        'as' => 'admin.asset-assignment.store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-assignment.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-assignments/{assetId}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@index',
        'as' => 'admin.asset-assignment.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset-return.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset-return',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@returnlist',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@returnlist',
        'as' => 'admin.asset-return.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset.toggle-repair-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/asset/toggle-repair-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@changeRepairStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@changeRepairStatus',
        'as' => 'admin.asset.toggle-repair-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.asset.return' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/asset-return/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@storeReturn',
        'controller' => 'App\\Http\\Controllers\\Web\\AssetAssignmentController@storeReturn',
        'as' => 'admin.asset.return',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::uPuhbvQpJaoLd5xU' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/overtime/get-user-department-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@getEmployeeByDepartmentPayrollType',
        'controller' => 'App\\Http\\Controllers\\Web\\EmployeeSalaryController@getEmployeeByDepartmentPayrollType',
        'as' => 'admin.generated::uPuhbvQpJaoLd5xU',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::jPXqLbXVpglvjrti' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/bonus/get-user-department-data',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\BonusController@getUserDepartmentData',
        'controller' => 'App\\Http\\Controllers\\Web\\BonusController@getUserDepartmentData',
        'as' => 'admin.generated::jPXqLbXVpglvjrti',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pf.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.pf.index',
        'uses' => 'App\\Http\\Controllers\\Web\\PFController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\PFController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pf.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pf/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.pf.create',
        'uses' => 'App\\Http\\Controllers\\Web\\PFController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\PFController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pf.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/pf',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.pf.store',
        'uses' => 'App\\Http\\Controllers\\Web\\PFController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\PFController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pf.edit' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/pf/{pf}/edit',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.pf.edit',
        'uses' => 'App\\Http\\Controllers\\Web\\PFController@edit',
        'controller' => 'App\\Http\\Controllers\\Web\\PFController@edit',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.pf.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/pf/{pf}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.pf.update',
        'uses' => 'App\\Http\\Controllers\\Web\\PFController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\PFController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.biometric-devices.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/biometric-devices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.biometric-devices.index',
        'uses' => 'App\\Http\\Controllers\\Web\\DeviceController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\DeviceController@index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.biometric-devices.create' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/biometric-devices/create',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.biometric-devices.create',
        'uses' => 'App\\Http\\Controllers\\Web\\DeviceController@create',
        'controller' => 'App\\Http\\Controllers\\Web\\DeviceController@create',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.biometric-devices.store' => 
    array (
      'methods' => 
      array (
        0 => 'POST',
      ),
      'uri' => 'admin/biometric-devices',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.biometric-devices.store',
        'uses' => 'App\\Http\\Controllers\\Web\\DeviceController@store',
        'controller' => 'App\\Http\\Controllers\\Web\\DeviceController@store',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.biometric-devices.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
        1 => 'PATCH',
      ),
      'uri' => 'admin/biometric-devices/{biometric_device}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'as' => 'admin.biometric-devices.update',
        'uses' => 'App\\Http\\Controllers\\Web\\DeviceController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\DeviceController@update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.biometric-devices.delete' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/biometric-devices/delete/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\DeviceController@delete',
        'controller' => 'App\\Http\\Controllers\\Web\\DeviceController@delete',
        'as' => 'admin.biometric-devices.delete',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.generated::e42fIBSRkEXuD5K7' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/log',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:451:"static function () {
            $log = \\base_path(\'storage/logs/laravel.log\');
            if (\\file_exists($log)) {
                $content = \\file($log);
                $lines = \\array_slice($content, -500); // show last 100 lines
                $output = \\implode("", $lines);
                return \\response($output, 200)->header(\'Content-Type\', \'text/plain\');
            }

            return \\response(\'Log file not found\', 404);
        }";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"00000000000010940000000000000000";}}',
        'as' => 'admin.generated::e42fIBSRkEXuD5K7',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance-settings.index' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendance-settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceSettingController@index',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceSettingController@index',
        'as' => 'admin.attendance-settings.index',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance-settings.update' => 
    array (
      'methods' => 
      array (
        0 => 'PUT',
      ),
      'uri' => 'admin/attendance-settings',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceSettingController@update',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceSettingController@update',
        'as' => 'admin.attendance-settings.update',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'admin.attendance-settings.toggle-status' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => 'admin/attendance-settings/toggle-status/{id}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
          1 => 'web',
          2 => 'admin.auth',
          3 => 'permission',
        ),
        'uses' => 'App\\Http\\Controllers\\Web\\AttendanceSettingController@toggleStatus',
        'controller' => 'App\\Http\\Controllers\\Web\\AttendanceSettingController@toggleStatus',
        'as' => 'admin.attendance-settings.toggle-status',
        'namespace' => NULL,
        'prefix' => '/admin',
        'where' => 
        array (
        ),
      ),
      'fallback' => false,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
    'generated::ySFdF52WLugeoUvd' => 
    array (
      'methods' => 
      array (
        0 => 'GET',
        1 => 'HEAD',
      ),
      'uri' => '{fallbackPlaceholder}',
      'action' => 
      array (
        'middleware' => 
        array (
          0 => 'web',
        ),
        'uses' => 'O:55:"Laravel\\SerializableClosure\\UnsignedSerializableClosure":1:{s:12:"serializable";O:46:"Laravel\\SerializableClosure\\Serializers\\Native":5:{s:3:"use";a:0:{}s:8:"function";s:46:"function() {
    return \\view(\'errors.404\');
}";s:5:"scope";s:37:"Illuminate\\Routing\\RouteFileRegistrar";s:4:"this";N;s:4:"self";s:32:"0000000000000e7e0000000000000000";}}',
        'namespace' => NULL,
        'prefix' => '',
        'where' => 
        array (
        ),
        'as' => 'generated::ySFdF52WLugeoUvd',
      ),
      'fallback' => true,
      'defaults' => 
      array (
      ),
      'wheres' => 
      array (
        'fallbackPlaceholder' => '.*',
      ),
      'bindingFields' => 
      array (
      ),
      'lockSeconds' => NULL,
      'waitSeconds' => NULL,
      'withTrashed' => false,
    ),
  ),
)
);
