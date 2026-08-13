<?php

namespace Quirosys\Datatable\Traits;

use Illuminate\Support\Carbon;

trait FilterTrait
{
    public function getFilterDate($data): array
    {
        $period = $data['value'];
        $dateStart = $data['dateStart'];
        $dateEnd = $data['dateEnd'];
        $monthStart = $data['monthStart'];
        $monthEnd = $data['monthEnd'];

        $dStart = null;
        $dEnd = null;

        switch ($period) {
            case 'all':
                // "Todos" (Filter::makePeriod()->includeAllOption()): sin
                // filtro de periodo — el consumidor debe omitir su whereBetween
                // cuando ambas fechas llegan null.
                break;
            case 'month':
                $dStart = Carbon::parse($monthStart.'-01')->format('Y-m-d');
                $dEnd = Carbon::parse($monthStart.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $dStart = Carbon::parse($monthStart.'-01')->format('Y-m-d');
                $dEnd = Carbon::parse($monthEnd.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $dStart = $dateStart;
                $dEnd = $dateStart;
                break;
            case 'between_dates':
                $dStart = $dateStart;
                $dEnd = $dateEnd;
                break;
        }

        return [
            'date_start' => $dStart,
            'date_end' => $dEnd,
        ];
    }
}
