<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\Tokens\TokenResult;

/**
 * Centrální definice PHPStan type aliasů pro celou aplikaci.
 *
 * Tento soubor neobsahuje žádný spustitelný kód — slouží pouze
 * jako zdroj @phpstan-type definic, které se importují pomocí
 * @phpstan-import-type v ostatních třídách.
 */

/**
 * @phpstan-type MenuRouteRow array{
 *     method: string,
 *     path: string,
 *     action: array{class-string, string},
 *     auth?: bool,
 *     title?: string,
 *     menu?: string,
 *     submenu?: string,
 *     section?: string,
 *     roles?: array<int, string>,
 *     regex?: string
 * }
 *
 * @phpstan-type MenuItem array{
 *     label: string,
 *     path: string,
 *     active: bool
 * }
 *
 * @phpstan-type MenuSection array{
 *     label: string,
 *     path: string,
 *     method: string,
 *     active: bool,
 *     items: array<int, MenuItem>
 * }
 *
 * @phpstan-type TaskStats array{
 *     assignments_count: int,
 *     total_minutes: int,
 *     total_km: int,
 *     workers_count: int
 * }
 *
 * @phpstan-type TaskListRow array{
 *     id: int,
 *     company_id: int,
 *     team_id: int,
 *     work_order_id: int,
 *     due_date: ?string,
 *     task_type: string,
 *     recurring_task_id: ?int,
 *     generated_date: ?string,
 *     title: string,
 *     description: ?string,
 *     status: string,
 *     created_by_user_id: int,
 *     created_at: string,
 *     done_at: ?string,
 *     work_order_title: string,
 *     work_order_status: string,
 *     work_order_priority: string,
 *     team_name: string,
 *     team_color: string,
 *     reports_count: int,
 *     total_minutes: int,
 *     total_km: string,
 *     can_add_report: bool,
 *     total_hours_formatted: string
 * }
 *
 * @phpstan-type TaskDetailRow array{
 *     id: int,
 *     company_id: int,
 *     team_id: int,
 *     work_order_id: int,
 *     due_date: ?string,
 *     task_type: string,
 *     recurring_task_id: ?int,
 *     generated_date: ?string,
 *     title: string,
 *     description: ?string,
 *     status: string,
 *     created_by_user_id: int,
 *     created_at: string,
 *     done_at: ?string,
 *     is_generated_task: int,
 *     recurring_master_id: ?int,
 *     recurring_frequency_type: ?string,
 *     recurring_frequency_value: ?int,
 *     recurring_next_due_date: ?string,
 *     recurring_warning_days_before: ?int,
 *     recurring_active: ?int,
 *     stats: TaskStats,
 *     can_cancel: bool,
 *     can_close: bool,
 *     team_name: string,
 *     team_color: string,
 * }
 *
 * @phpstan-type TaskBaseRow array{
 *   id: int,
 *   company_id: int,
 *   team_id: int,
 *   work_order_id: int,
 *   due_date: ?string,
 *   task_type: string,
 *   recurring_task_id: ?int,
 *   generated_date: ?string,
 *   title: string,
 *   description: ?string,
 *   status: string,
 *   created_by_user_id: int,
 *   created_at: string,
 *   done_at: ?string,
 *   is_generated_task: int,
 *   recurring_master_id: ?int,
 *   recurring_frequency_type: ?string,
 *   recurring_frequency_value: ?int,
 *   recurring_next_due_date: ?string,
 *   recurring_warning_days_before: ?int,
 *   recurring_active: ?int,
 *   count_instances?: int,
 *   canUserAddReport?: bool

 * }
 * 
 * @phpstan-type RecurringTaskRow array{
 *   id: int,
 *   company_id: int,
 *   team_id: int,
 *   work_order_id: int,
 *   due_date: ?string,
 *   task_type: string,
 *   recurring_task_id: ?int,
 *   generated_date: ?string,
 *   title: string,
 *   description: ?string,
 *   status: string,
 *   created_by_user_id: int,
 *   created_at: string,
 *   done_at: ?string,
 *   is_generated_task: int,
 *   recurring_master_id: ?int,
 *   recurring_frequency_type: ?string,
 *   recurring_frequency_value: ?int,
 *   recurring_next_due_date: ?string,
 *   recurring_warning_days_before: ?int,
 *   recurring_active: ?int,
 *   count_instances: int
 * }
 *
 * @phpstan-type WorkOrderRow array{
 *     id: int,
 *     company_id: int,
 *     parent_id: ?int,
 *     external_number: ?string,
 *     title: string,
 *     description: string,
 *     wo_due_date: ?string,
 *     source: string,
 *     requested_by: string,
 *     contact_person: string,
 *     priority: string,
 *     estimated_hours: ?float,
 *     status: string,
 *     is_system: int,
 *     created_by_user_id: int,
 *     created_at: string,
 *     closed_at: ?string,
 *     internal_number: int,
 *     contact_id: ?int,
 *     price_per_hour: int,
 *     price_per_km: int,
 *     year: int,
 *     customer_name: string,
 *     customer_city: ?string,
 *     customer_street: ?string,
 *     tasks_total: int,
 *     tasks_open: string,
 *     tasks_done: string,
 *     tasks_cancelled: string,
 *     reports_count: int,
 *     total_km: string,
 *     total_minutes: int,
 *     total_hours_formatted: string,
 *     progress: float,
 *     customer_address: string
 * }
 *
 * @phpstan-type WorkOrderDetailRow array{
 *  id: int,
 *  company_id: int,
 *  parent_id: ?int,
 *  external_number: ?string,
 *  title: string,
 *  description: string,
 *  wo_due_date: ?string,
 *  source: string,
 *  requested_by: string,
 *  contact_person: string,
 *  priority: string,
 *  estimated_hours: ?string,
 *  status: string,
 *  is_system: int,
 *  created_by_user_id: int,
 *  created_at: string,
 *  closed_at: ?string,
 *  internal_number: int,
 *  contact_id: ?int,
 *  price_per_hour: int,
 *  price_per_km: int,
 *  year: int,
 *  total_time: string,
 *  total_km: int,
 *  report_count: int,
 *  total_tasks_count: int,
 *  open_tasks_count: int,
 *  done_tasks_count: int,
 *  cancelled_tasks_count: int,
 *  company_name: string,
 *  ready_for_done: bool,
 *  ready_for_cancel: bool
 * }
 * 
 * @phpstan-type WorkbenchTaskRow array{
 *     id: int,
 *     title: string,
 *     status: string,
 *     due_date: ?string,
 *     team_id: int,
 *     work_order_id: int,
 *     created_at: string,
 *     recurring_task_id: ?int,
 *     task_type: string,
 *     work_order_title: string,
 *     work_order_priority: string,
 *     team_name: string,
 *     team_color: string,
 *     assignments_count?: int
 * }
 *
 * @phpstan-type WorkbenchOrderRow array{
 *     id: int,
 *     company_id: int,
 *     parent_id: ?int,
 *     external_number: ?string,
 *     title: string,
 *     description: string,
 *     wo_due_date: ?string,
 *     source: string,
 *     requested_by: string,
 *     contact_person: string,
 *     priority: string,
 *     estimated_hours: ?string,
 *     status: string,
 *     is_system: int,
 *     created_by_user_id: int,
 *     created_at: string,
 *     closed_at: ?string,
 *     internal_number: int,
 *     contact_id: ?int,
 *     price_per_hour: int,
 *     price_per_km: int,
 *     year: int,
 *     tasks?: array<int, WorkbenchTaskRow>,
 *     can_be_done?: bool,
 *     can_be_cancelled?: bool
 * }
 * 
 * @phpstan-type WorkOrderBaseRow array{
 *   id: int,
 *   company_id: int,
 *   parent_id: ?int,
 *   external_number: ?string,
 *   title: string,
 *   description: string,
 *   wo_due_date: ?string,
 *   source: string,
 *   requested_by: string,
 *   contact_person: string,
 *   priority: string,
 *   estimated_hours: ?string,
 *   status: string,
 *   is_system: int,
 *   created_by_user_id: int,
 *   created_at: string,
 *   closed_at: ?string,
 *   internal_number: int,
 *   contact_id: ?int,
 *   price_per_hour: int,
 *   price_per_km: int,
 *   year: int
 * }
 * 
 * @phpstan-type ContactRow array{
 *     id: int,
 *     company_id: int,
 *     company_name: string,
 *     official_name: string,
 *     ico: ?string,
 *     dic: ?string,
 *     street: ?string,
 *     house_number: ?string,
 *     orientation_number: ?string,
 *     city_part: ?string,
 *     city: ?string,
 *     postal_code: ?string,
 *     country_code: string,
 *     delivery_address_1: ?string,
 *     delivery_address_2: ?string,
 *     delivery_address_3: ?string,
 *     email: ?string,
 *     phone: ?string,
 *     bank_account: ?string,
 *     bank_code: ?string,
 *     notes: ?string,
 *     created_at: string,
 *     updated_at: string,
 *     contact?: string
 * }
 * 
 * @phpstan-type UserRow array{
 *     id: int,
 *     company_id: int,
 *     email: string,
 *     employee_number: string,
 *     telefon: string,
 *     password_hash: string,
 *     first_name: string,
 *     last_name: string,
 *     global_role: string,
 *     domain_admin: int,
 *     active: int,
 *     created_at: string,
 *     session_version: int
 * }
 *
 * @phpstan-type TeamRow array{
 *     id: int,
 *     company_id: int,
 *     name: string,
 *     color: string,
 *     active: int,
 *     created_at: string
 * }
 *
 * @phpstan-type TeamMemberRow array{
 *  membership_id: int,
 *  id: int,
 *  first_name: string,
 *  last_name: string,
 *  role_in_team: string
 * }
 * 
 * @phpstan-type TeamDetailRow array{
 *   id: int,
 *   company_id: int,
 *   name: string,
 *   color: string,
 *   active: int,
 *   created_at: string,
 *   members: array<int, TeamMemberRow>,
 *   members_count: int
 * }
 *
 * @phpstan-type SessionUserRow array{
 *     id: int,
 *     email: string,
 *     global_role: string,
 *     company_id: int,
 *     company_name: string,
 *     tenant_slug: string,
 *     first_name: string,
 *     last_name: string,
 *     db_name: string,
 *     session_version: int
 * }
 *
 * @phpstan-type ArchiveTaskRow array{
 *     id: int,
 *     company_id: int,
 *     team_id: int,
 *     work_order_id: int,
 *     due_date: ?string,
 *     task_type: string,
 *     recurring_task_id: ?int,
 *     generated_date: ?string,
 *     title: string,
 *     description: ?string,
 *     status: string,
 *     created_by_user_id: int,
 *     created_at: string,
 *     done_at: ?string,
 *     allReportsParticipants: array<int, array<string, mixed>>,
 *     WOStatus: string,
 *     totalKm: int
 * }
 *
 * @phpstan-type ArchiveOrderRow array{
 *     id: int,
 *     company_id: int,
 *     parent_id: ?int,
 *     external_number: ?string,
 *     title: string,
 *     description: string,
 *     wo_due_date: ?string,
 *     source: string,
 *     requested_by: string,
 *     contact_person: string,
 *     priority: string,
 *     estimated_hours: ?float,
 *     status: string,
 *     is_system: int,
 *     created_by_user_id: int,
 *     created_at: string,
 *     closed_at: ?string,
 *     internal_number: int,
 *     contact_id: ?int,
 *     price_per_hour: int,
 *     price_per_km: int,
 *     year: int
 * }
 *
 * @phpstan-type ArchiveTaskListRow array{
 *     id: int,
 *     company_id: int,
 *     team_id: int,
 *     work_order_id: int,
 *     due_date: ?string,
 *     task_type: string,
 *     recurring_task_id: ?int,
 *     generated_date: ?string,
 *     title: string,
 *     description: ?string,
 *     status: string,
 *     created_by_user_id: int,
 *     created_at: string,
 *     done_at: ?string,
 *     work_order_title: string,
 *     work_order_status: string,
 *     team_name: string,
 *     team_color: string
 * }
 * 
 * @phpstan-type WorkbenchInvoiceTaskRow array{
 *   id: int,
 *   title: string,
 *   done_at: ?string,
 *   work_order_id: int,
 *   work_order_title: string,
 *   team_name: string,
 *   total_minutes: string,
 *   total_kilometers: string
 * }
 * 
 *
 * @phpstan-type WorkbenchDataRow array{
 *     myTeams: array<int, TeamRow>,
 *     otherTeams: array<int, TeamRow>,
 *     myTeamTasks: array<int, WorkbenchTaskRow>,
 *     otherTeamTasks: array<int, WorkbenchTaskRow>,
 *     myReadyToDoneTasks: array<int, WorkbenchTaskRow>,
 *     otherReadyToDoneTasks: array<int, WorkbenchTaskRow>,
 *     myOrdersInProgress: array<int, WorkbenchOrderRow>,
 *     otherOrdersInProgress: array<int, WorkbenchOrderRow>,
 *     myReadyToCancelTasks: array<int, WorkbenchTaskRow>,
 *     myInvoiceToReady: array<int, WorkbenchInvoiceTaskRow>,
 *     otherInvoiceToReady: array<int, WorkbenchInvoiceTaskRow>,
 *     myReadyToDoneOrders?: array<int, WorkbenchOrderRow>,
 *     myReadyToCancelOrders?: array<int, WorkbenchOrderRow>,
 *     otherReadyToDoneOrders?: array<int, WorkbenchOrderRow>,
 *     otherReadyToCancelOrders?: array<int, WorkbenchOrderRow>,
 *     isInternalBilling?: bool,
 *     isExternalAccounting?: bool
 * }
 * 
 * @phpstan-type WorkOrderCreateData array{
 *     contact_id: int,
 *     price_per_hour: int,
 *     price_per_km: int,
 *     external_number: ?string,
 *     title: string,
 *     description: string,
 *     source: string,
 *     requested_by: string,
 *     contact_person: string,
 *     priority: string,
 *     created_by_user_id: int,
 *     wo_due_date: ?string,
 *     estimated_hours: ?int,
 *     internal_number?: int,
 *     year?: int
 * }
 *
 *
 * @phpstan-type TokenData array{
 *     id: int,
 *     email: string,
 *     user_id: ?int
 * }
 *
 * @phpstan-type TokenConsumeSuccess array{
 *     ok: true,
 *     result: TokenResult,
 *     data: TokenData
 * }
 *
 * @phpstan-type TokenConsumeError array{
 *     ok: false,
 *     result: TokenResult,
 *     data: array{}
 * }
 *
 * @phpstan-type TokenConsumeResult TokenConsumeSuccess|TokenConsumeError
 * 
 * @phpstan-type OnboardingResult array{
 *     ok: bool,
 *     result: string,
 *     data: array{
 *         company_id: int,
 *         db_name: string,
 *         user_id: int,
 *         team_id: int,
 *         email: string,
 *         first_name: string,
 *         last_name: string,
 *         company_name: string,
 *         slug: string,
 *         session_version: int,
 *         global_role: string
 *     }
 * }
 * 
 * @phpstan-type CompanyRegistrationResult array{
 *     ok: true,
 *     data: array{
 *         user_id: int,
 *         email: string,
 *         company_id: int,
 *         company_name: string,
 *         slug: string,
 *         first_name: string,
 *         last_name: string,
 *         global_role: string,
 *         db_name: string,
 *         team_id: int,
 *         session_version: int
 *     }
 * }|array{
 *     ok: false
 * }
 * 
 * @phpstan-type AuditLogRow array{
 *     id: int,
 *     company_id: ?int,
 *     user_id: ?int,
 *     user_email: ?string,
 *     action: string,
 *     entity: string,
 *     entity_id: ?int,
 *     diff: ?string,
 *     ip_address: ?string,
 *     user_agent: ?string,
 *     created_at: string
 * }
 * 
 * @phpstan-type InternalInvoiceRow array{
 *     id: int,
 *     company_id: int,
 *     invoice_number: string,
 *     work_order_id: ?int,
 *     contact_id: ?int,
 *     customer_name: string,
 *     issued_at: string,
 *     due_date: string,
 *     status: string,
 *     invoice_json: string,
 *     created_by: int,
 *     created_at: string
 * }
 * 
 *
 * @phpstan-type CompanyDetailsRow array{
 *     company_id: int,
 *     official_name: string,
 *     trade_name: ?string,
 *     dic: ?string,
 *     street: ?string,
 *     house_number: ?string,
 *     orientation_number: ?string,
 *     city_part: ?string,
 *     city: string,
 *     postal_code: string,
 *     country_code: string,
 *     delivery_address_1: ?string,
 *     delivery_address_2: ?string,
 *     delivery_address_3: ?string,
 *     legal_form_code: ?string,
 *     legal_form_ros_code: ?string,
 *     founded_at: ?string,
 *     ares_updated_at: ?string,
 *     created_at: string,
 *     updated_at: string
 * }
 *
 *
 * @phpstan-type BankAccountRow array{
 *     id: int,
 *     company_id: int,
 *     name: string,
 *     account_prefix: ?string,
 *     account_number: string,
 *     bank_code: ?string,
 *     iban: ?string,
 *     bic: ?string,
 *     is_default: int,
 *     active: int,
 *     created_at: string,
 *     updated_at: string,
 *     default_company_key: ?int
 * }
 *
 */
final class Types
{
}