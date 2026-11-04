# Análisis de los formatos de Excel

Se analizaron tres archivos reales del proceso actual. Aquí se describen sin datos personales.

| Archivo | Hojas | Qué es |
|---|---|---|
| Requisición completa | 1: `REQUISICION` | Solicitud de anticipo con gasolina, casetas, comidas, desayunos y materiales |
| Requisición con sufijo `_B` | 2: `REQUISICION` y `COMPROBACION` | Requisición complementaria: solo trae visible el renglón de hospedaje (Airbnb) |
| Comprobación | 1: `COMPROBACION` | Comprobación de gastos de un viaje, con facturas, presupuesto y depósito |

El nombre del archivo es el folio: `consecutivo_UNIDAD_PROYECTO[_sufijo]`, por ejemplo `089_SALT_CLIENTE`. `SALT` es la unidad de negocio Saltillo.

## Formato de requisición

### Encabezado

- No. de requisición (folio)
- Fecha de solicitud
- Solicitante
- Proyecto
- Área
- Descripción del servicio

### Datos bancarios del solicitante

- Cuenta (en uno de los archivos es un número de tarjeta de 16 dígitos)
- Banco
- Nombre del titular

### Tabla de conceptos

Cada renglón es un concepto. Las columnas cambian de significado según el concepto:

| Concepto | Datos que usa |
|---|---|
| Traslado (gasolina) | Vehículo, litros del tanque, rendimiento km/L, origen, destino, distancia, precio del litro, número de viajes |
| Peajes | Descripción de las casetas, precio, número de tickets |
| Alimentos (comida y cena) | Número de personas, días, veces por día, monto autorizado por comida |
| Desayunos / despensa | Número de personas, días del viaje y una tabla de referencia (7 o 9 días × 2 o 4 personas) |
| Hospedaje / Airbnb | Tipo de hospedaje, precio por noche, número de noches |
| Materiales y otros | Descripción, precio, cantidad |

Columnas comunes: precio unitario, cantidad, unidad (CARGA, TICKET, CONSUMO, DESAYUNO, NOCHE, ÚNICO) y cantidad final por concepto. Las columnas *Suma de comprobación* y *Diferencia* se llenan después, con lo comprobado.

### Totales y autorizaciones

- Total depósito aprobado, total "sin Airbnb" (solo es una etiqueta, sin fórmula) y diferencia total.
- Duración del servicio (texto libre, por ejemplo "3 DIAS").
- Autoriza coordinador y autoriza administración (nombres).
- Firma del solicitante, pago recibido y sobrante (solo en uno de los archivos).

### Textos fijos

- Aviso de velocidad máxima de 110 km/h.
- Nota: "la cantidad autorizada no es limitativa".
- Compromisos del solicitante (topes, gastos sin factura, plazos de comprobación). Las reglas están en [reglas de negocio](reglas-de-negocio.md).

## Formato de comprobación

### Encabezado

- Fecha de comprobación
- Solicitante, unidad de negocio, área y proyecto
- Una ciudad sin etiqueta debajo del proyecto (en un archivo "SALTILLO", en otro "MONTERREY"); parece ser la ciudad del servicio
- No. de requisición y No. de partida
- Recuadro "REEMBOLSO" con los datos bancarios: cuenta (no. de tarjeta), banco, plaza y sucursal

### Tabla de gastos

Columnas: consecutivo, no. de factura, nombre del establecimiento, una columna de importe por categoría, IVA y total.

Categorías: boletos de avión, boleto de autobús, hospedaje, comida, combustibles y lubricantes, taxis, caseta, estacionamiento, materiales, papelería, mensajería y otros. En uno de los archivos la columna de autobús se reutilizó a mano como "Desayuno (despensa)".

### Resumen

- Presupuesto, gastado y disponible por categoría.
- Observaciones.
- Total.
- Hasta cinco renglones de depósito con su importe abonado (en un archivo: tipo "TARJETA", cuenta de la empresa, fecha e importe).
- Total a pagar.
- Firmas del solicitante y de autorización.

## Detalles ocultos

- En la requisición `_B` las filas de casi todos los conceptos están **ocultas**; solo se ve el hospedaje.
- El área de impresión de la requisición deja fuera las columnas de comprobación y los compromisos.
- Cada hoja tiene el logo de DIAPSA como imagen.

## Problemas encontrados

| Problema | Ejemplo |
|---|---|
| Cuenta bancaria corrupta | Guardada como número: Excel solo conserva 15 dígitos y cambió el último dígito de la tarjeta |
| Folios inconsistentes | El nombre del archivo dice `073`, la celda dice `069` y la hoja se llama como otra requisición |
| Datos copiados de otro empleado | Una comprobación trae el nombre y la cuenta de otra persona; el número de requisición está vacío |
| Nombres distintos para la misma persona | El autorizador aparece con dos apellidos diferentes |
| Sumas por renglón inconsistentes | Unos renglones suman sin IVA y otros con IVA |
| Totales por columna con rangos distintos | "Gastado" suma hasta filas diferentes según la columna; hay gastos que no se cuentan |
| Fórmula de desayunos distinta en cada archivo | Una usa la referencia de 7 días; otra toma la de 9 días y la divide entre 7 |
| Políticas que cambian | El tope por comida dice $150 en un archivo y $170 en otro |
| "Disponible" con el signo al revés | Se calcula gastado − presupuesto: positivo significa que se excedió |
| Presupuesto de la comprobación capturado a mano | Debería salir de la requisición |
| Columnas reutilizadas | La columna de autobús se usó para desayunos |
| Unidad incorrecta | El renglón de materiales tiene unidad "NOCHE" |
| Fecha como texto | "31 de octubre" en lugar de una fecha |
