<?php

namespace App\Accounting\Helpers;

/**
 * Maps journal entry reference_type values to human-readable Arabic labels,
 * icons, and route URLs.
 *
 * Used in:
 *  - Journal Book view
 *  - General Ledger view
 *  - Journal Book Excel export
 *  - General Ledger Excel export
 *
 * Adding a new posting type? Add one entry to $map below.
 */
class ReferenceTypeMapper
{
    /**
     * Central mapping table.
     *
     * Each entry:
     *   'label'   => Arabic label shown to user
     *   'icon'    => Font Awesome class (for Blade)
     *   'color'   => Tailwind badge colour key (badge-{color})
     *   'route'   => Laravel route name to link to the record (null = no link)
     *   'param'   => Route parameter name (default 'id')
     */
    private static array $map = [
        // ─── Revenue / Customer ───────────────────────────────────────────────
        'order' => [
            'label'  => 'طلب توصيل',
            'icon'   => 'fa-truck',
            'color'  => 'blue',
            'route'  => 'orders.show',
            'param'  => 'order',
        ],
        'customer_payment' => [
            'label'  => 'دفعة عميل',
            'icon'   => 'fa-hand-holding-usd',
            'color'  => 'green',
            'route'  => 'customer-payments.show',
            'param'  => 'customer_payment',
        ],

        // ─── Purchasing / Supplier ────────────────────────────────────────────
        'supplier_purchase' => [
            'label'  => 'شراء من مورد',
            'icon'   => 'fa-shopping-cart',
            'color'  => 'orange',
            'route'  => 'supplier-purchases.show',
            'param'  => 'supplier_purchase',
        ],
        'supplier_payment' => [
            'label'  => 'دفعة مورد',
            'icon'   => 'fa-money-bill-wave',
            'color'  => 'red',
            'route'  => 'supplier-payments.show',
            'param'  => 'supplier_payment',
        ],

        // ─── Operating Expenses ───────────────────────────────────────────────
        'expense' => [
            'label'  => 'مصروف تشغيلي',
            'icon'   => 'fa-file-invoice-dollar',
            'color'  => 'yellow',
            'route'  => 'expenses.show',
            'param'  => 'expense',
        ],
        'land_rent_payment' => [
            'label'  => 'دفعة إيجار أرض',
            'icon'   => 'fa-map-marker-alt',
            'color'  => 'yellow',
            'route'  => null,
            'param'  => 'id',
        ],

        // ─── HR / Payroll ─────────────────────────────────────────────────────
        'payroll' => [
            'label'  => 'رواتب وأجور',
            'icon'   => 'fa-users',
            'color'  => 'purple',
            'route'  => 'payroll.show',
            'param'  => 'payroll',
        ],
        'employee_borrow' => [
            'label'  => 'سلفة موظف',
            'icon'   => 'fa-user-minus',
            'color'  => 'pink',
            'route'  => null,
            'param'  => 'id',
        ],

        // ─── Capital / Equity ─────────────────────────────────────────────────
        'contributor_payment' => [
            'label'  => 'دفعة مساهم',
            'icon'   => 'fa-user-tie',
            'color'  => 'indigo',
            'route'  => null,
            'param'  => 'id',
        ],

        // ─── Equipment / Fleet ────────────────────────────────────────────────
        'fuel_log' => [
            'label'  => 'سجل وقود',
            'icon'   => 'fa-gas-pump',
            'color'  => 'gray',
            'route'  => null,
            'param'  => 'id',
        ],
        'equipment_maintenance' => [
            'label'  => 'صيانة معدة',
            'icon'   => 'fa-wrench',
            'color'  => 'gray',
            'route'  => null,
            'param'  => 'id',
        ],
        'rental_shift' => [
            'label'  => 'وردية إيجار',
            'icon'   => 'fa-calendar-alt',
            'color'  => 'teal',
            'route'  => null,
            'param'  => 'id',
        ],
        'rental_maintenance' => [
            'label'  => 'صيانة معدة مستأجرة',
            'icon'   => 'fa-tools',
            'color'  => 'teal',
            'route'  => null,
            'param'  => 'id',
        ],

        // ─── Neighboring Stations ─────────────────────────────────────────────
        'neighboring_station_transaction' => [
            'label'  => 'معاملة محطة مجاورة',
            'icon'   => 'fa-industry',
            'color'  => 'cyan',
            'route'  => 'neighboring-stations.index',
            'param'  => 'id',
        ],
    ];

    // ─── Public API ──────────────────────────────────────────────────────────────

    /**
     * Get the Arabic label for a reference_type.
     * Handles _reversal suffixes automatically.
     *
     * @param  string|null $referenceType
     * @return string
     */
    public static function label(?string $referenceType): string
    {
        if (!$referenceType) {
            return 'قيد يدوي';
        }

        // Strip _reversal suffix
        $base = self::stripReversal($referenceType);
        $isReversal = $base !== $referenceType;

        $label = self::$map[$base]['label'] ?? self::humanize($referenceType);

        return $isReversal ? 'عكسي: ' . $label : $label;
    }

    /**
     * Get the Font Awesome icon class for a reference_type.
     */
    public static function icon(?string $referenceType): string
    {
        if (!$referenceType) {
            return 'fa-pencil-alt';
        }
        $base = self::stripReversal($referenceType);
        return self::$map[$base]['icon'] ?? 'fa-link';
    }

    /**
     * Get the badge colour key for a reference_type.
     */
    public static function color(?string $referenceType): string
    {
        if (!$referenceType) {
            return 'gray';
        }
        $base = self::stripReversal($referenceType);
        return self::$map[$base]['color'] ?? 'gray';
    }

    /**
     * Generate a full formatted string for Excel / plain-text display.
     * Example: "طلب توصيل #5"
     */
    public static function format(?string $referenceType, ?int $referenceId): string
    {
        $label = self::label($referenceType);
        $id    = $referenceId ? ' #' . $referenceId : '';
        return $label . $id;
    }

    /**
     * Generate a URL to the referenced business record (returns null if no route defined).
     */
    public static function url(?string $referenceType, ?int $referenceId): ?string
    {
        if (!$referenceType || !$referenceId) {
            return null;
        }

        $base  = self::stripReversal($referenceType);
        $entry = self::$map[$base] ?? null;

        if (!$entry || !$entry['route']) {
            return null;
        }

        try {
            return route($entry['route'], [$entry['param'] => $referenceId]);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Returns the full entry for a reference type (for advanced use).
     */
    public static function entry(?string $referenceType): array
    {
        if (!$referenceType) {
            return ['label' => 'قيد يدوي', 'icon' => 'fa-pencil-alt', 'color' => 'gray', 'route' => null, 'param' => 'id'];
        }
        $base = self::stripReversal($referenceType);
        return self::$map[$base] ?? ['label' => self::humanize($referenceType), 'icon' => 'fa-link', 'color' => 'gray', 'route' => null, 'param' => 'id'];
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────────

    private static function stripReversal(string $type): string
    {
        return str_ends_with($type, '_reversal')
            ? substr($type, 0, -strlen('_reversal'))
            : $type;
    }

    /**
     * Fallback: convert snake_case to Title Case when no mapping exists.
     */
    private static function humanize(string $type): string
    {
        return ucwords(str_replace('_', ' ', $type));
    }
}
