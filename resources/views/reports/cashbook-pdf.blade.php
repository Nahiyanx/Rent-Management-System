<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Monthly Cashbook · {{ $report['month_label'] }}</title>

    <style>
        @page {
            margin: 32px 36px 42px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #200e01;
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9.5px;
            line-height: 1.5;
        }

        .masthead {
            width: 100%;
            padding-bottom: 10px;
            border-bottom: 2px solid #5b0202;
        }

        .masthead table {
            width: 100%;
            border-collapse: collapse;
        }

        .masthead td {
            vertical-align: bottom;
        }

        .masthead-rule {
            margin-top: 3px;
            border-top: 1px solid #d8cba0;
        }

        .brand-sub {
            margin-top: 5px;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 2.2px;
            text-transform: uppercase;
            color: #8a7a68;
        }

        .meta {
            text-align: right;
            line-height: 1.7;
        }

        .meta-label {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: #5b0202;
        }

        .meta-generated {
            font-size: 7.5px;
            color: #8a7a68;
        }

        .report-kicker {
            width: 34px;
            height: 3px;
            margin-top: 26px;
            background: #8b0000;
        }

        .report-title {
            margin: 12px 0 0;
            font-family: 'DejaVu Serif', serif;
            font-size: 28px;
            font-weight: bold;
            letter-spacing: -0.6px;
            line-height: 1.05;
            color: #200e01;
        }

        .report-month {
            margin-top: 8px;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #8b0000;
        }

        .balances {
            width: 100%;
            margin-top: 22px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .balance-cell {
            padding: 12px 14px;
            vertical-align: top;
            background: #faf6e6;
            border: 1px solid #e6dcb8;
        }

        .balance-cash-bank {
            width: 28%;
        }

        .balance-combined {
            width: 34%;
            background: #5b0202;
            border-color: #5b0202;
        }

        .balance-gap {
            width: 8px;
        }

        .balance-label {
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: #8a7a68;
        }

        .balance-inner {
            width: 100%;
            margin-top: 8px;
            border-collapse: collapse;
        }

        .balance-inner td {
            padding: 3px 0;
            font-size: 9px;
            border-bottom: 1px solid #ece3c2;
        }

        .balance-inner tr:last-child td {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .balance-inner .row-label {
            color: #8a7a68;
        }

        .balance-inner .row-value {
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
            color: #200e01;
        }

        .balance-inner .closing-label {
            font-weight: bold;
            color: #6b5b4a;
        }

        .balance-inner .closing-value {
            font-size: 11px;
        }

        .balance-combined .balance-label {
            color: #d9cfa9;
        }

        .balance-total {
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            letter-spacing: -0.2px;
            color: #f6f0d8;
        }

        .balance-foot {
            margin-top: 5px;
            font-size: 7px;
            letter-spacing: 0.5px;
            color: #c6b98f;
        }

        .expenses {
            width: 100%;
            margin-top: 22px;
            border: 1px solid #e6dcb8;
            border-collapse: collapse;
        }

        .expenses thead {
            display: table-header-group;
        }

        .expenses thead th {
            background: #5b0202;
            color: #f6f0d8;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            text-align: left;
            padding: 9px 10px;
        }

        .expenses tbody td {
            padding: 8px 10px;
            font-size: 9px;
            border-bottom: 1px solid #ece3c2;
            vertical-align: top;
        }

        .expenses tbody tr:nth-child(even) td {
            background: #fcf9ee;
        }

        .expenses .date {
            width: 17%;
            white-space: nowrap;
            color: #8a7a68;
        }

        .expenses .description {
            width: 48%;
            color: #200e01;
        }

        .expenses .paid-from {
            width: 15%;
            color: #6b5b4a;
        }

        .expenses .amount {
            width: 20%;
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
        }

        .expenses tfoot td {
            padding: 8px 10px;
            background: #faf6e6;
            border-top: 1px solid #e6dcb8;
            color: #8a7a68;
        }

        .expenses tfoot .amount {
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
            color: #200e01;
        }

        .expenses tfoot .grand-total td {
            background: #f3ecd2;
            border-top: 2px solid #5b0202;
            color: #200e01;
            font-size: 10px;
            font-weight: bold;
        }

        .expenses tfoot .grand-total .amount {
            color: #8b0000;
        }

        .empty {
            padding: 22px 10px;
            text-align: center;
            color: #8a7a68;
            font-style: italic;
            background: #fcf9ee;
            border-bottom: 1px solid #e6dcb8;
        }
    </style>
</head>
<body>
    @php
        $summary = $report['summary'];
        $money = static fn (float|int $amount): string => 'BDT '.number_format((float) $amount, 2);
        $brandLogo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/rentra-logo.png')));
    @endphp

    <div class="masthead">
        <table>
            <tr>
                <td>
                    <img src="{{ $brandLogo }}" alt="Rentra" style="width: 78px; height: auto;">
                    <div class="brand-sub">Property accounts</div>
                </td>
                <td class="meta">
                    <div class="meta-label">Cash &amp; bank statement</div>
                    <div class="meta-generated">Generated {{ now()->format('d M Y, H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="masthead-rule"></div>

    <div class="report-kicker"></div>
    <h1 class="report-title">Monthly Cashbook</h1>
    <div class="report-month">{{ $report['month_label'] }} · Cash and bank</div>

    <table class="balances">
        <tr>
            <td class="balance-cell balance-cash-bank">
                <div class="balance-label">Cash</div>
                <table class="balance-inner">
                    <tr>
                        <td class="row-label">Opening</td>
                        <td class="row-value">{{ $money($summary['starting_cash']) }}</td>
                    </tr>
                    <tr>
                        <td class="row-label closing-label">Closing</td>
                        <td class="row-value closing-value">{{ $money($summary['closing_cash']) }}</td>
                    </tr>
                </table>
            </td>
            <td class="balance-gap"></td>
            <td class="balance-cell balance-cash-bank">
                <div class="balance-label">Bank</div>
                <table class="balance-inner">
                    <tr>
                        <td class="row-label">Opening</td>
                        <td class="row-value">{{ $money($summary['starting_bank']) }}</td>
                    </tr>
                    <tr>
                        <td class="row-label closing-label">Closing</td>
                        <td class="row-value closing-value">{{ $money($summary['closing_bank']) }}</td>
                    </tr>
                </table>
            </td>
            <td class="balance-gap"></td>
            <td class="balance-cell balance-combined">
                <div class="balance-label">Combined current balance</div>
                <div class="balance-total">{{ $money($summary['closing_total']) }}</div>
                <div class="balance-foot">Cash and bank together</div>
            </td>
        </tr>
    </table>

    <table class="expenses">
        <thead>
            <tr>
                <th class="date">Date</th>
                <th class="description">Description</th>
                <th class="paid-from">Paid from</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report['expenses'] as $expense)
                <tr>
                    <td class="date">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="description">{{ $expense->description }}</td>
                    <td class="paid-from">{{ $expense->paid_from }}</td>
                    <td class="amount">{{ $money($expense->amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">No expenses recorded for this month.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Cash expenses subtotal</td>
                <td class="amount">{{ $money($summary['cash_expenses']) }}</td>
            </tr>
            <tr>
                <td colspan="3">Bank expenses subtotal</td>
                <td class="amount">{{ $money($summary['bank_expenses']) }}</td>
            </tr>
            <tr class="grand-total">
                <td colspan="3">Total expenses</td>
                <td class="amount">{{ $money($summary['expenses']) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
