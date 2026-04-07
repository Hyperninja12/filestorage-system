<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 8mm 5mm 12mm 5mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 8pt;
        }

        .header {
            text-align: center;
            margin-bottom: 4px;
        }

        .header p {
            margin: 1px 0;
            color: #000;
            font-size: 9pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 3px 4px;
            text-align: left;
            word-wrap: break-word;
            overflow-wrap: anywhere;
            vertical-align: top;
            font-size: 8pt;
        }

        th {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 7pt;
            text-align: center;
            vertical-align: middle;
            padding: 5px 2px;
        }

        .cash {
            text-align: right;
            white-space: nowrap;
        }

        /* Column widths - matching the reference layout */
        .col-account-code   { width: 5%; }
        .col-fund           { width: 2.5%; }
        .col-category       { width: 6%; }
        .col-subcategory    { width: 5%; }
        .col-description    { width: 14%; }
        .col-date           { width: 5%; }
        .col-property-no    { width: 5%; }
        .col-inventory      { width: 5%; }
        .col-po-no          { width: 4.5%; }
        .col-unit           { width: 2.5%; }
        .col-qty            { width: 2.5%; }
        .col-unit-value     { width: 5%; }
        .col-onhand-count   { width: 3%; }
        .col-onhand-value   { width: 5%; }
        .col-person         { width: 7%; }
        .col-office         { width: 5%; }
        .col-area           { width: 4.5%; }
        .col-additional     { width: 7%; }
        .col-remarks        { width: 6%; }

        /* Page number footer */
        .page-number {
            position: fixed;
            bottom: 2mm;
            right: 5mm;
            font-size: 7pt;
            color: #333;
        }

        .page-number:after {
            content: "Page " counter(page);
        }
    </style>
</head>

<body>
    <div class="page-number"></div>

    <div class="header">
        @if($personResponsible)
            <p>Person Responsible: <strong>{{ $personResponsible }}</strong></p>
        @endif
        <p>Total Records: {{ count($records) }}</p>
    </div>

    @php
        $colClasses = [
            'Account Code' => 'col-account-code',
            'Fund' => 'col-fund',
            'Category' => 'col-category',
            'Subcategory' => 'col-subcategory',
            'Description' => 'col-description',
            'Date of Purchase' => 'col-date',
            'Property No.' => 'col-property-no',
            'Inventory Item No.' => 'col-inventory',
            'PO No.' => 'col-po-no',
            'Unit' => 'col-unit',
            'Qty' => 'col-qty',
            'Unit Value' => 'col-unit-value',
            'On Hand Count' => 'col-onhand-count',
            'On Hand Value' => 'col-onhand-value',
            'Person Responsible' => 'col-person',
            'Office' => 'col-office',
            'Area Location' => 'col-area',
            'Additional Information' => 'col-additional',
            'Remarks' => 'col-remarks',
        ];
    @endphp

    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th class="{{ $colClasses[$h] ?? '' }}">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
                <tr>
                    @foreach($headers as $h)
                        @php
                            $val = $record->getColumn($h);
                            $isMoney = in_array($h, ['Unit Value', 'On Hand Value'], true);
                            if ($isMoney) {
                                $formatted = \App\Support\CashFormatter::format($val, false);
                                $display = $formatted !== '' ? $formatted : '';
                                $tdClass = 'cash';
                            } else {
                                $display = ($val !== null && $val !== '') ? (string) $val : '';
                                $tdClass = '';
                            }
                        @endphp
                        <td class="{{ $tdClass }}">{{ $display }}</td>
                    @endforeach
                </tr>
            @endforeach
            @if(count($records) === 0)
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; padding: 20px;">No records found.</td>
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>