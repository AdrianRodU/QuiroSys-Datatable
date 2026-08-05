<?php

namespace Quirosys\Datatable\Traits;

use Quirosys\Datatable\Exports\GenericReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ExcelTrait
{
    /**
     * Exporta a Excel cualquier colección de datos formateada por un Resource y columnas configuradas.
     *
     * @param  string  $resource  Nombre de la Collection Resource (ej: CompanyCollection::class).
     * @param  string  $filename  Nombre del archivo a descargar.
     * @param  string  $title  Título grande del reporte.
     * @param  array  $exclude  Columnas a excluir (por defecto ['actions']).
     * @return BinaryFileResponse Archivo Excel descargable.
     *
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportRecordsGeneric(Request $request, string $resource, string $filename = 'reporte.xlsx',
        string $title = 'Reporte', array $exclude = ['actions'])
    {
        $filters = $request->input('filters', []);
        $this->sortBy = $request->input('sortBy');
        $this->descending = $request->input('descending');
        $this->direction = $this->descending ? 'desc' : 'asc';

        $query = $this->buildFilters($filters);
        $columns = $this->getColumns();

        // 1. Aplica el resource y convierte a array
        $records = collect((new $resource($query->get()))->jsonSerialize());

        // 2. Filtra columnas a mostrar
        $filteredColumns = array_filter($columns, fn ($c) => ! in_array($c['name'], $exclude));
        $fields = array_column($filteredColumns, 'name');
        $headers = array_column($filteredColumns, 'label');

        // 3. Mapea los datos según los campos y formatea saltos de línea
        $data = $records->map(function ($item) use ($fields) {
            $row = [];
            foreach ($fields as $f) {
                $r = data_get($item, $f);

                // 1. Si es una celda tipo Cell (array) con type_input
                if (is_array($r) && isset($r['type_input'])) {
                    switch ($r['type_input']) {
                        case 'multi_line':
                            // Junta las líneas con \n
                            $row[] = is_array($r['value']) ? implode("\n", $r['value']) : (string) $r['value'];
                            break;

                        case 'composite':
                            // Para composite, recorre cada línea y cada elemento de esa línea
                            $flatLines = [];
                            foreach ($r['lines'] as $line) {
                                $lineParts = [];
                                foreach ($line as $part) {
                                    if (is_array($part)) {
                                        // Si es texto
                                        if ($part['type_input'] === 'text') {
                                            $lineParts[] = $part['value'];
                                        }
                                        // Si es badge/chip, puedes mostrar solo el label o label + value
                                        elseif (in_array($part['type_input'], ['badge', 'chip'])) {
                                            $lineParts[] = $part['label'];
                                        }
                                        // Si es link, mostrar solo el texto o el URL, según desees
                                        elseif ($part['type_input'] === 'link') {
                                            $lineParts[] = $part['label'].' ('.$part['url'].')';
                                        }
                                        // Si es icon/avatar/switch, usualmente puedes omitir o poner un texto indicativo
                                        elseif ($part['type_input'] === 'icon') {
                                            $lineParts[] = '[icon]';
                                        } elseif ($part['type_input'] === 'avatar') {
                                            $lineParts[] = '[avatar]';
                                        } elseif ($part['type_input'] === 'switch') {
                                            $lineParts[] = $part['checked'] ? 'Sí' : 'No';
                                        }
                                        // Otras tipos: agregar lo que necesites
                                    }
                                }
                                $flatLines[] = implode(' ', $lineParts);
                            }
                            $row[] = implode("\n", $flatLines);
                            break;

                        case 'html':
                            // Quita <br> y reemplaza por salto de línea
                            $row[] = preg_replace('/<br\s*\/?>/i', "\n", $r['label']);
                            break;

                        case 'badge':
                        case 'chip':
                            $row[] = $r['label'];
                            break;

                        case 'text':
                            $row[] = $r['value'];
                            break;

                        case 'link':
                            $row[] = $r['label'].' ('.$r['url'].')';
                            break;

                        case 'icon':
                        case 'avatar':
                        case 'switch':
                            // Puedes omitirlos o poner algo indicativo
                            $row[] = '';
                            break;

                        default:
                            $row[] = ''; // Si es un tipo desconocido
                    }
                }
                // 2. Si es string plano
                elseif (is_string($r)) {
                    // Convierte <br> a salto de línea (por compatibilidad)
                    $row[] = preg_replace('/<br\s*\/?>/i', "\n", $r);
                }
                // 3. Si es null o vacío
                elseif (is_null($r) || $r === '') {
                    $row[] = '';
                }
                // 4. Otros casos (number, boolean, etc.)
                else {
                    $row[] = $r;
                }
            }

            // Garantiza el mismo número de columnas que los headers
            if (count($row) < count($fields)) {
                $row = array_pad($row, count($fields), '');
            }

            return $row;
        })->toArray();

        // 4. Exporta usando el Export genérico
        $response = Excel::download(new GenericReportExport($data, $headers, $title), $filename);
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

        return $response;
    }
}
