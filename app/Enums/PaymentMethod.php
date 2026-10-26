<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The payment methods at checkout; orders keep the value as a string. Bank
 * transfer is for partners with a transfer contract only, and the in-store
 * methods for personal pickup only (the client's wish, 2026-10-26).
 */
enum PaymentMethod: string implements HasLabel
{
    case BankTransfer = 'bacs';
    case CashOnDelivery = 'cod';
    case CardInStore = 'card_in_store';
    case CashInStore = 'cash_in_store';

    public function getLabel(): string
    {
        return match ($this) {
            self::BankTransfer => 'Banki átutalás',
            self::CashOnDelivery => 'Utánvét',
            self::CardInStore => 'Bankkártya (üzletünkben)',
            self::CashInStore => 'Készpénz (üzletünkben)',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::BankTransfer => 'Fizetés közvetlenül a bankszámlánkra. A megjegyzés rovatban tüntesse fel a rendelésszámot. A kiszállítás az összeg beérkezését követően történik.',
            self::CashOnDelivery => 'Fizetés a csomag átvételekor készpénzben vagy bankkártyával a futárnak.',
            self::CardInStore => 'Fizetés bankkártyával a rendelés átvételekor, X. kerületi üzletünkben.',
            self::CashInStore => 'Fizetés készpénzben a rendelés átvételekor, X. kerületi üzletünkben.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BankTransfer => 'fa-university',
            self::CashOnDelivery => 'fa-money-bill-wave',
            self::CardInStore => 'fa-credit-card',
            self::CashInStore => 'fa-coins',
        };
    }

    /**
     * Shown under the label even when the method cannot be chosen, so the
     * customer knows why.
     */
    public function restrictionNote(): ?string
    {
        return match ($this) {
            self::BankTransfer => 'Kizárólag átutalásos szerződéssel rendelkező partnereink részére.',
            self::CardInStore, self::CashInStore => 'Csak személyes átvétel esetén.',
            default => null,
        };
    }

    public function isPaidInStore(): bool
    {
        return in_array($this, [self::CardInStore, self::CashInStore], true);
    }
}
