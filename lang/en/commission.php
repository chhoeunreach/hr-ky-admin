<?php
return [
    'title'=>'Commission Settings','help'=>'Manage one global commission policy for every service and mobile role.',
    'reports'=>'Sales Reports','global'=>'Global controls','enabled'=>'Enable commission earning on new transactions',
    'visible'=>'Show commission in the mobile app','calculator_enabled'=>'Allow report commission calculations',
    'allow_mode_switch'=>'Allow permitted roles to change calculation mode','default_mode'=>'Default calculation mode',
    'with_conditions'=>'With conditions','without_conditions'=>'Without conditions','service_rules'=>'Rules by service',
    'conditions_help'=>'Conditions apply across the selected report period. Phone numbers require more than 6 characters. Set a minimum to 0 to remove that condition.',
    'service'=>'Service','enabled_short'=>'Enabled','rate'=>'Rate','basis'=>'Calculate per','require_phone'=>'Require phone',
    'minimum_amount'=>'Minimum invoice amount','part_time_quantity'=>'Part-time minimum quantity','full_time_quantity'=>'Full-time minimum quantity',
    'item'=>'Item','invoice'=>'Invoice','role_controls'=>'Mobile role controls','role'=>'Role','view'=>'Show commission',
    'calculate'=>'Allow calculation','change_mode'=>'Allow mode change',
    'roles_help'=>'Hidden commission is removed from API responses and mobile exports. Without calculation access, a role sees saved invoice commissions only. These controls do not grant payroll or cross-employee access.',
    'history_help'=>'Saved invoice and payroll commissions stay unchanged. New transactions use the new rules; permitted report previews use current rules.',
    'save'=>'Save commission settings','saved'=>'Commission settings saved.','recent_changes'=>'Recent changes',
    'revision'=>'Revision','actor'=>'Changed by','date'=>'Date','no_changes'=>'No changes yet.',
    'types'=>['sell'=>'Sell','material'=>'Material','iron'=>'Iron','repair'=>'Repair','buy_in'=>'Buy In','icloud_cus'=>'iCloud Cus'],
];
