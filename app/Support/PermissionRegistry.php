<?php

declare(strict_types=1);

namespace App\Support;

/**
 * সব permission ও role-এর একমাত্র উৎস (single source of truth)।
 *
 * `php artisan permissions:sync` reads this and reconciles the database, so
 * permissions are code-reviewed and versioned rather than hand-edited in prod.
 *
 * Naming convention: module.entity.action
 */
final class PermissionRegistry
{
    /**
     * @return array<string, list<string>> module => permissions
     */
    public static function permissions(): array
    {
        return [
            'academic' => array_merge(
                self::crud('academic.session', ['lock']),
                self::crud('academic.marhala'),
                self::crud('academic.jamaat'),
                self::crud('academic.section'),
                self::crud('academic.kitab'),
                self::crud('academic.curriculum'),
            ),

            'people' => array_merge(
                self::crud('people.student', ['import', 'export', 'promote']),
                self::crud('people.guardian'),
                self::crud('people.employee'),
                self::crud('people.admission', ['approve', 'enroll']),
                self::crud('people.id_card', ['print']),
            ),

            'attendance' => array_merge(
                self::crud('attendance.student', ['report']),
                self::crud('attendance.employee', ['report']),
                self::crud('attendance.holiday'),
            ),

            'exam' => array_merge(
                self::crud('exam.term'),
                self::crud('exam.schedule'),
                self::crud('exam.mark', ['entry']),
                self::crud('exam.result', ['calculate', 'publish', 'print']),
                self::crud('exam.grade_scale'),
            ),

            'hifz' => array_merge(
                self::crud('hifz.enrollment'),
                self::crud('hifz.daily_entry'),
                self::crud('hifz.milestone'),
                self::crud('hifz.report', ['print']),
            ),

            'finance' => array_merge(
                self::crud('finance.fee_head'),
                self::crud('finance.fee_structure'),
                self::crud('finance.invoice', ['generate']),
                self::crud('finance.payment', ['cancel', 'receipt']),
                self::crud('finance.transaction'),
                self::crud('finance.ledger'),
                self::crud('finance.report', ['export']),
            ),

            'donation' => array_merge(
                self::crud('donation.khat'),
                self::crud('donation.donor', ['statement']),
                self::crud('donation.collector'),
                self::crud('donation.receipt_book'),
                self::crud('donation.entry', ['cancel', 'receipt']),
                self::crud('donation.report', ['export']),
            ),

            'boarding' => array_merge(
                self::crud('boarding.building'),
                self::crud('boarding.room'),
                self::crud('boarding.seat', ['allocate']),
                self::crud('boarding.meal_plan'),
                self::crud('boarding.khana_bill', ['generate']),
                self::crud('boarding.visit'),
                self::crud('boarding.leave', ['approve']),
            ),

            'cms' => array_merge(
                self::crud('cms.site_setting'),
                self::crud('cms.page'),
                self::crud('cms.slider'),
                self::crud('cms.notice'),
                self::crud('cms.gallery'),
                self::crud('cms.teacher_profile'),
                self::crud('cms.menu'),
                self::crud('cms.contact_message'),
            ),

            'system' => array_merge(
                self::crud('system.user', ['impersonate']),
                self::crud('system.role'),
                self::crud('system.setting'),
                self::crud('system.activity_log'),
                self::crud('system.backup', ['run']),
            ),
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        $flat = [];

        foreach (self::permissions() as $group) {
            foreach ($group as $permission) {
                $flat[] = $permission;
            }
        }

        return array_values(array_unique($flat));
    }

    /**
     * রোল → permission ম্যাপিং।
     *
     * '*' = এই রোল সব permission পাবে।
     * 'prefix.*' = ঐ prefix-এর সব permission।
     *
     * @return array<string, array{name: string, permissions: list<string>}>
     */
    public static function roles(): array
    {
        return [
            'madrasa_admin' => [
                'name' => 'মাদরাসা অ্যাডমিন',
                'permissions' => ['*'],
            ],
            'muhtamim' => [
                'name' => 'মুহতামিম',
                // Everything except user management and destructive settings;
                // full *view* of finance plus approvals.
                'permissions' => [
                    'academic.*', 'people.*', 'attendance.*', 'exam.*', 'hifz.*',
                    'boarding.*', 'cms.*', 'donation.*',
                    'finance.fee_head.view', 'finance.fee_structure.view',
                    'finance.invoice.view', 'finance.payment.view',
                    'finance.transaction.view', 'finance.ledger.view',
                    'finance.report.view', 'finance.report.export',
                    'system.activity_log.view', 'system.setting.view',
                ],
            ],
            'shikkha_sochib' => [
                'name' => 'শিক্ষা সচিব',
                'permissions' => [
                    'academic.*', 'people.*', 'attendance.*', 'exam.*', 'hifz.*',
                ],
            ],
            'hisab_rokkhok' => [
                'name' => 'হিসাবরক্ষক',
                'permissions' => [
                    'finance.*', 'donation.*',
                    'boarding.khana_bill.view', 'boarding.khana_bill.generate',
                    'people.student.view',
                ],
            ],
            'ustad' => [
                'name' => 'উস্তাদ / শিক্ষক',
                // Row-level scoping (own sections / own kitabs / own hifz
                // students) is enforced by Policies on top of these.
                'permissions' => [
                    'attendance.student.view', 'attendance.student.create', 'attendance.student.update',
                    'exam.mark.view', 'exam.mark.entry', 'exam.mark.create', 'exam.mark.update',
                    'hifz.daily_entry.view', 'hifz.daily_entry.create', 'hifz.daily_entry.update',
                    'hifz.enrollment.view', 'hifz.report.view',
                    'people.student.view', 'academic.curriculum.view',
                    'exam.result.view',
                ],
            ],
            'nazeme_darul_iqama' => [
                'name' => 'নাযেমে দারুল ইকামা',
                'permissions' => [
                    'boarding.*',
                    'people.student.view',
                    'attendance.student.view',
                ],
            ],
            'adaykari' => [
                'name' => 'আদায়কারী (মুহাসসিল)',
                // Restricted further by DonationPolicy to their own receipt book.
                'permissions' => [
                    'donation.entry.view', 'donation.entry.create', 'donation.entry.receipt',
                    'donation.donor.view', 'donation.donor.create',
                    'donation.report.view',
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $extra
     * @return list<string>
     */
    private static function crud(string $entity, array $extra = []): array
    {
        $actions = ['view', 'create', 'update', 'delete', ...$extra];

        return array_map(static fn (string $action): string => "{$entity}.{$action}", $actions);
    }
}
