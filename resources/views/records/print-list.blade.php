<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Records List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 9pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 16pt;
        }
        .header p {
            margin: 5px 0;
            color: #555;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: left;
            word-break: break-word;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .cash {
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        @media print {
            @page {
                size: 13in 8.5in; /* Long Bond Paper (Folio) Landscape */
                margin: 5mm;      /* Smaller margins to fit more data */
            }
            body {
                margin: 0;
            }
            .no-print {
                display: none;
            }
            table {
                table-layout: auto;
            }
            td, th {
                font-size: 7.5pt;
                padding: 3px 2px;
                white-space: nowrap; /* Prevent standard columns from wrapping to another line */
            }
            .wrap-text {
                white-space: normal; /* Only allow these specific columns to wrap naturally */
                word-wrap: break-word;
                overflow-wrap: anywhere;
                min-width: 120px; /* Give it enough space to breathe */
            }
        }
        .action-bar {
            margin-bottom: 20px;
            text-align: right;
        }
        .action-bar button {
            padding: 8px 16px;
            background: #3b82f6;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12pt;
        }
        .action-bar button:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <button onclick="window.print()">Print</button>
        <button onclick="window.close()" style="background: #6b7280; margin-left: 8px;">Close</button>
    </div>

    <div class="header">
        <h1>Records List</h1>
        @if($personResponsible)
            <p>Filtered by Person Responsible: <strong>{{ $personResponsible }}</strong></p>
        @endif
        @if($type === 'par')
            <p>Type: <strong>PAR (Unit Value 50,000 and above)</strong></p>
        @elseif($type === 'ics')
            <p>Type: <strong>ICS (Unit Value below 50,000)</strong></p>
        @endif
        <p>Total Records: {{ count($records) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
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
                                $display = \App\Support\CashFormatter::formatOrPlaceholder($val);
                                $tdClass = 'cash';
                            } else {
                                $display = ($val !== null && $val !== '') ? (string) $val : '—';
                                $tdClass = in_array($h, ['Description', 'Additional Information', 'Remarks'], true) ? 'wrap-text' : '';
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

    <script>
        // Auto-print when loaded
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
