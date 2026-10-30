<?php
declare(strict_types=1);
/** které tabulky budou podrobovány auditu */
return [
    'enabled' => true,

    'auditables' => [
        'access_logs',
        'bans',
        'companies',
        'company_bank_accounts',
        'company_details',
        'contacts',
        'email_rate_limits',
        'internal_invoices',
        'internal_invoice_items',
        'internal_invoice_lines',
        'recurring_tasks',
        'settings',
        'system_jobs',
        'tasks',
        'task_assignments',
        'task_assignment_participants',
        'teams',
        'team_memberships',
        'tokens',        
        'users',
        'work_orders',
        'work_order_sequences',
        
    ],

    'ignores' => [
        'audit_logs',
    ],
];

