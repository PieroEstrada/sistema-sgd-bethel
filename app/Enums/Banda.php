<?php

namespace App\Enums;

enum Banda: string
{
    case FM = 'FM';
    case AM = 'AM';
    case VHF = 'VHF';
    case UHF = 'UHF';

    public function getLabel(): string
    {
        return match($this) {
            self::FM => 'FM',
            self::AM => 'AM',
            self::VHF => 'VHF (TV)',
            self::UHF => 'UHF (TV)',
        };
    }

    public function label(): string
    {
        return $this->getLabel();
    }

    public function getDescription(): string
    {
        return match($this) {
            self::FM => 'Frecuencia Modulada (88.1 - 107.9 MHz)',
            self::AM => 'Amplitud Modulada (530 - 1700 KHz)',
            self::VHF => 'Very High Frequency TV (Canales 2-13)',
            self::UHF => 'Ultra High Frequency TV (Canales 14-69)',
        };
    }

    public function getRangoFrecuencia(): array
    {
        return match($this) {
            self::FM => ['min' => 88.1, 'max' => 107.9, 'unidad' => 'MHz'],
            self::AM => ['min' => 530, 'max' => 1700, 'unidad' => 'KHz'],
            self::VHF => ['min' => 2, 'max' => 13, 'unidad' => 'Canal'],
            self::UHF => ['min' => 14, 'max' => 69, 'unidad' => 'Canal'],
        };
    }

    public function esTv(): bool
    {
        return in_array($this, [self::VHF, self::UHF]);
    }

    public function esRadio(): bool
    {
        return in_array($this, [self::FM, self::AM]);
    }

    public function getIcon(): string
    {
        return match($this) {
            self::FM => 'fas fa-radio',
            self::AM => 'fas fa-radio',
            self::VHF => 'fas fa-tv',
            self::UHF => 'fas fa-tv',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::FM => 'primary',
            self::AM => 'info',
            self::VHF => 'success',
            self::UHF => 'warning',
        };
    }

    public static function getOptions(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'description' => $case->getDescription(),
            'color' => $case->getColor(),
            'icon' => $case->getIcon(),
            'tipo' => $case->esTv() ? 'TV' : 'Radio'
        ], self::cases());
    }

    public static function getRadioOptions(): array
    {
        return array_filter(self::getOptions(), fn($option) => !in_array($option['value'], ['VHF', 'UHF']));
    }

    public static function getTvOptions(): array
    {
        return array_filter(self::getOptions(), fn($option) => in_array($option['value'], ['VHF', 'UHF']));
    }
}