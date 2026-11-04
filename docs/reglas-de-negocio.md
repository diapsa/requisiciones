# Reglas de negocio

Reglas tomadas de los formatos de Excel actuales. Las marcadas como **por confirmar** tienen datos contradictorios en los archivos y deben validarse con administración antes de programarlas.

## Estados

### Requisición (`requisitions.status`)

| Valor | Significado |
|---|---|
| `draft` | Borrador; el solicitante la está llenando |
| `submitted` | Enviada; espera autorizaciones |
| `approved` | Autorizada por coordinador y administración |
| `rejected` | Rechazada |
| `advance_paid` | Administración registró el depósito del anticipo |
| `advance_received` | El solicitante confirmó que recibió el depósito |
| `closed` | Comprobada y con saldo liquidado |
| `cancelled` | Cancelada |

Mientras está en `submitted`, la etapa en la que va (coordinador o administración) se sabe por los registros de `approvals` (ver [autorizaciones](#autorizaciones-approvals)). Si alguien la devuelve (`returned`), regresa a `draft` para corregirla.

### Comprobación (`expense_reports.status`)

`draft` → `submitted` → `approved` o `rejected`.

### Autorizaciones (`approvals`)

| Etapa (`stage`) | Quién la registra | Cuándo | Decisiones (`decision`) |
|---|---|---|---|
| `coordinator` | Coordinador del solicitante | Al enviarse la requisición | `approved`, `rejected` o `returned` |
| `administration` | Administración | Después del coordinador | `approved`, `rejected` o `returned` |
| `deposit_confirmation` | **El solicitante** | Cuando administración registra un depósito | `confirmed` o `not_received` |

La confirmación del depósito equivale al "Pago recibido" del Excel. Se registra por cada depósito (`approvable_type` = pago), porque una requisición puede tener más de uno. Al confirmar el primero, la requisición pasa a `advance_received`; si marca `not_received`, administración debe revisarlo antes de continuar.

## Tipos de gasto

Catálogo inicial de `expense_types`. Cada concepto de la requisición corresponde a una columna de la comprobación, por eso es un solo catálogo.

| `code` | Nombre | Concepto en la requisición | Columna en la comprobación | `calculation_method` | Unidad |
|---|---|---|---|---|---|
| `fuel` | Combustibles y lubricantes | Traslado | Combustibles y lubricantes | `fuel_by_distance` | CARGA |
| `tolls` | Casetas | Peajes | Caseta | `fixed` | TICKET |
| `meals` | Comida | Alimentos (comida y cena) | Comida | `per_person_meals` | CONSUMO |
| `breakfast` | Desayuno (despensa) | Desayunos / despensa | Desayuno (despensa) | `breakfast_reference` | DESAYUNO |
| `lodging` | Hospedaje | Hospedaje / Airbnb | Hospedaje | `per_night` | NOCHE |
| `materials` | Materiales | Materiales | Materiales | `fixed` | ÚNICO |
| `airfare` | Boletos de avión | — | Boletos de avión | `fixed` | ÚNICO |
| `bus_fare` | Boletos de autobús | — | Boleto de autobús | `fixed` | ÚNICO |
| `taxi` | Taxis | — | Taxis | `fixed` | ÚNICO |
| `parking` | Estacionamiento | — | Estacionamiento | `fixed` | ÚNICO |
| `office_supplies` | Papelería | — | Papelería | `fixed` | ÚNICO |
| `courier` | Mensajería | — | Mensajería | `fixed` | ÚNICO |
| `other` | Otros | Renglones libres | Otros | `fixed` | ÚNICO |

## Cálculo de cada partida

### `fuel_by_distance` (traslado)

```
litros_consumidos = distance_km ÷ km_per_liter
unit_price        = litros_consumidos × fuel_price_per_liter
amount            = unit_price × quantity        (quantity = número de viajes)
```

Ejemplo: 650 km, 15 km/L, $25.00 por litro, 1 viaje → 43.33 L → $1,083.33.

Solo informativos: km por tanque (`tank_capacity_liters × km_per_liter`) y tanques estimados (`litros_consumidos ÷ tank_capacity_liters`).

### `per_person_meals` (alimentos)

```
quantity = days × meals_per_day
amount   = people_count × unit_price × quantity
```

Ejemplo: 2 personas, 3 días, 2 comidas al día, $170 → 2 × $170 × 6 = $2,040.

### `breakfast_reference` (desayunos y despensa) — por confirmar

El Excel trae una tabla de referencia:

| Días | 2 personas | 4 personas |
|---|---|---|
| 7 | $660.20 | $1,320.40 |
| 9 | $990.30 | $1,980.60 |

Los archivos usan fórmulas distintas: una toma la referencia de 7 días ÷ 7 × días del viaje; otra toma la de 9 días × días ÷ 7. Además, los montos de 9 días no son proporcionales a los de 7 ($47.16 contra $55.02 por persona al día). Falta definir la regla correcta.

### `per_night` (hospedaje)

```
amount = unit_price × quantity        (quantity = noches)
```

### `fixed` (casetas, materiales y otros)

```
amount = unit_price × quantity
```

### Total de la requisición

`approved_amount` = suma de `requisition_items.amount`. Se fija al autorizarse; los cambios posteriores de tarifas no la modifican.

## Comprobación

- Cada factura o ticket se registra con su tipo de gasto.
- Por cada tipo de gasto se compara:
  - **Presupuesto**: suma de lo pedido en la requisición.
  - **Gastado**: suma de lo comprobado.
  - **Disponible**: presupuesto − gastado. Si es negativo, se excedió.
- Una requisición puede comprobarse en varias partes (`expense_reports.sequence`).
- Una factura con el mismo `cfdi_uuid` no puede registrarse dos veces.

## Saldo y cierre

```
saldo = anticipos (payments.type = advance) − total comprobado
```

| Saldo | Qué pasa | Registro en `payments` |
|---|---|---|
| Positivo | El empleado regresa el sobrante, o se le descuenta de nómina o del siguiente anticipo | `refund` o `payroll_deduction` |
| Negativo | DIAPSA le reembolsa la diferencia | `reimbursement` |
| Cero | Se cierra la requisición | — |

## Políticas (compromisos del solicitante)

### Topes por comida

| Caso | Tope |
|---|---|
| Comida o cena **con factura**, por persona | **Por confirmar**: $150 en un archivo, $170 en otro. Si se excede, se justifica |
| Comida o cena **sin factura**, por persona | $80, presentando ticket o nota y justificando |

### Gastos sin factura permitidos

| Duración del viaje | Gastos sin factura permitidos |
|---|---|
| 1 a 3 días | Ninguno |
| Más de 3 días | 1 por evento por persona |
| 10 días o más | 2 por evento por persona |

Excepción: lugares donde no es posible obtener factura ni ticket, avisando antes a administración.

### Plazos para entregar la comprobación

| Regreso del viaje | Plazo |
|---|---|
| Del 1 al 15 del mes | 5 días después del regreso |
| Del 16 al 22 del mes | 3 días después del regreso |
| Del 23 al fin de mes | Comprobación diaria |

El Excel dice "del 23 al 30"; falta confirmar qué pasa con el día 31.

### Otras reglas

- El monto para alimentos es un máximo; lo que no se use sigue siendo de DIAPSA.
- Se comprueba por rubro (gasolina con gasolina, alimentos con alimentos).
- Se prefieren facturas; los tickets o notas sin factura se aceptan solo dentro de los límites anteriores.
- Prohibido comprar con viáticos cerveza, cigarros, artículos personales o comida fuera de lo permitido.
- Si se excede lo entregado y no se comprueba, se descuenta del siguiente anticipo.
- Si no se comprueba en el plazo, se descuenta del siguiente pago de nómina.
- La cantidad autorizada no es limitativa: se puede pedir más justificando con facturas lo ya gastado (requisición complementaria).

### Avisos fijos (solo texto)

- Las estimaciones de gasolina suponen una velocidad máxima de 110 km/h; ni DIAPSA ni el seguro responden por velocidades mayores.

## Por confirmar con administración

1. ¿El sufijo `_B` es siempre una requisición complementaria de la original?
2. ¿Qué debe sumar exactamente "Total sin Airbnb"?
3. Regla de cálculo de desayunos y despensa.
4. Tope de comida con factura: $150 o $170.
5. ¿"Proyecto" es el cliente (por ejemplo, la empresa visitada) o el servicio?
6. ¿La ciudad sin etiqueta de la comprobación es la ciudad del servicio?
7. ¿Se siguen necesitando plaza y sucursal bancaria, o basta con la CLABE?
8. Plazo de comprobación para regresos el día 31.
