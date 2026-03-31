{{-- Template nga ICS (Inventory/Property) para sa Print — pareho sa layout sa ICS.docx. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ICS</title>
    <style>
        * { box-sizing: border-box; }
        /* Long bond: 8.5in x 13in. Usa ka page lang — header + table + photos sulod sa .ics-page. */
        @page { size: 8.5in 13in; margin: 0; }
        body { font-family: 'Times New Roman', serif; font-size: 11pt; color: #000; margin: 0; padding: 0; width: 8.5in; }
        .no-print { padding: 1rem 0.5in; margin: 0 auto; max-width: 8.5in; }
        .print-btn { padding: 0.5rem 1rem; background: #1e293b; color: #fff; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; font-family: inherit; }
        .print-btn:hover { background: #334155; }
        .ics-page { width: 8.5in; height: 13in; margin: 0 auto; display: flex; flex-direction: column; overflow: hidden; }
        .ics-header { flex: 0 0 auto; width: 100%; margin: 0; padding: 0; line-height: 0; }
        .ics-header img { width: 100%; height: auto; max-height: 2.2in; display: block; object-fit: contain; }
        .ics-body { flex: 1 1 0; min-height: 0; width: 8.5in; margin: 0; padding: 0.25in 0.25in 0.35in; box-sizing: border-box; display: flex; flex-direction: column; overflow: hidden; }
        .ics-table-wrap { flex: 1 1 0; min-height: 0; overflow: hidden; }
        .ics-table { width: 100%; border-collapse: collapse; font-size: 10pt; table-layout: fixed; }
        .ics-table th { width: 28%; text-align: left; padding: 4px 8px; font-weight: bold; border: 1px solid #000; vertical-align: top; background: #fff; font-size: 10pt; line-height: 1.25; }
        .ics-table td { padding: 4px 8px; border: 1px solid #000; vertical-align: top; font-size: 10pt; line-height: 1.25; word-wrap: break-word; overflow-wrap: break-word; }
        .ics-photo-section { flex: 0 0 auto; min-height: 0; margin-top: 0.25in; display: flex; flex-direction: column; max-height: 3.8in; }
        .ics-photo-row { display: flex; gap: 0.2in; flex: 1; min-height: 0; align-items: stretch; }
        .ics-photo-box { border: 1px solid #000; padding: 6px; flex: 1 1 0; min-width: 0; max-width: 50%; background: #fafafa; box-sizing: border-box; display: flex; flex-direction: column; min-height: 0; }
        .ics-photo-box img { width: 100%; height: auto; max-height: 3.4in; object-fit: contain; display: block; }
        @media print {
            .no-print { display: none !important; }
            body { width: 8.5in; overflow: hidden; }
            .ics-page { width: 8.5in; height: 13in; overflow: hidden; page-break-after: avoid; page-break-inside: avoid; }
            .ics-header img { max-height: 2.1in; }
            .ics-body { padding: 0.2in 0.22in 0.3in; }
            .ics-table, .ics-table th, .ics-table td { font-size: 10pt; padding: 4px 8px; line-height: 1.25; }
            .ics-photo-section { max-height: 3.9in; }
            .ics-photo-box { padding: 6px; }
            .ics-photo-box img { max-height: 3.5in; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="print-btn" onclick="window.print()">Print</button>
        <button type="button" class="print-btn" onclick="window.close()" style="margin-left:8px; background:#64748b;">Close</button>
    </div>

    <div class="ics-page">
    <header class="ics-header">
        <img src="{{ asset('images/ics-header.jpeg') }}" alt="">
    </header>

    <div class="ics-body">

    <div class="ics-table-wrap">
    <table class="ics-table">
        <tr><th>ACCOUNT CODE</th><td>{{ $data['account_code'] ?? '—' }}</td></tr>
        <tr><th>CATEGORY</th><td>{{ $data['category'] ?? '—' }}</td></tr>
        <tr><th>SUBCATEGORY</th><td>{{ $data['subcategory'] ?? '—' }}</td></tr>
        <tr><th>DESCRIPTION</th><td>{!! nl2br(e($data['description'] ?? '—')) !!}</td></tr>
        <tr><th>DATE OF PURCHASE</th><td>{{ $data['date_of_purchase'] ?? '—' }}</td></tr>
        <tr><th>PROPERTY NUMBER</th><td>{{ $data['property_no'] ?? '—' }}</td></tr>
        <tr><th>INVENTORY ITEM NO.</th><td>{{ $data['inventory_item_no'] ?? '—' }}</td></tr>
        <tr><th>P.O NUMBER</th><td>{{ $data['po_no'] ?? '—' }}</td></tr>
        <tr><th>UNIT VALUE</th><td>{{ $data['unit_value'] ?? '—' }}</td></tr>
        <tr><th>ON HAND VALUE</th><td>{{ $data['on_hand_value'] ?? '—' }}</td></tr>
        <tr><th>QTY</th><td>{{ $data['qty'] ?? '—' }}</td></tr>
        <tr><th>PERSON RESPONSIBLE</th><td>{{ $data['person_responsible'] ?? '—' }}</td></tr>
        <tr><th>OFFICE</th><td>{{ $data['office'] ?? '—' }}</td></tr>
        <tr><th>AREA LOCATION</th><td>{{ $data['area_location'] ?? '—' }}</td></tr>
        <tr><th>ADDITIONAL INFORMATION</th><td>{!! nl2br(e($data['additional_information'] ?? '—')) !!}</td></tr>
        <tr><th>REMARKS</th><td>{!! nl2br(e($data['remarks'] ?? '—')) !!}</td></tr>
    </table>
    </div>

    @if(count($imageUrls) > 0)
    <div class="ics-photo-section">
        <div class="ics-photo-row">
            @foreach($imageUrls as $i => $url)
                <div class="ics-photo-box">
                    <img src="{{ $url }}" alt="Asset {{ $i + 1 }}">
                </div>
            @endforeach
        </div>
    </div>
    @endif

    </div>{{-- end .ics-body --}}
    </div>{{-- end .ics-page --}}

    <script>window.onload = function() { window.print(); };</script>
</body>
</html>
