<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', Times, serif;
            background: white;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .grid-table > tbody > tr > td {
            padding: 2px;
            vertical-align: top;
        }
        .card {
            border: 1px solid #8B7355;
            width: 100%;
            page-break-inside: avoid;
        }
        .card-inner {
            width: 100%;
            border-collapse: collapse;
        }
        .card-inner td {
            vertical-align: middle;
            padding: 2px;
        }
        .qr-cell {
            width: 24mm;
            text-align: center;
        }
        .qr-cell img {
            width: 22mm;
            height: 22mm;
            display: block;
        }
        .info-cell {
            padding-left: 2px;
        }
        .info-title {
            font-size: 9pt;
            color: #8B7355;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .info-name {
            font-size: 9pt;
            color: #8B7355;
            font-weight: bold;
            margin-top: 4px;
            line-height: 1.3;
            text-align: center;
        }
        .info-keterangan {
            font-size: 7pt;
            color: #8B7355;
            margin-top: 4px;
            text-align: center;
        }
        .info-desc {
            font-size: 7pt;
            color: #666;
            margin-top: 4px;
            text-align: center;
            font-style: italic;
        }
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    @php $cols = $columns ?? 3; @endphp
    <table class="grid-table">
        <tbody>
            @foreach($tamuList->chunk($cols) as $row)
            <tr>
                @foreach($row as $tamu)
                <td style="width: {{ round(100 / $cols, 2) }}%;">
                    <div class="card">
                        <table class="card-inner">
                            <tbody>
                                <tr>
                                    <td class="qr-cell">
                                        <img src="{{ $tamu->getQrCodeForPdf(4) }}" />
                                    </td>
                                    <td class="info-cell">
                                        <div class="info-title">
                                            {{ $tamu->bahasa === 'en' ? 'Dearest' : 'Kepada Yth.' }}
                                        </div>
                                        <div class="info-name">
                                            {{ $tamu->nama }}
                                        </div>
                                        @if ($tamu->deskripsi)
                                            <div class="info-keterangan">
                                                {{ $tamu->deskripsi }}
                                            </div>
                                        @endif
                                        <div class="info-desc">
                                            @if($tamu->bahasa === 'en')
                                                Invitation valid for {{ $tamu->jumlah_orang }} person(s)
                                            @else
                                                Undangan berlaku untuk {{ $tamu->jumlah_orang }} orang
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
                @endforeach
                @for($i = $row->count(); $i < $cols; $i++)
                <td style="width: {{ round(100 / $cols, 2) }}%;"></td>
                @endfor
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
