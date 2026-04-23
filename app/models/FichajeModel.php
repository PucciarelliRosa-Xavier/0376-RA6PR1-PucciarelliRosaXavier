<?php
/**
 * TimeControl - FichajeModel
 * Lógica de cálculo de horas trabajadas
 */

class FichajeModel {

    /**
     * Dada una lista de fichajes del día, calcula las horas trabajadas en formato decimal
     * Ej: 7.5 = 7 horas y 30 minutos
     */
    public static function calcularHorasTrabajadas(array $fichajes): float {
        $minutos = 0;
        $entrada = null;

        foreach ($fichajes as $f) {
            if ($f['tipo'] === 'entrada') {
                $entrada = strtotime($f['timestamp']);
            } elseif ($f['tipo'] === 'salida' && $entrada !== null) {
                $salida   = strtotime($f['timestamp']);
                $minutos += ($salida - $entrada) / 60;
                $entrada  = null;
            }
        }

        // Si hay entrada sin salida (sigue dentro), contar hasta ahora
        if ($entrada !== null) {
            $minutos += (time() - $entrada) / 60;
        }

        return round($minutos / 60, 2);
    }

    /**
     * Devuelve un string formateado: "7h 30m"
     */
    public static function formatearHoras(float $horas_decimal): string {
        $h = (int)$horas_decimal;
        $m = (int)(($horas_decimal - $h) * 60);
        return "{$h}h {$m}m";
    }
}
