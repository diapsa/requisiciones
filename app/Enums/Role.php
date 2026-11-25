<?php

namespace App\Enums;

/**
 * Roles created by RolePermissionSeeder (see docs/reglas-de-negocio.md#roles).
 */
enum Role: string
{
    case Requester = 'requester';
    case Coordinator = 'coordinator';
    case Manager = 'manager';
    case Admin = 'admin';

    /**
     * Name shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Requester => 'Solicitante',
            self::Coordinator => 'Coordinador',
            self::Manager => 'Gerente',
            self::Admin => 'Administrador',
        };
    }

    /**
     * What the role does in the authorization chain (see docs/reglas-de-negocio.md#roles).
     */
    public function description(): string
    {
        return match ($this) {
            self::Requester => 'Captura requisiciones, registra el depósito que recibe y comprueba sus gastos.',
            self::Coordinator => 'Da la primera autorización a las requisiciones y comprobaciones de su equipo.',
            self::Manager => 'Da la autorización final, revisa comprobaciones y cierra saldos.',
            self::Admin => 'Puede hacer todo, incluido administrar usuarios y catálogos.',
        };
    }

    /**
     * Options in authorization-chain order: [['value' => 'requester', 'label' => 'Solicitante', 'description' => '...'], ...].
     *
     * @return list<array{value: string, label: string, description: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $role): array => ['value' => $role->value, 'label' => $role->label(), 'description' => $role->description()],
            self::cases(),
        );
    }
}
