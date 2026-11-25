# Requisiciones — Documentación

Sistema interno de DIAPSA para gestionar las **requisiciones de viáticos** (solicitud de anticipo) y su **comprobación de gastos**, que hasta ahora se manejan en formatos de Excel.

## Índice

| Documento                                                       | Contenido                                                             |
| --------------------------------------------------------------- | --------------------------------------------------------------------- |
| [Análisis de los formatos de Excel](analisis-formatos-excel.md) | Qué contiene cada formato actual y los problemas que tienen           |
| [Base de datos](base-de-datos.md)                               | Convenciones, diagrama relacional y diccionario de datos              |
| [Reglas de negocio](reglas-de-negocio.md)                       | Flujo, estados, fórmulas de cálculo, políticas y puntos por confirmar |

La instalación, los comandos, la integración continua y el flujo de ramas y commits están en el [README principal](../README.md).

## Proceso que cubre el sistema

1. El solicitante captura la **requisición**: proyecto, fechas del servicio y los conceptos que necesita (gasolina, casetas, comidas, hospedaje, materiales…).
2. El **coordinador** la autoriza.
3. El **gerente** la autoriza.
4. El gerente **deposita el anticipo** fuera del sistema (puede haber más de un depósito).
5. El solicitante **registra el depósito que recibió**, con su comprobante; queda registrado en las autorizaciones.
6. Al regresar, el solicitante captura la **comprobación**: cada factura o ticket con su categoría.
7. Se revisa la comprobación y se compara lo **autorizado contra lo gastado** por tipo de gasto.
8. Se **cierra** con el saldo: DIAPSA reembolsa la diferencia, el empleado regresa el sobrante o se descuenta de nómina.

## Estado actual

- Estructura base generada con Laravel 13 y el starter kit de React (Inertia, TypeScript, Tailwind, Fortify).
- Base de datos `diapsa_requisitions` en MySQL con todas las tablas del [diagrama](base-de-datos.md), aprobado por el encargado senior.
- Roles y permisos (`requester`, `coordinator`, `manager`, `admin`) sin registro público; ver [reglas de negocio](reglas-de-negocio.md#roles).

## Decisiones tomadas

| Tema                       | Decisión                                                                                              |
| -------------------------- | ----------------------------------------------------------------------------------------------------- |
| Stack                      | Laravel 13 + Inertia 3 + React + TypeScript + Tailwind CSS 4, igual que los demás proyectos de DIAPSA |
| Base de datos              | MySQL; nombre `diapsa_requisitions`                                                                   |
| Idioma de la base de datos | Tablas, campos y valores de estados en **inglés**                                                     |
| Idioma de la interfaz      | **Español** (pantallas, mensajes, PDF)                                                                |
| Pruebas                    | PHPUnit, como en `idap` y `komply`                                                                    |
| Ramas                      | Rama de trabajo → `develop` → `master`                                                                |
| Commits                    | En inglés, formato `type(scope): description` (`feat`, `fix`, `chore`, `docs`…)                       |

> El repositorio todavía es público. Esta documentación no incluye nombres de empleados, clientes ni datos bancarios reales, y así debe mantenerse.
