<?php
/**
 * ---------------------------------------------------------------------
 * models/Finanza.php
 * ---------------------------------------------------------------------
 * Acceso a la tabla `finanzas` (ingresos y gastos). El balance no se
 * guarda: se calcula como SUM(ingresos) - SUM(gastos).
 * ---------------------------------------------------------------------
 */

defined('APP') or exit('Acceso directo no permitido');

class Finanza extends Model
{
    protected static string $tabla = 'finanzas';

    /** Movimientos de un mes (formato 'YYYY-MM'), más recientes primero. */
    public static function delMes(string $mes): array
    {
        $sql = "SELECT f.*, CONCAT(u.nombre, ' ', u.apellido) AS registrado_por
                  FROM finanzas f LEFT JOIN usuarios u ON u.id = f.creado_por
                 WHERE DATE_FORMAT(f.fecha, '%Y-%m') = ?
                 ORDER BY f.fecha DESC, f.id DESC";
        return self::query($sql, [$mes])->fetchAll();
    }

    /** Totales de un mes: ['ingresos' => x, 'gastos' => y, 'balance' => x - y]. */
    public static function resumen(string $mes): array
    {
        $f = self::query("SELECT COALESCE(SUM(CASE WHEN tipo = 'Ingreso' THEN monto END), 0) ingresos,
                                 COALESCE(SUM(CASE WHEN tipo = 'Gasto'   THEN monto END), 0) gastos
                            FROM finanzas WHERE DATE_FORMAT(fecha, '%Y-%m') = ?", [$mes])->fetch();
        return ['ingresos' => (float) $f['ingresos'], 'gastos' => (float) $f['gastos'],
                'balance' => (float) $f['ingresos'] - (float) $f['gastos']];
    }

    /**
     * Ingresos y gastos por día de los últimos $dias días (para la gráfica).
     * Devuelve [['dia' => 'dd/mm', 'ingresos' => x, 'gastos' => y], ...]
     */
    public static function porDia(int $dias = 7): array
    {
        $filas = self::query("SELECT fecha,
                                     SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END) ingresos,
                                     SUM(CASE WHEN tipo = 'Gasto'   THEN monto ELSE 0 END) gastos
                                FROM finanzas WHERE fecha > CURDATE() - INTERVAL ? DAY
                               GROUP BY fecha", [$dias])->fetchAll();
        $porFecha = array_column($filas, null, 'fecha');
        $serie = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $f = date('Y-m-d', strtotime("-$i day"));
            $serie[] = ['dia' => date('d/m', strtotime($f)),
                        'ingresos' => (float) ($porFecha[$f]['ingresos'] ?? 0),
                        'gastos'   => (float) ($porFecha[$f]['gastos'] ?? 0)];
        }
        return $serie;
    }
}
